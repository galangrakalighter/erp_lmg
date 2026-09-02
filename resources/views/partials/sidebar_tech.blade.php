<a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseTech">
    Tech
</a>

@php
    $techProdukList = $techProduk->contains('client_id', $currentProduct);
    $isPdf = Route::is('PDF');
    $isTechCMS = Route::is('client.produk_divisi') && request()->route('division') == 5;
@endphp
<div id="collapseTech" class="collapse {{ $techProdukList && $isPdf || $isTechCMS || ($masterFolderDivisionId == 5) ? 'show' : '' }}"
    aria-labelledby="headingTech" data-parent="#collapseDivisi">
    <div class="collapse-container px-2 py-2">
        
        @if ($isSuperAdmin || ($myDivision->name == "Tech" && $myRole->level < 2))
            <div class="mb-2">
                <a href="{{ route('client.produk_divisi', 5) }}" class="btn-cms-link client-sub-menu-link-client d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ $isTechCMS ? 'active' : '' }}">
                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                </a>
            </div>
            
            {{-- PERBAIKAN: Hapus {{ $isPdf ? 'collapse' : '' }} dari tag <a> ini --}}
            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2" 
            data-toggle="collapse" 
            data-target="#cat_tech"
            aria-expanded="{{ $isPdf ? 'true' : 'false' }}">
                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                <i class="fas fa-caret-down arrow-icon-xs"></i>
            </a>
        @endif

        <div class="group-wrapper mb-2">
            <div id="cat_tech" class="collapse {{ $techProdukList && $isPdf ? 'show' : '' }} ml-2 border-left-custom">
                @foreach ($techProduk as $client)
                    @if($myDivision->name == "Tech" || $isSuperAdmin)
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
                                <a class="client-dropdown-link client-sub-menu-link-client text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient || ($techProdukList && $isPdf) || $isTechCMS ? 'collapsed' : '' }}" 
                                data-toggle="collapse" 
                                data-target="#client_tech_{{ $client->client_id }}" 
                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                    <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                </a>

                                {{-- Bagian Isi Sub-Menu (Overview, Github, Monitoring, File) --}}
                                <div id="client_tech_{{ $client->client_id }}" class="collapse {{ $isActiveClient || ($isActiveClient && $isPdf) ? 'show' : '' }}">
                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                        <div class="client-item-wrapper mb-1">
                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_overview') ? 'active' : '' }}" href="#">
                                                <i class="fas fa-eye mr-2 text-primary"></i> <span>Overview</span>
                                            </a>
                                        </div>
                                        <div class="client-item-wrapper mb-1">
                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_github') ? 'active' : '' }}" href="#">
                                                <i class="fab fa-github mr-2 text-dark"></i> <span>Github</span>
                                            </a>
                                        </div>
                                        <div class="client-item-wrapper mb-1">
                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ request()->is('tech_monitoring') ? 'active' : '' }}" href="#">
                                                <i class="fas fa-desktop mr-2 text-success"></i> <span>Monitoring</span>
                                            </a>
                                        </div>
                                        <div class="client-item-wrapper mb-1">
                                            <a class="d-flex client-sub-menu-link align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active' : '' }}" href="{{ route('PDF', ['id_produk' => $client->id]) }}">
                                                <span>File</span>
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

        @if($isSuperAdmin || $auth->userDivisions->contains('division_id', 5))
            <div class="mb-2">
                <a href="{{ route('master-folder.index', ['division_id' => 5]) }}" class="btn-cms-link client-sub-menu-link-client d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover {{ Route::is('master-folder.*') && $masterFolderDivisionId == 5 ? 'active' : '' }}">
                    <i class="fas fa-folder-open mr-2 text-warning"></i> <span>Folder Master</span>
                </a>
            </div>
        @endif

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
