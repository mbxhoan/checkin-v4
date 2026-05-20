<?php

use App\Http\Controllers\Admin\AudioController;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CampaignDetailController;
use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\CardDetailController;
use App\Http\Controllers\Admin\CheckinController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomFieldTemplateController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EmailSenderController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EventAreaController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventFileController;
use App\Http\Controllers\Admin\EventSettingController;
use App\Http\Controllers\Admin\FeatureUnavailableController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\ImpExpFileController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\LabelDetailController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LanguageDefineController;
use App\Http\Controllers\Admin\LuckyDrawBuilderController;
use App\Http\Controllers\Admin\LuckyDrawClientController;
use App\Http\Controllers\Admin\LuckyDrawController;
use App\Http\Controllers\Admin\LuckyDrawDrawController;
use App\Http\Controllers\Admin\LuckyDrawRewardController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\N8nChatbotController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ShowDashboard;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', ShowDashboard::class)->name('dashboard');
});

Route::middleware('role:admin')->prefix('chatbot/n8n')->name('chatbot.n8n.')->group(function () {
    Route::get('/history', [N8nChatbotController::class, 'history'])->name('history');
    Route::post('/mode', [N8nChatbotController::class, 'selectMode'])->name('mode');
    Route::post('/send', [N8nChatbotController::class, 'send'])->name('send');
    Route::post('/reset', [N8nChatbotController::class, 'reset'])->name('reset');
});

/* LOG VIEWER */
Route::get('logs', function () {
    if (auth()->check()) {
        if (auth()->user()->isSysAdmin()) {
            return app(LogViewerController::class)->index();
        }

        abort(404);
    }
})->name('logs');

