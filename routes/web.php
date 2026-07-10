<?php

use Illuminate\Support\Facades\Route;

Route::get('/terms-privacy-policy', function(){
    return view('privacy');
});
Route::post('/n8n-trigger', [App\Http\Controllers\BarangClientController::class, 'trigger'])->name('n8n.trigger');
Route::middleware('auth')->group(function() {
    // Dashboard
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    
    // Logout
    Route::post("/logout", [App\Http\Controllers\LoginController::class, 'logout'])->name('logout');
    
    // Analisis Konten (Ambil Data dari Access Token Graph Instagram)
    Route::get('/analisis/{id_produk}/{platform}', [App\Http\Controllers\AnalisisController::class, 'index'])->name('analisis');

    // Market Research
    Route::get('/market_research/{id_produk}/{platform}', [App\Http\Controllers\marketResearchController::class, 'index'])->name('market_research');
    
    // Branding
    Route::get('/branding/{id_produk}/{platform}', [App\Http\Controllers\brandingController::class, 'index'])->name('branding');
    
    // Export PDF
    Route::get('/exportPDF/{id}/{halaman}', [App\Http\Controllers\PDFController::class, 'exportPDF'])->name('invoice.download');

    // MANAJEMEN USER
    Route::resource('manajemen_user', App\Http\Controllers\UserController::class);
    
    Route::resource('crm', App\Http\Controllers\CRMController::class);
    
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
    Route::get("/manajemen/{id_produk}/{platform}", [App\Http\Controllers\ManajemenKontenController::class, 'index'])->name('manajemen');
    Route::post("/manajemen/{id_produk}/create", [App\Http\Controllers\ManajemenKontenController::class, 'store'])->name('manajemen_create');
    Route::post("/manajemen/{id_produk}/edit", [App\Http\Controllers\ManajemenKontenController::class, 'update'])->name('manajemen_update');
    Route::post("/manajemen/{id_produk}/delete", [App\Http\Controllers\ManajemenKontenController::class, 'destroy'])->name('manajemen_delete');

    // Produk
    Route::get('cms_{nama}/produk', [App\Http\Controllers\ProdukController::class, 'index'])->name('cms.produk');
    Route::get('cms/docs', [App\Http\Controllers\ProdukController::class, 'docs'])->name('docs');
    Route::post('cms/produk/create', [App\Http\Controllers\ProdukController::class, 'store'])->name('produk_create');
    Route::post('cms/produk/edit/{id}', [App\Http\Controllers\ProdukController::class, 'update'])->name('produk_edit');
    Route::post('cms/produk/delete/{id}', [App\Http\Controllers\ProdukController::class, 'destroy'])->name('produk_delete');

    Route::get('client/{division}', [App\Http\Controllers\ProdukController::class, 'indexDivision'])->name('client.produk_divisi');
    
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
    
    //Segmentasi 
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
    Route::get('dashboard_hc', [App\Http\Controllers\HCOverviewController::class, 'dashboard'])->name('overview.dashboard');

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
    // Route::get('/payment', function(){
    //     $title = "Payment - ";
    //     return view('payment.index', compact('title'));
    // })->name('payment.index');

    // Route::post('/payment/get-token', [App\Http\Controllers\HCDailyController::class, 'getPaymentToken'])->name('payment.getToken');

    Route::get('/finance_coa/data/akun', [App\Http\Controllers\COAController::class, 'getCoaData'])->name('finance_coa.data');
    Route::get('/general_journal/data/jurnal', [App\Http\Controllers\GeneralJournalController::class, 'getGeneralJournalData'])->name('general_journal.data');
    Route::resource('finance_coa', App\Http\Controllers\COAController::class);
    Route::resource('general_journal', App\Http\Controllers\GeneralJournalController::class);
    Route::resource('adjustment_journal', App\Http\Controllers\AdjustmentJournalController::class);
    Route::get('/neraca-saldo', [App\Http\Controllers\LedgerController::class, 'neracaSaldo'])->name('Ledger.neraca_saldo');
    Route::get('/neraca-saldo/export', [App\Http\Controllers\LedgerController::class, 'exportExcel'])->name('Ledger.export_neraca_saldo');
    Route::get('/opening-balance', [App\Http\Controllers\OpeningBalanceController::class, 'index'])->name('opening-balance.index');
    Route::get('/neraca-lajur', [App\Http\Controllers\LedgerController::class, 'neracaLajur'])->name('Ledger.neraca_lajur');
    Route::get('/arus-kas', [App\Http\Controllers\LedgerController::class, 'arus_kas'])->name('Ledger.arus_kas');
    Route::get('/jurnal-penutup', [App\Http\Controllers\LedgerController::class, 'jurnal_penutup'])->name('Ledger.jurnal_penutup');
    Route::post('/jurnal-tutup', [App\Http\Controllers\LedgerController::class, 'lockPeriode'])->name('Ledger.lock_periode');
    Route::get('finance/report/worksheet/export-laba-rugi', [App\Http\Controllers\LedgerController::class, 'export_laba_rugi']);
    Route::get('finance/report/worksheet/export-neraca', [App\Http\Controllers\LedgerController::class, 'export_neraca']);
    Route::get('finance/report/cash-flow/export', [App\Http\Controllers\LedgerController::class, 'export_cash_flow']);
    Route::post('/invoice/payment/{id}', [App\Http\Controllers\InvoiceController::class, 'payment'])->name('invoices.payment');
    Route::get('/invoice/pdf/{id}', [App\Http\Controllers\InvoiceController::class, 'generatePdf'])->name('invoices.generate_pdf');
    Route::get('finance_ledger/list_account', [App\Http\Controllers\LedgerController::class, 'pilih_akun'])->name('finance_ledger.list_account');
    Route::resource('finance_ledger', App\Http\Controllers\LedgerController::class);
    Route::resource('invoice', App\Http\Controllers\InvoiceController::class);
    Route::get("/PDF/{id_produk}", [App\Http\Controllers\PDFController::class, 'index'])->name('PDF');
    Route::resource('PDF', App\Http\Controllers\PDFController::class);
    Route::post('/opening-balance/save', [App\Http\Controllers\OpeningBalanceController::class, 'save'])->name('opening-balance.save');
});

Route::middleware('guest')->group(function() {
    Route::get('/', [App\Http\Controllers\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/post-login', [App\Http\Controllers\LoginController::class, 'login'])->name('post-login');
});