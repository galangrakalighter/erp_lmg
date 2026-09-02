@php
    // Daftar semua route yang termasuk di bawah menu MCN
    $isMCNPage = Route::is([
        'barang_client.index',
        'social_creator.index',
        'blacklist.index',
        'tools.index',
        'gform_affiliate.index',
        'selection.index',
    ]);

    $isMCNCMS = Route::is('client.produk_divisi') && request()->route('division') == 3;

    $isMCNProduct = $mcnProduk->contains('client_id', $currentProduct);

    $isPdf = Route::is('PDF');
@endphp

<a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseMCN">
    MCN
</a>

<div id="collapseMCN" class="collapse {{ $isMCNPage || ($isMCNProduct && $isPdf) || $isMCNCMS ? 'show' : '' }}"
    aria-labelledby="headingMCN" data-parent="#collapseDivisi">
    <div class="collapse-container px-2 py-2">
        
        @if  ($isSuperAdmin || ($myDivision->name == "MCN" && $myRole->level < 4))
            <div class="mb-2">
                <a href="{{ route('client.produk_divisi', 3) }}" class="btn-cms-link client-sub-menu-link-client d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ $isMCNCMS ? 'active' : '' }}">
                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                </a>
            </div>
            <a class="category-dropdown-link client-sub-menu-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2 {{ $isPdf ? 'collapsed' : '' }}" 
            data-toggle="collapse" 
            data-target="#cat_mcn">
                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                <i class="fas fa-caret-down arrow-icon-xs dropdown-arrow"></i>
            </a>
        @endif

        <div class="group-wrapper mb-2">
            <div id="cat_mcn" class="collapse {{ ($isPdf) ? 'show' : '' }} ml-2 border-left-custom">
                @foreach ($mcnProduk as $client)
                    @if($myDivision->name == "MCN" || $isSuperAdmin)
                        @php
                            // 1. Cek apakah user punya akses eksplisit ke client ini (dari tabel user_client)
                            $hasClientAccess = in_array($client->client_id, $myClientIds);

                            // 2. Tentukan Power User: Super Admin, Role Admin, atau Head of Division
                            $isPowerUser = $isSuperAdmin || $myRole->role_name == "Busines Unit Head";

                            // 3. Syarat Utama Membuka Dropdown Client
                            $canOpen = $isPowerUser || (in_array(1, $myDivision->pluck('id')->toArray()) && $hasClientAccess);
                            $isActiveClient = $currentProduct == $client->client_id;
                        @endphp

                        <div class="client-item-wrapper mb-1">
                            {{-- Bagian Header Client --}}
                            @if($canOpen)
                                <a class="client-dropdown-link client-sub-menu-link-client text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }} {{ $isActiveClient ? 'active' : '' }}" 
                                data-toggle="collapse" 
                                data-target="#client_mcn_{{ $client->client_id }}" 
                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                    <span class="text-truncate font-weight-bold"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                    <i class="fas fa-caret-right arrow-icon-xs dropdown-arrow"></i>
                                </a>

                                {{-- Bagian Isi Platform --}}
                                <div id="client_mcn_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                        <div class="client-item-wrapper mb-1">
                                            <a href="{{ route('PDF', ['id_produk' => $client->id]) }}" class="client-sub-menu-link d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active' : '' }}">
                                                <span>File Dokumen</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Tampilan untuk yang tidak punya akses --}}
                                <div class="p-2 text-muted small rounded" style="opacity: 0.6; cursor: not-allowed;">
                                    <span><i class="far fa-user-circle mr-2"></i>{{ $client->nama }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>


        @if($myDivision->name == "MCN" || $isSuperAdmin)
            <div class="client-item-wrapper mb-2">
                <a href="{{ route('barang_client.index') }}" class="client-sub-menu-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('barang_client.index') ? 'active' : '' }}">
                    <i class="fas fa-boxes mr-2 text-warning"></i> <span>Project MCN</span>
                </a>
            </div>

            <div class="client-item-wrapper mb-2">
                <a href="{{ route('social_creator.index') }}" class="client-sub-menu-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('social_creator.index') ? 'active' : '' }}">
                    <i class="fas fa-users mr-2 text-primary"></i> <span>Affiliator</span>
                </a>
            </div>

            <div class="client-item-wrapper mb-2">
                <a href="{{ route('blacklist.index') }}" class="client-sub-menu-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('blacklist.index') ? 'active' : '' }}">
                    <i class="fas fa-ban mr-2 text-danger"></i> <span>Blacklist</span>
                </a>
            </div>
            
            <div class="client-item-wrapper mb-2">
                <a href="{{ route('tools.index') }}" class="client-sub-menu-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('tools.index') ? 'active' : '' }}">
                    <i class="fas administrative-tools mr-2 text-success"></i> <span>Tools</span>
                </a>
            </div>
        @endif

        @foreach ($mcnClient as $item)
            @php
                $isItemActive = request()->is('project/' . $item->id . '*');
            @endphp
            <a class="client-dropdown-link client-sub-menu-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover mb-1 {{ !$isItemActive ? 'collapsed' : '' }} {{ $isItemActive ? 'active' : '' }}" 
            data-toggle="collapse" 
            data-target="#client_mcn_project_{{ $item->id }}" 
            aria-expanded="{{ $isItemActive ? 'true' : 'false' }}">
                <span class="text-uppercase font-weight-bold text-truncate">
                    <i class="far fa-user-circle mr-2 text-secondary"></i>{{ strtoupper($item->project) }}
                </span>
                <i class="fas fa-caret-down arrow-icon-xs dropdown-arrow"></i>
            </a>

            {{-- Konten Sub-Menu Project --}}
            <div id="client_mcn_project_{{ $item->id }}" class="collapse {{ request()->segment(2) == $item->id ? 'show' : '' }}">
                <div class="platform-sub-links pl-3 pt-1 pb-2"> 
                    
                    {{-- Link Gform --}}
                    <div class="client-item-wrapper mb-1">
                        <a href="{{ route('gform_affiliate.index', ['project_id' => $item->id]) }}" class="client-sub-menu-link d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('gform_affiliate/' . $item->id) ? 'active' : '' }}">
                            <i class="fas fa-file-alt mr-2 text-info"></i><span>Gform</span>
                        </a>
                    </div>

                    {{-- Link Hasil Seleksi --}}
                    <div class="client-item-wrapper mb-1">
                        <a href="{{ route('selection.index', ['project_id' => $item->id]) }}" class="client-sub-menu-link d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('selection/' . $item->id) ? 'active' : '' }}">
                            <i class="fas fa-clipboard-check mr-2 text-success"></i><span>Hasil Seleksi</span>
                        </a>
                    </div>
                    
                </div>
            </div>
        @endforeach

        <style>
            .transition-hover {
                transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
            }
            .transition-hover:hover {
                background-color: #f8f9fc !important;
                color: #4e73df !important;
            }
        </style>
    </div>
</div>
