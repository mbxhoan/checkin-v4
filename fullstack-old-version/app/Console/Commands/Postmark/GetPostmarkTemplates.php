<?php

namespace App\Console\Commands\Postmark;

use App\Services\Middleware\EmailTemplateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GetPostmarkTemplates extends Command
{
    protected $service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-postmark-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Postmark email templates';

    public function __construct(EmailTemplateService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");

        $this->line("Fetching Postmark email templates...");
        $this->getPostmarkTemplates(true);

        $end = date("Y/m/d H:i:s a");
        $this->comment("Started at {$start}");
        $this->comment("Finished at {$end}");
        $diff = (strtotime($end) - strtotime($start))/60;
        $this->comment("Collapsed time: {$diff} minutes !");
        $this->question('COMPLETED');
        return 1;
    }

    public function getPostmarkTemplates()
    {
        $templates = $this->service->getRedis("postmark", "email_templates", "array");

        try {
            $params = [
                'count'             => 100,
                'offset'            => 0,
                'LayoutTemplate'    => null,
                'TemplateType'      => "Standard", // Layout là layouts, Standard là templates
            ];

            $result = $this->service->httpClient->get("templates", $params);

            if ($result && (isset($result['TotalCount']) && isset($result['Templates']))) {
                foreach ($result['Templates'] as $index => $template) {
                    $postMarkTemplate = $this->service->getPostmarkTemplate($template['TemplateId']);
                    $result['Templates'][$index] = $postMarkTemplate;
                }

                $result['Templates'] = $templates;
                $result['TotalCount'] = count($templates);

                /* re-assign */
                $this->service->updateRedis("postmark", "email_templates", json_encode($result['Templates']), config("app.times.minutes.30"));
                return $result;
            }

            Log::info($result);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error("Call API Postmark - Get Templates: {$e->getMessage()}");
        }

        return [];
    }
}