Route::middleware([
    'auth',
    'web.except.routes',
    'role:admin',
])->group(function () {
    // Shown when basic users click features not included in their package.
    Route::get('feature-unavailable', FeatureUnavailableController::class)->name('feature-unavailable');

    /* COMPANYS */
    Route::resource('companys', CompanyController::class);
    Route::prefix('companys')->group(function () {
        Route::post('sync-event-settings/{company}', [CompanyController::class, 'syncEventSetting'])->name('companys.sync-event-settings');
    });

    /* EVENTS */
    Route::resource('events', EventController::class);
    Route::prefix('events')->group(function () {
        Route::post('upload-medias/{event}', [EventController::class, 'uploadMedias'])->name('events.upload-medias');
        Route::get('/data/get-list-by/{companyId}', [EventController::class, 'getListByCompanyId'])->name('events.get-list-by-company-id');
        Route::get('/data/get-types-by-event/{eventId}', [EventController::class, 'getClientTypesByEvent'])->name('events.get-types-by-event');
        Route::put('update-custom_checkin_messages/{event}', [EventController::class, 'updateCustomCheckinMessages'])->name('events.update-custom_checkin_messages');
        Route::post('clone/{event}', [EventController::class, 'clone'])->name('events.clone');
        Route::post('update-features/{event}', [EventController::class, 'updateFeatures'])->name('events.update-features');
        Route::post('remove-feature/{event}', [EventController::class, 'removeFeature'])->name('events.remove-feature');
        Route::delete('erase/{event}', [EventController::class, 'erase'])->name('events.erase');
        Route::get('qrcode-preview/{event}', [EventController::class, 'qrcodePreview'])->name('events.qrcode-preview');
    });

    /* EVENTS */
    Route::resource('event_areas', EventAreaController::class);
    // Route::prefix('events')->group(function () {
    //     Route::post('upload-medias/{event}', [EventController::class, 'uploadMedias'])->name('events.upload-medias');
    //     Route::get('/data/get-list-by/{companyId}', [EventController::class, 'getListByCompanyId'])->name('events.get-list-by-company-id');
    //     Route::put('update-custom_checkin_messages/{event}', [EventController::class, 'updateCustomCheckinMessages'])->name('events.update-custom_checkin_messages');
    //     Route::post('clone/{event}', [EventController::class, 'clone'])->name('events.clone');
    //     Route::post('update-features/{event}', [EventController::class, 'updateFeatures'])->name('events.update-features');
    //     Route::post('remove-feature/{event}', [EventController::class, 'removeFeature'])->name('events.remove-feature');
    // });

    /* EVENT SETTINGS */
    Route::resource('event_settings', EventSettingController::class)->only(['update', 'store', 'destroy']);
    Route::prefix('event_settings')->group(function () {
        Route::post('sync-settings/{event}', [EventSettingController::class, 'syncSettings'])->name('event_settings.sync-settings');
        Route::put('event_settings/{eventSetting}', [EventSettingController::class, 'updateValue'])->name('event_settings.update-value');
    });

    /* EVENT FILES */
    Route::prefix('event_files')->group(function () {
        Route::post('upload/{event}', [EventFileController::class, 'upload'])->name('event_files.upload');
    });

    /* CUSTOM FIELD TEMPLATES */
    Route::resource('custom_field_templates', CustomFieldTemplateController::class)->only(['update', 'store', 'destroy']);
    Route::prefix('custom_field_templates')->group(function () {
        // Route::put('update/{custom_field_template}', [CustomFieldTemplateController::class, 'update'])->name('custom_field_templates.update');
        Route::post('/update-orders', [CustomFieldTemplateController::class, 'updateOrders'])->name('custom_field_templates.update-orders');
    });

    /* CHECKIN */
    Route::prefix('checkins')->group(function () {
        Route::get('/config/{event}', [CheckinController::class, 'config'])->name('checkins.config');
        Route::get('/render-background/{event}/{screen}/{msg}', [CheckinController::class, 'renderBackground'])->name('checkins.render-background');
        Route::get('/export/check-in-out/{event}/{qrcode?}', [CheckinController::class, 'exportCheckInOutReport'])->name('checkins.export-check-in-out');
        Route::get('/export/checkin_count/{event}/{qrcode?}', [CheckinController::class, 'exportCheckInCount'])->name('checkins.export-checkin_count');
        Route::delete('/destroy-by-qrcode/{event}/{qrcode}', [CheckinController::class, 'destroyByQrcode'])->name('checkins.destroy-by-qrcode');
        Route::delete('/destroy-all/{event}', [CheckinController::class, 'destroyAll'])->name('checkins.destroy-all');
    });

    /* CLIENTS */
    Route::resource('clients', ClientController::class)->only(['destroy']);
    Route::get('clients/data/{event}', [ClientController::class, 'getData'])->name('clients.data');
    Route::prefix('clients')->group(function () {
        Route::get('/import/{event}', [ClientController::class, 'import'])->name('clients.import');
        Route::post('/upload/{event}', [ClientController::class, 'upload'])->name('clients.upload');
        Route::delete('/destroy-all/{event}', [ClientController::class, 'destroyAll'])->name('clients.destroy-all');
    });

    /* IMP EXP FILE */
    Route::prefix('imp_exp_files')->group(function () {
        Route::get('/progress/{imp_exp_file}', [ImpExpFileController::class, 'getProgress'])->name('imp_exp_files.progress');
    });

    /* CAMPAIGNS */
    Route::resource('campaigns', CampaignController::class)->only(['create', 'index', 'edit', 'update', 'store', 'destroy']);
    Route::prefix('campaigns')->group(function () {
        Route::get('/select-event-to-create', [CampaignController::class, 'selectEventToCreate'])->name('campaigns.select-event-to-create');
        // Route::get('/create/{event}', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::get('/history/{campaign}', [CampaignController::class, 'viewHistory'])->name('campaigns.history');
        Route::get('/progress/{campaign}', [CampaignController::class, 'getProgress'])->name('campaigns.progress');
        Route::get('/send-mail-table/{campaign}', [CampaignController::class, 'getSendMailTable'])->name('campaigns.send-mail-table');
        Route::get('/history-table/{campaign}', [CampaignController::class, 'getHistoryTable'])->name('campaigns.history-table');
        Route::post('/sync-template-options', [CampaignController::class, 'syncTemplateOptions'])->name('campaigns.sync-template-options');
        Route::post('/sync-campaign-detail/{campaign}', [CampaignController::class, 'syncCampaignDetail'])->name('campaigns.sync-campaign-detail');
        Route::post('/cancel/{campaign}', [CampaignController::class, 'cancel'])->name('campaigns.cancel');
        Route::post('/clone/{campaign}', [CampaignController::class, 'clone'])->name('campaigns.clone');
    });

    /* CAMPAIGN DETAILS */
    Route::prefix('campaign_details')->group(function () {
        Route::post('/send-mail/{campaign}', [CampaignDetailController::class, 'sendMail'])->name('campaign_details.send-mail');
    });

    /* EMAILS */
    Route::prefix('emails')->group(function () {
        Route::get('/export-report/{event}', [EmailController::class, 'exportReport'])->name('emails.export-report');
        Route::get('/export-error-emails/{campaign}', [EmailController::class, 'exportErrorEmail'])->name('emails.export-error-emails');
        Route::post('/cancel/{campaign}', [EmailController::class, 'cancelByCampaign'])->name('emails.cancel-by-campaign');
        Route::post('/change-status/{email}', [EmailController::class, 'changeStatus'])->name('emails.change-status');
    });

    /* EMAIL SENDERS */
    Route::resource('email_senders', EmailSenderController::class)->only(['index', 'edit', 'create', 'store']);
    Route::prefix('email_senders')->group(function () {
        Route::post('/sync-options', [EmailSenderController::class, 'syncOptions'])->name('email_senders.sync-options');
        // /* vì default method update ở đây lấy put nên không set default được */
        // Route::post('/update/{senderId}', [EmailSenderController::class, 'update'])->name('email_senders.update');
    });

    /* EMAIL TEMPLATES */
    Route::resource('email_templates', EmailTemplateController::class)->only(['index', 'create', 'store']);
    Route::prefix('email_templates')->group(function () {
        Route::get('/get-postmark-templates/{id}', [EmailTemplateController::class, 'get']);
        Route::get('/sync-postmark-template-async/{templateId}', [EmailTemplateController::class, 'syncPostmarkTemplateAsync'])
            ->name('email_templates.sync-postmark-template-async');
        /* POSTMARK */
        Route::get('/re-sync-postmark-templates', [EmailTemplateController::class, 'reSyncPostmarkTemplates'])->name('email_templates.re-sync-postmark-templates');
        Route::get('/sync-postmark-template/{templateId}', [EmailTemplateController::class, 'syncPostmarkTemplate'])->name('email_templates.sync-postmark-template');
        Route::get('/view-postmark-template/{templateId}', [EmailTemplateController::class, 'viewPostmarkTemplate'])->name('email_templates.view-postmark-template');
        Route::get('/edit-postmark-template/{templateId}', [EmailTemplateController::class, 'editPostmarkTemplate'])->name('email_templates.edit-postmark-template');
        Route::post('/update-postmark-template/{templateId}', [EmailTemplateController::class, 'updatePostmarkTemplate'])->name('email_templates.update-postmark-template');
        Route::post('/update-paid-access/{templateId}', [EmailTemplateController::class, 'updatePaidAccess'])->name('email_templates.update-paid-access');
        Route::post('/request-unlock/{templateId}', [EmailTemplateController::class, 'requestUnlock'])->name('email_templates.request-unlock');
        Route::post('/send-test-postmark-template/{templateId}', [EmailTemplateController::class, 'sendTestPostmarkTemplate'])->name('email_templates.send-test-postmark-template');
        Route::post('/clone-postmark-template', [EmailTemplateController::class, 'clonePostmarkTemplate'])->name('email_templates.clone-postmark-template');
    });

    /* LABEL */
    Route::resource('labels', LabelController::class)->only(['index', 'create', 'update', 'store', 'destroy']);
    Route::prefix('labels')->group(function () {
        Route::get('/select-event-to-create', [LabelController::class, 'selectEventToCreate'])->name('labels.select-event-to-create');
        // Route::get('/create/{event}', [LabelController::class, 'create'])->name('labels.create');
        Route::get('/edit/{label}', [LabelController::class, 'edit'])->name('labels.edit');
        Route::get('/render-label/{label}', [LabelController::class, 'renderLabel'])->name('labels.render-label');
        Route::get('/render-box', [LabelController::class, 'renderBox'])->name('labels.render-box');
        Route::post('/update-live/{label}', [LabelController::class, 'updateLive'])->name('labels.update-live');
        Route::post('/clone/{label}', [LabelController::class, 'clone'])->name('labels.clone');
    });

    /* LABEL DETAILS */
    Route::resource('label_details', LabelDetailController::class)->only(['store', 'update']);

    /* CARDS */
    Route::resource('cards', CardController::class)->only(['index', 'create', 'update', 'store', 'destroy']);
    Route::prefix('cards')->group(function () {
        Route::get('/select-event-to-create', [CardController::class, 'selectEventToCreate'])->name('cards.select-event-to-create');
        // Route::get('/create/{event}', [CardController::class, 'create'])->name('cards.create');
        Route::get('/edit/{card}', [CardController::class, 'edit'])->name('cards.edit');
        Route::get('/render-background/{event}/{card}', [CardController::class, 'renderBackground'])->name('cards.render-background');
        Route::get('/progress/{card}', [CardController::class, 'getProgress'])->name('cards.progress');
        Route::get('/download-images/{card}', [CardController::class, 'downloadCardImages'])->name('cards.download-images');
        Route::post('/generate/{card}', [CardController::class, 'generate'])->name('cards.generate');
        Route::get('/get-full-screen/{card}', [CardController::class, 'getFullScreen'])->name('cards.get-full-screen');
    });

    /* CARD DETAILS */
    Route::resource('card_details', CardDetailController::class)->only(['store', 'update']);
    Route::prefix('card_details')->group(function () {
        // Route::post('/update-by-custom-field-template/{custom_field_template}', [CardDetailController::class, 'updateByCustomFieldTemplate'])->name('card_details.update-by-custom-field-template');
    });

    /* LANDING PAGES */
    Route::resource('landing_pages', LandingPageController::class)->only(['create', 'index', 'update', 'store', 'destroy']);
    Route::prefix('landing_pages')->group(function () {
        Route::get('/select-event-to-create', [LandingPageController::class, 'selectEventToCreate'])->name('landing_pages.select-event-to-create');
        // Route::get('/create', [LandingPageController::class, 'create'])->name('landing_pages.create');
        // Route::get('/create/{event}', [LandingPageController::class, 'create'])->name('landing_pages.create');
        Route::get('/edit/{landing_page}', [LandingPageController::class, 'edit'])->name('landing_pages.edit');
        Route::post('/clone/{landing_page}', [LandingPageController::class, 'clone'])->name('landing_pages.clone');
        Route::post('/update-show-language-selection/{landing_page}', [LandingPageController::class, 'updateShowLanguageSelection'])->name('landing_pages.update-show-language-selection');
    });

    /* LANGUAGES DEFINES */
    // Route::resource('language_defines', LanguageDefineController::class)->only(['update', 'store']);
    Route::prefix('language_defines')->group(function () {
        Route::get('/generate-lang/{event}', [LanguageDefineController::class, 'generateLang'])->name('language_defines.generate-lang');
        Route::post('/edit-value', [LanguageDefineController::class, 'editValue'])->name('language_defines.edit-value');
    });

    /* LUCKY DRAW */
    Route::resource('lucky_draws', LuckyDrawController::class)->only(['index', 'create', 'edit', 'update', 'store', 'destroy']);
    Route::prefix('lucky_draws')->group(function () {
        Route::get('/select-event-to-create', [LuckyDrawController::class, 'selectEventToCreate'])->name('lucky_draws.select-event-to-create');
        // Route::get('/create/{event}', [LuckyDrawController::class, 'create'])->name('lucky_draws.create');
        // Route::get('/view-raffle/{id}', [LuckyDrawController::class, 'viewRaffle'])->name('lucky-draw.view-raffle');
        // Route::get('/export-raffle-results/{id}', [LuckyDrawController::class, 'exportRaffleResult'])->name('lucky-draw.export-raffle-results');
        // Route::post('/update-raffle', [LuckyDrawController::class, 'updateRaffle'])->name('lucky-draw.update-raffle');
        /* UPLOAD EVENT IMAGE */
        Route::post('upload/background', [LuckyDrawController::class, 'uploadBackground'])->name('lucky-draw.upload-background');
        /* DELETE EVENT IMAGE */
        Route::delete('delete/background', [LuckyDrawController::class, 'deleteBackground'])->name('lucky-draw.delete-background');
        Route::post('/cancel-reward', [LuckyDrawController::class, 'cancelReward'])->name('lucky-draw.cancel-reward');
    });

    /* LUCKY DRAW REWARD */
    Route::resource('lucky_draw_rewards', LuckyDrawRewardController::class)->only(['destroy']);
    Route::prefix('lucky_draw_rewards')->group(function () {
        Route::get('/download-template/{lucky_draw}', [LuckyDrawRewardController::class, 'downloadTemplate'])->name('lucky_draw_rewards.download-template');
        Route::post('/store/{lucky_draw}', [LuckyDrawRewardController::class, 'store'])->name('lucky_draw_rewards.store');
        Route::post('/upload/{lucky_draw}', [LuckyDrawRewardController::class, 'upload'])->name('lucky_draw_rewards.upload');
        Route::post('/update/{lucky_draw_reward}', [LuckyDrawRewardController::class, 'update'])->name('lucky_draw_rewards.update');
        Route::post('/{lucky_draw}/reset-assignees', [LuckyDrawRewardController::class, 'resetAssignees'])->name('lucky_draw_rewards.reset_assignees');
        Route::post('/{lucky_draw}/destroy-all', [LuckyDrawRewardController::class, 'destroyAllRewards'])->name('lucky_draw_rewards.destroy_all');
    });

    /* LUCKY DRAW CLIENT */
    Route::resource('lucky_draw_clients', LuckyDrawClientController::class)->only(['destroy']);
    Route::prefix('lucky_draw_clients')->group(function () {
        Route::post('/sync/{lucky_draw}', [LuckyDrawClientController::class, 'sync'])->name('lucky_draw_clients.sync');
        Route::post('/reset', [LuckyDrawClientController::class, 'reset'])->name('lucky-draw-client.reset');
        Route::post('/reset/{lucky_draw}', [LuckyDrawClientController::class, 'resetLuckyDraw'])->name('lucky_draw_clients.reset');
        Route::post('/{lucky_draw_client}/remove-reward', [LuckyDrawClientController::class, 'removeReward'])->name('lucky_draw_clients.remove_reward');
    });

    /* LUCKY DRAW - reset winners (chỉ xóa gán giải, giữ danh sách tham dự) */
    Route::post('lucky_draws/{lucky_draw}/reset-winners', [LuckyDrawController::class, 'resetWinners'])->name('lucky_draws.reset_winners');
    Route::get('lucky_draws/{lucky_draw}/export-winners', [LuckyDrawController::class, 'exportWinners'])->name('lucky_draws.export_winners');
    Route::post('lucky_draws/upload-image-link', [LuckyDrawController::class, 'uploadImageLink'])->name('lucky_draws.upload_image_link');

    /* LUCKY DRAW BUILDER */
    Route::prefix('lucky_draws/{lucky_draw}/builder')->name('lucky_draws.builder.')->group(function () {
        Route::get('/', [LuckyDrawBuilderController::class, 'index'])->name('index');
        Route::get('/fields', [LuckyDrawBuilderController::class, 'getFields'])->name('fields');
        Route::get('/proxy-image', [LuckyDrawBuilderController::class, 'proxyImage'])->name('proxy-image');
        Route::post('/preview', [LuckyDrawBuilderController::class, 'preview'])->name('preview');
        Route::get('/layouts/default', [LuckyDrawBuilderController::class, 'getDefaultLayout'])->name('layouts.default');
        Route::post('/layouts', [LuckyDrawBuilderController::class, 'storeLayout'])->name('layouts.store');
        Route::get('/layouts/{layout}', [LuckyDrawBuilderController::class, 'showLayout'])->name('layouts.show');
        Route::put('/layouts/{layout}', [LuckyDrawBuilderController::class, 'updateLayout'])->name('layouts.update');
        Route::delete('/layouts/{layout}', [LuckyDrawBuilderController::class, 'destroyLayout'])->name('layouts.destroy');
        Route::post('/layouts/{layout}/clone', [LuckyDrawBuilderController::class, 'cloneLayout'])->name('layouts.clone');
    });

    /* LUCKY DRAW DISPLAY */
    Route::get('lucky_draws/{lucky_draw}/display', [LuckyDrawBuilderController::class, 'display'])->name('lucky_draws.display');
    Route::post('lucky_draws/{lucky_draw}/background', [LuckyDrawBuilderController::class, 'updateBackground'])->name('lucky_draws.background.update');

    /* LUCKY DRAW STATE MACHINE */
    Route::prefix('lucky_draws/{lucky_draw}/draw')->name('lucky_draws.draw.')->group(function () {
        Route::get('/state', [LuckyDrawDrawController::class, 'getState'])->name('state');
        Route::post('/start', [LuckyDrawDrawController::class, 'start'])->name('start');
        Route::post('/stop', [LuckyDrawDrawController::class, 'stop'])->name('stop');
        Route::post('/confirm', [LuckyDrawDrawController::class, 'confirm'])->name('confirm');
        Route::post('/reset', [LuckyDrawDrawController::class, 'reset'])->name('reset');
    });

    /* AUDIO */
    Route::resource('audios', AudioController::class)->only(['store', 'update']);
    Route::prefix('audios')->group(function () {
        Route::get('/set-to-event/{event}', [AudioController::class, 'setToEvent'])->name('audios.set-to-event');
    });

    /* HISTORY */
    Route::resource('histories', HistoryController::class)->only(['index']);

    /* Users */
    Route::resource('users', UserController::class)->only(['index', 'create', 'edit', 'update', 'store']);
    Route::prefix('users')->group(function () {
        Route::post('/send-verification/{user}', [UserController::class, 'sendVerification'])->name('users.send-verification');
        Route::post('/approve-access/{user}', [UserController::class, 'approveAccess'])->name('users.approve-access');
        Route::post('/sign-out/{user}', [UserController::class, 'signOut'])->name('users.sign-out');
        Route::get('/refresh', [UserController::class, 'refresh'])->name('users.refresh');
    });

    /* MEDIA */
    Route::resource('media', MediaLibraryController::class)->only(['index', 'create', 'store', 'destroy']);
});

