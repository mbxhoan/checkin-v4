<?php
namespace App\Services\Admin;

use App\Models\EventArea;
use App\Services\BaseService;
use App\Services\Middleware\ClientService as MiddlewareClientService;

class EventAreaService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(EventArea::class);
    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function checkin()
    {
        return app(CheckinService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function event_type()
    {
        return app(EventTypeService::class);
    }

    public function province()
    {
        return app(ProvinceService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function event_file()
    {
        return app(EventFileService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function postmark()
    {
        return app(PostmarkService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function page_access_log()
    {
        return app(PageAccessLogService::class);
    }

    public function card()
    {
        return app(CardService::class);
    }

    public function label()
    {
        return app(LabelService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function imp_exp_file()
    {
        return app(ImpexpFileService::class);
    }
}
