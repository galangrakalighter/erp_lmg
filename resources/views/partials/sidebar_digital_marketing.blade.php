<a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseDM">
    Digital Marketing
</a>

<div id="collapseDM" class="collapse {{ ($isMarketingPage && $isDMProduct) ? 'show' : '' }}" 
    aria-labelledby="headingDM" data-parent="#collapseDivisi">
    <div class="collapse-container px-2">

        @if ($isSuperAdmin || (($myDivision->name == "Digital Marketing" || $myDivision->name == "Ecommerce") && $myRole->level < 3))
            <div class="brand-sub-header text-danger mb-2 px-2">
                <i class="fas fa-store mr-2"></i> MR. CREPE
            </div>
            <div class="mb-2">
                <a href="{{ route('cms.produk', 'LGTR') }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                </a>
            </div>
        
            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover {{ !$isMarketingPage ? 'collapsed' : '' }}" 
                data-toggle="collapse" 
                data-target="#cat_dm" 
            aria-expanded="{{ $isMarketingPage ? 'true' : 'false' }}">
                <span><i class="fas fa-folder-open mr-2 text-warning"></i>Client</span>
                <i class="fas fa-caret-down arrow-icon-xs"></i>
            </a>
        @endif

        <div id="cat_dm" class="collapse {{ $isMarketingPage ? 'show' : '' }} ml-2 border-left-custom">
            @foreach ($digitalMarketingProduk as $client)
                @if($myDivision->name == "Digital Marketing" || $myDivision->name == "Ecommerce" || $isSuperAdmin)
                    @php
                        $hasClientAccess = in_array($client->client_id, $myClientIds);
                        $isPowerUser = $isSuperAdmin || $myRole->role_name == "Performance & Reporting";
                        // Jika division Ecommerce, kita berikan izin canOpen bernilai true agar bisa melihat semua isi sidebar
                        $canOpen = $isPowerUser || $myDivision->name == "Ecommerce" || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                        $isActiveClient = $currentProduct == $client->client_id;
                    @endphp

                    <div class="client-item-wrapper mb-1">
                        @if($canOpen)
                            <a class="client-dropdown-link client-sub-menu-link-client text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }} {{ $isActiveClient ? 'active' : '' }}" 
                                data-toggle="collapse" 
                                data-target="#client_lgtr_{{ $client->client_id }}" 
                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                <span class="text-truncate font-weight-bold"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                <i class="fas fa-caret-right arrow-icon-xs dropdown-arrow"></i>
                            </a>

                            <div id="client_lgtr_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                <div class="platform-sub-links pl-3 pt-1 pb-2">
                                    @php
                                        $accessToken = json_decode($client->access_token, true);
                                        $platforms = collect($accessToken['platform'] ?? [])->filter();
                                    @endphp

                                    @forelse ($platforms as $plt)
                                        <div class="platform-group mb-2">
                                            <div class="platform-label small font-weight-bold text-dark px-2 py-1">
                                                <i class="fab fa-{{ strtolower($plt) }} mr-1"></i> {{ $plt }}
                                            </div>
                                            <div class="d-flex flex-column pl-2">
                                                <a href="{{ route('market_research', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="client-sub-menu-link {{ Route::is('market_research') && $currentPlatform == $plt && $isActiveClient ? 'active' : '' }}">Market Research</a>
                                                <a href="{{ route('branding', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="client-sub-menu-link {{ Route::is('branding') && $currentPlatform == $plt && $isActiveClient ? 'active' : '' }}">Branding</a>
                                                <a href="{{ route('analisis', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="client-sub-menu-link {{ Route::is('analisis') && $currentPlatform == $plt && $isActiveClient ? 'active' : '' }}">Analisis</a>
                                                <a href="{{ route('manajemen', ['id_produk'=>$client->id, 'platform'=>$plt]) }}" class="client-sub-menu-link {{ Route::is('manajemen') && $currentPlatform == $plt && $isActiveClient ? 'active' : '' }}">Content Plan</a>
                                            </div>
                                        </div>
                                    @empty
                                        <span class="text-muted small p-2 d-block font-italic">Platform belum diset</span>
                                    @endforelse
                                    
                                    <a href="{{ route('PDF', ['id_produk'=>$client->id]) }}" class="client-sub-menu-link mt-2 d-flex align-items-center justify-content-between text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active' : '' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-file-pdf mr-2"></i>
                                            <span>File Dokumen</span>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>

        <style>
            .transition-hover {
                transition: background-color 0.2s ease-in-out;
            }
            .transition-hover:hover {
                background-color: #f8f9fc !important;
                color: #4e73df !important;
            }
        </style>
    </div>
</div>