Route::middleware([
    'auth',
    'web.except.routes',
    'role:admin|user',
])->group(function () {
    /* EVENT */
    Route::resource('events', EventController::class)->only(['index']);

    /* CLIENTS */
    Route::resource('clients', ClientController::class)->only(['update', 'store']);
    Route::prefix('clients')->group(function () {
        Route::get('/index/{event}', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/create/{event}', [ClientController::class, 'create'])->name('clients.create');
        Route::get('/edit/{client}', [ClientController::class, 'edit'])->name('clients.edit');
        Route::post('/generate/{event}', [ClientController::class, 'generate'])->name('clients.generate');
        Route::post('/generate-qrcodes/{event}', [ClientController::class, 'generateQrcodeImages'])->name('clients.generate-qrcodes');
        Route::get('/export/template-import/{event}', [ClientController::class, 'exportTemplateImport'])->name('clients.export-template-import');
        Route::get('/export/list/{event}', [ClientController::class, 'exportList'])->name('clients.export-list');
        Route::get('/export-qrcodes/{event}', [ClientController::class, 'exportQrcodes'])->name('clients.export-qrcodes');
        Route::get('/fill-qrcode/{event}', [ClientController::class, 'fillQrcode'])->name('clients.fill-qrcode');
        Route::get('/get-template-qrcodes/{event}', [ClientController::class, 'getTemplateQrcodes'])->name('clients.get-template-qrcodes');
        Route::get('/download/qrcodes/{event}', [ClientController::class, 'downloadQrcodeImages'])->name('clients.download-qrcodes');
        Route::post('/save-print', [ClientController::class, 'savePrint'])->name('clients.save-print');

        Route::post('/send-email/{client}', [ClientController::class, 'sendClientEmail'])->name('clients.send-email');
        Route::post('/generate-card/{client}', [ClientController::class, 'generateClientCard'])->name('clients.generate-card');
    });

    /* CHECKIN */
    Route::prefix('checkins')->group(function () {
        Route::get('/index/{event}', [CheckinController::class, 'index'])->name('checkins.index');
        Route::post('/checkin', [CheckinController::class, 'checkin'])->name('checkins.checkin');
    });

    /* REPORT */
    Route::resource('reports', ReportController::class)->only(['index']);
    // Route::get('/reports/get-clients-table/{event}', [ReportController::class, 'getClientTable'])->name('reports.get-clients-table');
    Route::prefix('reports')->group(function () {
        Route::get('/get-clients-table/{event}', [ReportController::class, 'getClientTable'])->name('reports.get-clients-table');
        Route::get('/event/{event}', [ReportController::class, 'report'])->name('reports.report');
        Route::get('/render-report/{event}', [ReportController::class, 'renderReport']);
    });
});

/* MEDIA */
Route::resource('media', MediaLibraryController::class)->only(['show']);

// Route::resource('posts', PostController::class);
// Route::delete('/posts/{post}/thumbnail', [PostThumbnailController::class, 'destroy'])->name('posts_thumbnail.destroy');
// Route::resource('comments', CommentController::class)->only(['index', 'edit', 'update', 'destroy']);
