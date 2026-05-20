<?php
namespace App\Services\Scan;

use App\Models\EventSetting;
use App\Services\BaseService;
use App\Services\Middleware\CheckinService as MiddlewareCheckinService;

class ScanService extends BaseService
{
    public function __construct()
    {

    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
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
        return app(ScanFileService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    // public function getEventSettings()
    // {
    //     $checkin = new MiddlewareCheckinService(
    //         $this->attributes['event_code'],
    //     );
    //     $checkin->attributes = [
    //         'user_group'    => $this->attributes['user_group'],
    //     ];
    //     return $checkin->getEventSettings();
    // }

    public function checkin()
    {
        $checkin = new MiddlewareCheckinService(
            $this->attributes['event_code'],
            $this->attributes['qrcode'],
            $this->attributes['scan_time'] ?? now()->format('Y-m-d H:i:s'),
        );

        $checkin->attributes = [
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'] ?? EventSetting::GROUP_DESKTOP,
        ];

        return $checkin->checkin();
    }

    public function multiCheckin()
    {
        $checkin = new MiddlewareCheckinService(
            $this->attributes['event_code'],
        );

        $checkin->attributes = [
            'data'          => $this->attributes['data'],
            'total_records' => $this->attributes['total_records'],
            'custom_fields' => $this->attributes['custom_fields'] ?? [],
            'user_group'    => $this->attributes['user_group'],
        ];

        return $checkin->multiCheckin();
    }
}
