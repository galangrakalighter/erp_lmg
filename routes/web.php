<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DataCenterController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/terms-privacy-policy', function () {
    return redirect()->route('legal.privacy');
});
Route::view('/privacy-policy', 'legal.privacy')->name('legal.privacy');
Route::view('/terms-of-service', 'legal.terms')->name('legal.terms');
Route::view('/data-deletion', 'legal.data-deletion')->name('legal.data-deletion');
Route::post('oauth/threads/deauthorize', [App\Http\Controllers\ThreadsOAuthController::class, 'deauthorize'])->name('oauth.threads.deauthorize');
Route::post('oauth/threads/data-deletion', [App\Http\Controllers\ThreadsOAuthController::class, 'dataDeletion'])->name('oauth.threads.data-deletion');
Route::middleware('auth')->group(function () {
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->middleware(EnsureUserIsAdmin::class)
        ->name('activity-logs.index');

    Route::middleware(EnsureUserIsAdmin::class)->group(function () {
        Route::get('data-center', [DataCenterController::class, 'index'])->name('data-center.index');
        Route::post('data-center', [DataCenterController::class, 'store'])->name('data-center.store');
        Route::put('data-center/{dataCenter}', [DataCenterController::class, 'update'])->name('data-center.update');
        Route::delete('data-center/{dataCenter}', [DataCenterController::class, 'destroy'])->name('data-center.destroy');
        Route::post('data-center/{dataCenter}/reveal', [DataCenterController::class, 'reveal'])->name('data-center.reveal');
    });

    Route::get('monitoring', [App\Http\Controllers\MonitoringController::class, 'index'])
        ->name('monitoring.index');
    Route::get('monitoring/access', [App\Http\Controllers\MonitoringController::class, 'accessIndex'])
        ->name('monitoring.access.index');
    Route::post('monitoring/access', [App\Http\Controllers\MonitoringController::class, 'storeAccess'])
        ->name('monitoring.access.store');
    Route::put('monitoring/access/{access}', [App\Http\Controllers\MonitoringController::class, 'updateAccess'])
        ->name('monitoring.access.update');
    Route::delete('monitoring/access/{access}', [App\Http\Controllers\MonitoringController::class, 'destroyAccess'])
        ->name('monitoring.access.destroy');
    Route::post('monitoring/sessions', [App\Http\Controllers\MonitoringController::class, 'start'])->name('monitoring.sessions.start');
    Route::get('monitoring/users/{user}/active-session', [App\Http\Controllers\MonitoringController::class, 'active'])->name('monitoring.users.active');
    Route::post('monitoring/sessions/{session}/join', [App\Http\Controllers\MonitoringController::class, 'join'])->name('monitoring.sessions.join');
    Route::get('monitoring/sessions/{session}/ice-servers', [App\Http\Controllers\MonitoringController::class, 'iceServers'])->name('monitoring.sessions.ice-servers');
    Route::post('monitoring/sessions/{session}/signal', [App\Http\Controllers\MonitoringController::class, 'sendSignal'])->name('monitoring.sessions.signal');
    Route::get('monitoring/sessions/{session}/signals', [App\Http\Controllers\MonitoringController::class, 'signals'])->name('monitoring.sessions.signals');
    Route::post('monitoring/sessions/{session}/firebase-token', [App\Http\Controllers\MonitoringController::class, 'firebaseToken'])->name('monitoring.sessions.firebase-token');
    Route::post('monitoring/sessions/{session}/stop', [App\Http\Controllers\MonitoringController::class, 'stop'])->name('monitoring.sessions.stop');

    // Dashboard
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::get('/daily-work', [App\Http\Controllers\HCDailyController::class, 'index'])->name('daily-work.self');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/digital-marketing/client/{id_produk}', [App\Http\Controllers\DigitalMarketingWorkspaceController::class, 'show'])
        ->name('digital-marketing.workspace');
    Route::put('/digital-marketing/client/{id_produk}/platform-mirroring', [App\Http\Controllers\DigitalMarketingWorkspaceController::class, 'updateMirroring'])
        ->name('digital-marketing.platform-mirroring');
    Route::get('/multimedia/client/{id_produk}', [App\Http\Controllers\MultimediaWorkspaceController::class, 'show'])
        ->name('multimedia.workspace');
    Route::get('/business-dev/client/{id_produk}', [App\Http\Controllers\BusinessDevWorkspaceController::class, 'show'])
        ->name('business-dev.workspace');
    Route::get('/idme/client/{id_produk}', [App\Http\Controllers\IdmeClientWorkspaceController::class, 'show'])
        ->middleware(EnsureUserIsAdmin::class)
        ->name('idme.workspace');
    Route::get('/division/{division}/client/{id_produk}', [App\Http\Controllers\DivisionClientWorkspaceController::class, 'show'])
        ->whereIn('division', ['mcn', 'tech', 'ecommerce'])
        ->name('division-client.workspace');
    Route::get('/client/{client}/kpi-timeline', [App\Http\Controllers\ClientKpiTimelineController::class, 'index'])
        ->name('client.kpi-timeline');
    Route::get('/client/{client}/content-guide', [App\Http\Controllers\ClientContentGuideController::class, 'index'])
        ->name('client.content-guide');
    Route::post('/client/{client}/content-guide/{section}', [App\Http\Controllers\ClientContentGuideController::class, 'store'])
        ->name('client.content-guide.store');
    Route::put('/client/{client}/content-guide/{section}/{item}', [App\Http\Controllers\ClientContentGuideController::class, 'update'])
        ->name('client.content-guide.update');
    Route::delete('/client/{client}/content-guide/{section}/{item}', [App\Http\Controllers\ClientContentGuideController::class, 'destroy'])
        ->name('client.content-guide.destroy');

    Route::get('file-master', [App\Http\Controllers\AccountMasterFileController::class, 'index'])->name('file-master.index');
    Route::get('file-master/{owner}', [App\Http\Controllers\AccountMasterFileController::class, 'show'])->name('file-master.show');
    Route::post('file-master/{owner}/upload', [App\Http\Controllers\AccountMasterFileController::class, 'upload'])->name('file-master.upload');
    Route::post('file-master/{owner}/documents', [App\Http\Controllers\AccountMasterFileController::class, 'createDocument'])->name('file-master.documents.store');
    Route::get('file-master/{owner}/files/{fileName}/view', [App\Http\Controllers\AccountMasterFileController::class, 'view'])->name('file-master.files.view');
    Route::get('file-master/{owner}/files/{fileName}/preview', [App\Http\Controllers\AccountMasterFileController::class, 'preview'])->name('file-master.files.preview');
    Route::get('file-master/{owner}/files/{fileName}/download', [App\Http\Controllers\AccountMasterFileController::class, 'download'])->name('file-master.files.download');
    Route::delete('file-master/{owner}/files/{file}', [App\Http\Controllers\AccountMasterFileController::class, 'destroy'])->name('file-master.files.destroy');

    // Logout
    Route::post('/logout', [App\Http\Controllers\LoginController::class, 'logout'])->name('logout');

    // Analisis Konten (Ambil Data dari Access Token Graph Instagram)
    Route::get('/analisis/{id_produk}/{platform}', [App\Http\Controllers\AnalisisController::class, 'index'])->name('analisis');

    // Market Research
    Route::get('/market_research/{id_produk}/{platform}', [App\Http\Controllers\marketResearchController::class, 'index'])->name('market_research');

    // Branding
    Route::get('/branding/{id_produk}/{platform}', [App\Http\Controllers\brandingController::class, 'index'])->name('branding');

    // Export PDF
    Route::get('/exportPDF/{id}/{halaman}', [App\Http\Controllers\PDFController::class, 'exportPDF'])->name('invoice.download');

    // MANAJEMEN USER
    Route::patch('manajemen_user/{user}/lock', [App\Http\Controllers\UserController::class, 'toggleLock'])
        ->name('manajemen_user.lock');
    Route::resource('manajemen_user', App\Http\Controllers\UserController::class);

    Route::post('crm/human/refresh-data', [App\Http\Controllers\CRMController::class, 'refreshHumanData'])
        ->name('crm.human.refresh-data');
    Route::resource('crm', App\Http\Controllers\CRMController::class);

    Route::get('master-folder', [App\Http\Controllers\MasterFolderController::class, 'index'])->name('master-folder.index');
    Route::get('my-master-folder', [App\Http\Controllers\MasterFolderController::class, 'myFolders'])->name('master-folder.mine');
    Route::post('master-folder', [App\Http\Controllers\MasterFolderController::class, 'store'])->name('master-folder.store');
    Route::put('master-folder/{folder}', [App\Http\Controllers\MasterFolderController::class, 'update'])->name('master-folder.update');
    Route::delete('master-folder/{folder}', [App\Http\Controllers\MasterFolderController::class, 'destroy'])->name('master-folder.destroy');
    Route::get('master-folder/{folder}', [App\Http\Controllers\MasterFolderController::class, 'show'])->name('master-folder.show');
    Route::post('master-folder/{folder}/files', [App\Http\Controllers\MasterFolderController::class, 'upload'])->name('master-folder.files.upload');
    Route::post('master-folder/{folder}/documents', [App\Http\Controllers\MasterFolderController::class, 'createDocument'])->name('master-folder.documents.store');
    Route::put('master-folder/{folder}/files/{file}', [App\Http\Controllers\MasterFolderController::class, 'updateFile'])->name('master-folder.files.update');
    Route::delete('master-folder/{folder}/files/{file}', [App\Http\Controllers\MasterFolderController::class, 'destroyFile'])->name('master-folder.files.destroy');
    Route::get('master-folder/{folder}/files/{file:original_name}/view', [App\Http\Controllers\MasterFolderController::class, 'viewPdf'])->scopeBindings()->name('master-folder.files.view');
    Route::get('master-folder/{folder}/files/{file}/preview', [App\Http\Controllers\MasterFolderController::class, 'preview'])->name('master-folder.files.preview');
    Route::get('master-folder/{folder}/files/{file}/download', [App\Http\Controllers\MasterFolderController::class, 'download'])->name('master-folder.files.download');

    Route::resource('social_creator', App\Http\Controllers\SosialCreatorController::class);
    Route::resource('blacklist', App\Http\Controllers\BlacklistController::class);
    Route::resource('gform_affiliate', App\Http\Controllers\gformAffiliateController::class);
    Route::resource('selection', App\Http\Controllers\selectionController::class);
    Route::resource('barang_client', App\Http\Controllers\BarangClientController::class);
    Route::get('/tools', [App\Http\Controllers\BarangClientController::class, 'index_tools'])->name('tools.index');
    Route::post('/import_creator', [App\Http\Controllers\SosialCreatorController::class, 'import'])->name('creator.import');
    // Route::get('/manajemen_user', [App\Http\Controllers\UserController::class, 'index'])->name('manajemen_user');
    Route::get('api/hc/get-tasks-json', [App\Http\Controllers\HCDailyController::class, 'getTaskJSON'])->name('getTaskJSON');
    // CMS MANAJEMEN`

    // Manajemen Konten
    Route::get('/manajemen/{id_produk}/{platform}', [App\Http\Controllers\ManajemenKontenController::class, 'index'])->name('manajemen');
    Route::post('/manajemen/{id_produk}/create', [App\Http\Controllers\ManajemenKontenController::class, 'store'])->name('manajemen_create');
    Route::post('/manajemen/{id_produk}/edit', [App\Http\Controllers\ManajemenKontenController::class, 'update'])->name('manajemen_update');
    Route::post('/manajemen/{id_produk}/delete', [App\Http\Controllers\ManajemenKontenController::class, 'destroy'])->name('manajemen_delete');

    // Produk
    Route::get('cms_{nama}/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('cms.produk');
    Route::get('cms/docs', [App\Http\Controllers\ProdukController::class, 'docs'])->name('docs');
    Route::post('cms/produk/create', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk_create');
    Route::post('cms/produk/edit/{id}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk_edit');
    Route::post('cms/produk/delete/{id}', [App\Http\Controllers\ProdukController::class, 'destroy'])->name('produk_delete');

    Route::get('oauth/meta/{client}/redirect', [App\Http\Controllers\MetaOAuthController::class, 'redirect'])->name('oauth.meta.redirect');
    Route::get('oauth/meta/connect', [App\Http\Controllers\MetaOAuthController::class, 'redirectDraft'])->name('oauth.meta.connect');
    Route::get('oauth/meta/callback', [App\Http\Controllers\MetaOAuthController::class, 'callback'])->name('oauth.meta.callback');
    Route::put('oauth/meta/{client}/assets', [App\Http\Controllers\MetaOAuthController::class, 'selectAssets'])->name('oauth.meta.assets');
    Route::delete('oauth/meta/{client}', [App\Http\Controllers\MetaOAuthController::class, 'disconnect'])->name('oauth.meta.disconnect');

    Route::get('oauth/threads/{client}/redirect', [App\Http\Controllers\ThreadsOAuthController::class, 'redirect'])->name('oauth.threads.redirect');
    Route::get('oauth/threads/connect', [App\Http\Controllers\ThreadsOAuthController::class, 'redirectDraft'])->name('oauth.threads.connect');
    Route::get('oauth/threads/callback', [App\Http\Controllers\ThreadsOAuthController::class, 'callback'])->name('oauth.threads.callback');
    Route::delete('oauth/threads/{client}', [App\Http\Controllers\ThreadsOAuthController::class, 'disconnect'])->name('oauth.threads.disconnect');

    Route::get('oauth/tiktok/{client}/redirect', [App\Http\Controllers\TikTokOAuthController::class, 'redirect'])->name('oauth.tiktok.redirect');
    Route::get('oauth/tiktok/connect', [App\Http\Controllers\TikTokOAuthController::class, 'redirectDraft'])->name('oauth.tiktok.connect');
    Route::get('oauth/tiktok/callback', [App\Http\Controllers\TikTokOAuthController::class, 'callback'])->name('oauth.tiktok.callback');
    Route::delete('oauth/tiktok/{client}', [App\Http\Controllers\TikTokOAuthController::class, 'disconnect'])->name('oauth.tiktok.disconnect');

    Route::get('client/{division}', [App\Http\Controllers\ProdukController::class, 'indexDivision'])->name('client.produk_divisi');
    Route::resource('perusahaan', App\Http\Controllers\PerusahaanController::class);

    // Hook
    Route::get('cms_{nama}/hook', [App\Http\Controllers\HookController::class, 'index'])->name('cms.hook');
    Route::post('cms_{nama}/hook/create', [App\Http\Controllers\HookController::class, 'store'])->name('hook_create');
    Route::post('cms/hook/edit/{id}', [App\Http\Controllers\HookController::class, 'update'])->name('hook_edit');
    Route::post('cms/hook/delete/{id}', [App\Http\Controllers\HookController::class, 'destroy'])->name('hook_delete');

    // Strategyp
    Route::get('cms_{nama}/strategy', [App\Http\Controllers\StrategyController::class, 'index'])->name('cms.strategy');
    Route::post('cms_{nama}/strategy/create', [App\Http\Controllers\StrategyController::class, 'store'])->name('strategy_create');
    Route::post('cms/strategy/edit/{id}', [App\Http\Controllers\StrategyController::class, 'update'])->name('strategy_edit');
    Route::post('cms/strategy/delete/{id}', [App\Http\Controllers\StrategyController::class, 'destroy'])->name('strategy_delete');

    // Status
    Route::get('cms_{nama}/status', [App\Http\Controllers\StatusController::class, 'index'])->name('cms.status');
    Route::post('cms_{nama}/status/create', [App\Http\Controllers\StatusController::class, 'store'])->name('status_create');
    Route::post('cms/status/edit/{id}', [App\Http\Controllers\StatusController::class, 'update'])->name('status_edit');
    Route::post('cms/status/delete/{id}', [App\Http\Controllers\StatusController::class, 'destroy'])->name('status_delete');

    // Type
    Route::get('cms_{nama}/type', [App\Http\Controllers\TypeController::class, 'index'])->name('cms.type');
    Route::post('cms_{nama}/type/create', [App\Http\Controllers\TypeController::class, 'store'])->name('type_create');
    Route::post('cms/type/edit/{id}', [App\Http\Controllers\TypeController::class, 'update'])->name('type_edit');
    Route::post('cms/type/delete/{id}', [App\Http\Controllers\TypeController::class, 'destroy'])->name('type_delete');

    // Pilar
    Route::get('cms_{nama}/pilar', [App\Http\Controllers\PilarController::class, 'index'])->name('cms.pilar');
    Route::post('cms_{nama}/pilar/create', [App\Http\Controllers\PilarController::class, 'store'])->name('pilar_create');
    Route::post('cms/pilar/edit/{id}', [App\Http\Controllers\PilarController::class, 'update'])->name('pilar_edit');
    Route::post('cms/pilar/delete/{id}', [App\Http\Controllers\PilarController::class, 'destroy'])->name('pilar_delete');

    // Jenis Body
    Route::get('cms_{nama}/body', [App\Http\Controllers\JenisBodyController::class, 'index'])->name('cms.body');
    Route::post('cms_{nama}/body/create', [App\Http\Controllers\JenisBodyController::class, 'store'])->name('body_create');
    Route::post('cms/body/edit/{id}', [App\Http\Controllers\JenisBodyController::class, 'update'])->name('body_edit');
    Route::post('cms/body/delete/{id}', [App\Http\Controllers\JenisBodyController::class, 'destroy'])->name('body_delete');

    // Jenis CTA
    Route::get('cms_{nama}/cta', [App\Http\Controllers\JenisCTAController::class, 'index'])->name('cms.cta');
    Route::post('cms_{nama}/cta/create', [App\Http\Controllers\JenisCTAController::class, 'store'])->name('cta_create');
    Route::post('cms/cta/edit/{id}', [App\Http\Controllers\JenisCTAController::class, 'update'])->name('cta_edit');
    Route::post('cms/cta/delete/{id}', [App\Http\Controllers\JenisCTAController::class, 'destroy'])->name('cta_delete');

    // Komposisi Content Plan
    Route::get('cms_{nama}/composition', [App\Http\Controllers\ContentCompositionController::class, 'index'])->name('cms.composition');
    Route::post('cms_{nama}/composition/create', [App\Http\Controllers\ContentCompositionController::class, 'store'])->name('composition_create');
    Route::post('cms/composition/edit/{id}', [App\Http\Controllers\ContentCompositionController::class, 'update'])->name('composition_edit');
    Route::post('cms/composition/delete/{id}', [App\Http\Controllers\ContentCompositionController::class, 'destroy'])->name('composition_delete');

    // CMS MARKET

    // Analisa 4P
    Route::get('cms_market/analize_4p', [App\Http\Controllers\analize4PController::class, 'index'])->name('cms_market.analize_4p');
    Route::post('cms_market/analize_4p/create', [App\Http\Controllers\analize4PController::class, 'store'])->name('analize_4p_create');
    Route::post('cms_market/analize_4p/edit/{id}', [App\Http\Controllers\analize4PController::class, 'update'])->name('analize_4p_edit');
    Route::post('cms_market/analize_4p/delete/{id}', [App\Http\Controllers\analize4PController::class, 'destroy'])->name('analize_4p_delete');

    // Analisa SWOT
    Route::get('cms_market/analize_swot', [App\Http\Controllers\analizeSWOTController::class, 'index'])->name('cms_market.analize_swot');
    Route::post('cms_market/analize_swot/create', [App\Http\Controllers\analizeSWOTController::class, 'store'])->name('analize_swot_create');
    Route::post('cms_market/analize_swot/edit/{id}', [App\Http\Controllers\analizeSWOTController::class, 'update'])->name('analize_swot_edit');
    Route::post('cms_market/analize_swot/delete/{id}', [App\Http\Controllers\analizeSWOTController::class, 'destroy'])->name('analize_swot_delete');

    // Segmentasi
    Route::get('cms_market/segmentasi', [App\Http\Controllers\segmentasiController::class, 'index'])->name('cms_market.segmentasi');
    Route::post('cms_market/segmentasi/create', [App\Http\Controllers\segmentasiController::class, 'store'])->name('segmentasi_create');
    Route::post('cms_market/segmentasi/edit/{id}', [App\Http\Controllers\segmentasiController::class, 'update'])->name('segmentasi_edit');
    Route::post('cms_market/segmentasi/delete/{id}', [App\Http\Controllers\segmentasiController::class, 'destroy'])->name('segmentasi_delete');

    // Target Audience
    Route::get('cms_market/target_audience', [App\Http\Controllers\targetAudienceController::class, 'index'])->name('cms_market.target_audience');
    Route::post('cms_market/target_audience/create', [App\Http\Controllers\targetAudienceController::class, 'store'])->name('target_audience_create');
    Route::post('cms_market/target_audience/edit/{id}', [App\Http\Controllers\targetAudienceController::class, 'update'])->name('target_audience_edit');
    Route::post('cms_market/target_audience/delete/{id}', [App\Http\Controllers\targetAudienceController::class, 'destroy'])->name('target_audience_delete');

    // Positioning
    Route::get('cms_market/positioning', [App\Http\Controllers\positioningController::class, 'index'])->name('cms_market.positioning');
    Route::post('cms_market/positioning/create', [App\Http\Controllers\positioningController::class, 'store'])->name('positioning_create');
    Route::post('cms_market/positioning/edit/{id}', [App\Http\Controllers\positioningController::class, 'update'])->name('positioning_edit');
    Route::post('cms_market/positioning/delete/{id}', [App\Http\Controllers\positioningController::class, 'destroy'])->name('positioning_delete');

    // Route::resource('cms_market/positioning', App\Http\Controllers\positioningController::class);

    // CMS BRANDING

    // Brand Identify
    Route::post('cms_branding/identify/create', [App\Http\Controllers\brandIdentifyController::class, 'store'])->name('identify.store');
    Route::post('cms_branding/identify/edit/{id}', [App\Http\Controllers\brandIdentifyController::class, 'update'])->name('identify.update');
    Route::post('cms_branding/identify/delete/{id}', [App\Http\Controllers\brandIdentifyController::class, 'destroy'])->name('identify.destroy');

    // Brand Image
    Route::get('cms_branding/image', [App\Http\Controllers\brandImageController::class, 'index'])->name('cms_branding.image');
    Route::post('cms_branding/image/create', [App\Http\Controllers\brandImageController::class, 'store'])->name('image.store');
    Route::post('cms_branding/image/edit/{id}', [App\Http\Controllers\brandImageController::class, 'update'])->name('image.update');
    Route::post('cms_branding/image/delete/{id}', [App\Http\Controllers\brandImageController::class, 'destroy'])->name('image.destroy');

    // Brand Value
    Route::get('cms_branding/value', [App\Http\Controllers\brandValueController::class, 'index'])->name('cms_branding.value');
    Route::post('cms_branding/value/create', [App\Http\Controllers\brandValueController::class, 'store'])->name('value.store');
    Route::post('cms_branding/value/edit/{id}', [App\Http\Controllers\brandValueController::class, 'update'])->name('value.update');
    Route::post('cms_branding/value/delete/{id}', [App\Http\Controllers\brandValueController::class, 'destroy'])->name('value.destroy');

    // Tagline
    Route::get('cms_branding/tagline', [App\Http\Controllers\taglineController::class, 'index'])->name('cms_branding.tagline');
    Route::post('cms_branding/tagline/create', [App\Http\Controllers\taglineController::class, 'store'])->name('tagline.store');
    Route::post('cms_branding/tagline/edit/{id}', [App\Http\Controllers\taglineController::class, 'update'])->name('tagline.update');
    Route::post('cms_branding/tagline/delete/{id}', [App\Http\Controllers\taglineController::class, 'destroy'])->name('tagline.destroy');

    // Gaya Komunikasi
    Route::post('cms_branding/komunikasi/create', [App\Http\Controllers\gayaKomunikasiController::class, 'store'])->name('komunikasi.store');
    Route::post('cms_branding/komunikasi/edit/{id}', [App\Http\Controllers\gayaKomunikasiController::class, 'update'])->name('komunikasi.update');
    Route::post('cms_branding/komunikasi/delete/{id}', [App\Http\Controllers\gayaKomunikasiController::class, 'destroy'])->name('komunikasi.destroy');

    // Komposisi
    Route::post('cms_branding/komposisi/create', [App\Http\Controllers\komposisiController::class, 'store'])->name('komposisi.store');
    Route::post('cms_branding/komposisi/edit/{id}', [App\Http\Controllers\komposisiController::class, 'update'])->name('komposisi.update');
    Route::post('cms_branding/komposisi/delete/{id}', [App\Http\Controllers\komposisiController::class, 'destroy'])->name('komposisi.destroy');

    // Audio Branding
    Route::post('cms_branding/audio/create', [App\Http\Controllers\audioBrandingController::class, 'store'])->name('audio.store');
    Route::post('cms_branding/audio/edit/{id}', [App\Http\Controllers\audioBrandingController::class, 'update'])->name('audio.update');
    Route::post('cms_branding/audio/delete/{id}', [App\Http\Controllers\audioBrandingController::class, 'destroy'])->name('audio.destroy');

    // Alat Branding
    Route::post('cms_branding/alat/create', [App\Http\Controllers\alatBrandingController::class, 'store'])->name('alat.store');
    Route::post('cms_branding/alat/edit/{id}', [App\Http\Controllers\alatBrandingController::class, 'update'])->name('alat.update');
    Route::post('cms_branding/alat/delete/{id}', [App\Http\Controllers\alatBrandingController::class, 'destroy'])->name('alat.destroy');

    // Moodboard
    Route::post('cms_branding/moodboard/create', [App\Http\Controllers\moodBoardController::class, 'store'])->name('moodboard.store');
    Route::post('cms_branding/moodboard/edit/{id}', [App\Http\Controllers\moodBoardController::class, 'update'])->name('moodboard.update');
    Route::post('cms_branding/moodboard/delete/{id}', [App\Http\Controllers\moodBoardController::class, 'destroy'])->name('moodboard.destroy');

    // Dashboard HC
    Route::get('dashboard_hc', [App\Http\Controllers\DashboardController::class, 'dashboard'])->name('overview.dashboard');
    Route::get('dashboard_kpi', [App\Http\Controllers\DashboardController::class, 'kpi_dashboard'])->name('overview.dashboard_kpi');
    Route::get('dashboard_finance/{id}', [App\Http\Controllers\DashboardController::class, 'finance_dashboard'])->name('finance.dashboard');
    Route::get('audit', [App\Http\Controllers\DashboardController::class, 'audit'])->name('audit.index');
    Route::get('/kpi/stats', [App\Http\Controllers\KPIController::class, 'getKpiStats']);
    Route::get('/task/stats', [App\Http\Controllers\KPIController::class, 'getTaskStats']);
    Route::get('/kpi/get-active-task', [App\Http\Controllers\KPIController::class, 'getActiveTask']);
    Route::post('hc_overview/daily_task', [App\Http\Controllers\HCDailyController::class, 'storeDaily'])->name('hc_daily.storeDaily');
    Route::get('hc_overview/{id_client}', [App\Http\Controllers\HCOverviewController::class, 'index'])->name('hc_overview.index');
    Route::post('hc_overview/{id_client}', [App\Http\Controllers\HCOverviewController::class, 'store'])->name('hc_overview.store');
    Route::put('hc_overview/{id_client}/{id}', [App\Http\Controllers\HCOverviewController::class, 'update'])->name('hc_overview.update');
    Route::delete('hc_overview/{id_client}/{id}', [App\Http\Controllers\HCOverviewController::class, 'destroy'])->name('hc_overview.destroy');
    Route::get('progress_client', [App\Http\Controllers\HCOverviewController::class, 'list_client'])->name('hc_overview.list');
    Route::get('hc_dailywork', [App\Http\Controllers\HCDailyController::class, 'index'])->name('hc_daily.index');
    Route::post('kpi/store', [App\Http\Controllers\HCOverviewController::class, 'tambahKPI'])->name('kpi.store');
    Route::put('kpi/{id}', [App\Http\Controllers\HCOverviewController::class, 'editKPI'])->name('kpi.update');
    Route::put('kpi/{id}/selesai', [App\Http\Controllers\HCOverviewController::class, 'endKPI'])->name('kpi.endKPI');
    Route::delete('kpi/{id}', [App\Http\Controllers\HCOverviewController::class, 'deleteKPI'])->name('kpi.destroy');
    Route::get('/journals/export-pdf', [App\Http\Controllers\PDFController::class, 'exportGeneralJournal'])->name('journals.export-pdf');
    Route::get('/finance-ledger/export-pdf', [App\Http\Controllers\PDFController::class, 'exportBukuBesar'])->name('ledger.export-pdf');
    Route::get('/adjustment-journal/export-pdf/{id}', [App\Http\Controllers\PDFController::class, 'exportAdjustmen'])->name('adjustment_journal.export-pdf');
    // Route::get('/payment', function(){
    //     $title = "Payment - ";
    //     return view('payment.index', compact('title'));
    // })->name('payment.index');

    // Route::post('/payment/get-token', [App\Http\Controllers\HCDailyController::class, 'getPaymentToken'])->name('payment.getToken');

    Route::get('/finance_coa/data/akun/{id}', [App\Http\Controllers\COAController::class, 'getCoaData'])->name('finance_coa.data');
    Route::get('/general_journal/data/jurnal/{id}', [App\Http\Controllers\GeneralJournalController::class, 'getGeneralJournalData'])->name('general_journal.data');

    Route::get('finance_coa/{id}', [App\Http\Controllers\COAController::class, 'index'])->name('finance_coa.index');
    Route::post('finance_coa/{id}', [App\Http\Controllers\COAController::class, 'store'])->name('finance_coa.store');
    Route::put('finance_coa/{id}', [App\Http\Controllers\COAController::class, 'update'])->name('finance_coa.update');
    Route::delete('finance_coa/{id}', [App\Http\Controllers\COAController::class, 'destroy'])->name('finance_coa.destroy');

    Route::get('general_journal/{id}', [App\Http\Controllers\GeneralJournalController::class, 'index'])->name('general_journal.index');
    Route::get('general_journal/detail/{id}', [App\Http\Controllers\GeneralJournalController::class, 'show'])->name('general_journal.show');
    Route::post('general_journal/{id}', [App\Http\Controllers\GeneralJournalController::class, 'store'])->name('general_journal.store');
    Route::put('general_journal/{id}', [App\Http\Controllers\GeneralJournalController::class, 'update'])->name('general_journal.update');
    Route::delete('general_journal/{id}', [App\Http\Controllers\GeneralJournalController::class, 'destroy'])->name('general_journal.destroy');

    Route::get('adjustment_journal/{id}', [App\Http\Controllers\AdjustmentJournalController::class, 'index'])->name('adjustment_journal.index');
    Route::post('adjustment_journal/{id}', [App\Http\Controllers\AdjustmentJournalController::class, 'store'])->name('adjustment_journal.store');
    Route::put('adjustment_journal/{id}', [App\Http\Controllers\AdjustmentJournalController::class, 'update'])->name('adjustment_journal.update');
    Route::delete('adjustment_journal/{id}', [App\Http\Controllers\AdjustmentJournalController::class, 'destroy'])->name('adjustment_journal.destroy');

    Route::get('invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'index'])->name('invoice.index');
    Route::post('invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'store'])->name('invoice.store');
    Route::put('invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'update'])->name('invoice.update');
    Route::delete('invoice/{id}', [App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoice.destroy');

    Route::get('payroll/{id}', [App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::post('payroll/{id}', [App\Http\Controllers\PayrollController::class, 'store'])->name('payroll.store');
    Route::put('payroll/{id}', [App\Http\Controllers\PayrollController::class, 'update'])->name('payroll.update');
    Route::delete('payroll/{id}', [App\Http\Controllers\PayrollController::class, 'destroy'])->name('payroll.destroy');
    // Route::post('/payroll/payment', [App\Http\Controllers\PayrollController::class, 'baru'])->name('payroll.payment_baru');
    Route::post('/payroll/payment/{id}', [App\Http\Controllers\PayrollController::class, 'payment'])->name('payroll.payment_baru');

    Route::get('/neraca-saldo/{id}', [App\Http\Controllers\LedgerController::class, 'neracaSaldo'])->name('Ledger.neraca_saldo');
    Route::get('/neraca-saldo/export', [App\Http\Controllers\LedgerController::class, 'exportExcel'])->name('Ledger.export_neraca_saldo');
    Route::get('/opening-balance', [App\Http\Controllers\OpeningBalanceController::class, 'index'])->name('opening-balance.index');
    Route::get('/neraca-lajur/{id}', [App\Http\Controllers\LedgerController::class, 'neracaLajur'])->name('Ledger.neraca_lajur');
    Route::get('/arus-kas/{id}', [App\Http\Controllers\LedgerController::class, 'arus_kas'])->name('Ledger.arus_kas');
    Route::get('/jurnal-penutup/{id}', [App\Http\Controllers\LedgerController::class, 'jurnal_penutup'])->name('Ledger.jurnal_penutup');
    Route::post('/jurnal-tutup/{id}', [App\Http\Controllers\LedgerController::class, 'closeAccount'])->name('Ledger.close_account');
    Route::put('/jurnal-tutup/{id}/{closingId}', [App\Http\Controllers\LedgerController::class, 'updateAccountClosing'])->name('Ledger.update_account_closing');
    Route::delete('/jurnal-tutup/{id}/{closingId}', [App\Http\Controllers\LedgerController::class, 'deleteAccountClosing'])->name('Ledger.delete_account_closing');
    Route::get('finance/report/worksheet/export-laba-rugi/{id}', [App\Http\Controllers\LedgerController::class, 'export_laba_rugi']);
    Route::get('finance/report/worksheet/export-neraca/{id}', [App\Http\Controllers\LedgerController::class, 'export_neraca']);
    Route::get('finance/report/cash-flow/export/{id}', [App\Http\Controllers\LedgerController::class, 'export_cash_flow']);
    Route::post('/invoice/payment/{id}', [App\Http\Controllers\InvoiceController::class, 'payment'])->name('invoices.payment');
    Route::get('/invoice/pdf/{id}', [App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('invoices.generate_pdf');
    Route::get('finance_ledger/list_account/{id}', [App\Http\Controllers\LedgerController::class, 'pilih_akun'])->name('finance_ledger.list_account');
    Route::get('finance_ledger/show/{perusahaan}/{id}', [App\Http\Controllers\LedgerController::class, 'show'])->name('finance_ledger.show');
    Route::get('profit/list/{id}', [App\Http\Controllers\InvoiceController::class, 'list_client'])->name('profit.list');
    Route::get('profit/detail/{id}', [App\Http\Controllers\InvoiceController::class, 'detail'])->name('profit.detail');
    Route::get('/PDF/file/{file}/view', [App\Http\Controllers\PDFController::class, 'viewFile'])->name('PDF.files.view');
    Route::get('/PDF/file/{file}/preview', [App\Http\Controllers\PDFController::class, 'previewFile'])->name('PDF.files.preview');
    Route::get('/PDF/file/{file}/versions/{version}/preview', [App\Http\Controllers\PDFController::class, 'previewVersion'])->name('PDF.files.versions.preview');
    Route::get('/PDF/{id_produk}', [App\Http\Controllers\PDFController::class, 'index'])->name('PDF');
    Route::post('/PDF/document/create', [App\Http\Controllers\PDFController::class, 'createDocument'])->name('PDF.documents.store');
    Route::resource('PDF', App\Http\Controllers\PDFController::class);
    Route::post('/opening-balance/save', [App\Http\Controllers\OpeningBalanceController::class, 'save'])->name('opening-balance.save');
});

Route::post('/ai/chat-proxy', function (Request $request) {
    $validated = $request->validate([
        'message' => ['nullable', 'string'],
        'raw_input' => ['required', 'string'],
        'type' => ['required', 'in:chat,search_affiliator'],
        'source' => ['nullable', 'string'],
        'user_id' => ['nullable'],
        'divisi_id' => ['nullable'],
        'timestamp' => ['nullable', 'date'],
    ]);

    try {
        $n8nResponse = Http::acceptJson()
            ->asJson()
            ->timeout(60)
            ->post(
                'https://bmglbl3.n8n.bocindonesia.com/webhook-test/96f14d6e-6636-4df4-b3af-ef6342d1d1b9',
                $validated
            );

        $responseBody = $n8nResponse->json();

        if ($responseBody === null) {
            $responseBody = [
                'response' => $n8nResponse->body(),
            ];
        }

        return response()->json(
            $responseBody,
            $n8nResponse->status()
        );
    } catch (\Throwable $exception) {
        Log::error('Gagal menghubungi webhook n8n', [
            'message' => $exception->getMessage(),
        ]);

        return response()->json([
            'message' => 'Gagal menghubungi layanan AI.',
        ], 500);
    }
})->name('api.ai.chat-proxy');

Route::middleware('guest')->group(function () {
    Route::get('/', [App\Http\Controllers\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/post-login', [App\Http\Controllers\LoginController::class, 'login'])->name('post-login');
});
Route::post('/n8n-trigger', [App\Http\Controllers\BarangClientController::class, 'trigger'])->name('n8n.trigger');
Route::post('/n8n-trigger-baru-lagi', [App\Http\Controllers\BarangClientController::class, 'triggerBaru'])->name('n8n.triggerBaru');
