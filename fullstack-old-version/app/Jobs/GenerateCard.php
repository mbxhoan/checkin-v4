<?php

namespace App\Jobs;

use App\Models\Card;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GenerateCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cardId;

    protected $clientId;

    public $tries = 2;

    public $backoff = [20, 60];

    public $timeout = 180;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $cardId, ?int $clientId = null)
    {
        $this->cardId = $cardId;
        $this->clientId = $clientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cmd = "generate:cards {$this->cardId} --clientId=0";

        if ($this->clientId) {
            $cmd = "generate:cards {$this->cardId} --clientId={$this->clientId}";
        }

        Artisan::call($cmd);

        $this->markCardCompletedWhenFinished();
    }

    protected function markCardCompletedWhenFinished(): void
    {
        if (empty($this->clientId)) {
            return;
        }

        $card = Card::query()->find($this->cardId);
        if (empty($card)) {
            return;
        }

        $query = Client::query()
            ->where('event_id', $card->event_id)
            ->where('status', '!=', Client::STATUS_DELETED);

        if (! empty($card->client_type)) {
            $query->where('type', $card->client_type);
        }

        $hasPending = (clone $query)
            ->where(function ($subQuery) {
                $subQuery->whereNull('document_pdf')
                    ->orWhere('document_pdf', '');
            })->exists();

        if (! $hasPending) {
            $card->update([
                'status' => Card::STATUS_COMPLETED,
            ]);
        }
    }
}
