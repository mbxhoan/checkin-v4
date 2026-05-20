<?php

namespace App\Services\Middleware;

use App\Jobs\GenerateCard;
use App\Models\Card;
use App\Models\Client;
use App\Services\BaseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CardService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Card::class);
    }

    public function generate($cardId, $clientId = null)
    {
        $card = Card::query()->find($cardId);
        if (empty($card)) {
            return false;
        }

        if (! empty($clientId)) {
            $this->dispatchGenerateCardJob((int) $cardId, (int) $clientId);

            return true;
        }

        $query = Client::query()
            ->where('event_id', $card->event_id)
            ->where('status', '!=', Client::STATUS_DELETED);

        if (! empty($card->client_type)) {
            $query->where('type', $card->client_type);
        }

        // Reset card links once before splitting into many small jobs.
        $query->update([
            'card_link_mobile' => null,
            'card_link_desktop' => null,
            'document_pdf' => null,
        ]);

        $card->update([
            'status' => Card::STATUS_INPROCESS,
        ]);

        $query->orderBy('id')->select('id')->chunkById(500, function ($clients) use ($cardId) {
            foreach ($clients as $client) {
                $this->dispatchGenerateCardJob((int) $cardId, (int) $client->id);
            }
        });

        return true;
    }

    protected function dispatchGenerateCardJob(int $cardId, int $clientId): void
    {
        $objJob = new GenerateCard($cardId, $clientId);
        $objJob->timeout = 180;
        $generateCardJob = $objJob
            ->onQueue($this->getCardQueueName())
            ->delay(Carbon::now()->addSecond());
        dispatch($generateCardJob);
    }

    protected function getCardQueueName(): string
    {
        return (string) config('queue.names.cards', 'default');
    }

    public function generateCardNow($cardId, $clientId = null)
    {
        try {
            Artisan::call("generate:cards {$cardId} --clientId={$clientId}");
            // Artisan::call("scale:images {$cardId} --clientId={$clientId}");
        } catch (Exception $e) {
            Log::alert($e->getMessage());

            return [
                'status' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return [
            'status' => true,
            'msg' => null,
        ];
    }
}
