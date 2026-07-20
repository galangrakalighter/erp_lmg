<a class="collapse-item dropdown-toggle" href="#" data-toggle="collapse" data-target="#collapseEcommerce">
    Ecommerce
</a>

<div id="collapseEcommerce" class="collapse {{ request()->is('*ecommerce*') ? 'show' : '' }}" 
    aria-labelledby="headingEcommerce" data-parent="#collapseDivisi">
    <div class="collapse-container px-2 py-2">
        
        @if ($isSuperAdmin || ($myDivision->name == "Ecommerce" && $myRole->level < 4))
            <div class="mb-2">
                <a href="{{ route('client.produk_divisi', 7) }}" class="btn-cms-link d-flex align-items-center text-dark py-2 px-2 rounded text-decoration-none transition-hover">
                    <i class="fas fa-cog mr-2"></i> <span>Dashboard CMS</span>
                </a>
            </div>
            <a class="category-dropdown-link d-flex align-items-center justify-content-between p-2 rounded text-decoration-none text-dark transition-hover mb-2" 
            data-toggle="collapse" 
            data-target="#cat_ecommerce">
                <span><i class="fas fa-folder-open mr-2 text-info"></i>Client</span>
                <i class="fas fa-caret-down arrow-icon-xs"></i>
            </a>
        @endif


        <div class="group-wrapper mb-2">
            <div id="cat_ecommerce" class="collapse ml-2 border-left-custom">
                @foreach ($ecommerceProduk as $client)
                    @if($myDivision->name == "Ecommerce" || $isSuperAdmin)
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
                                <a class="client-dropdown-link text-dark d-flex align-items-center justify-content-between p-2 rounded text-decoration-none transition-hover {{ !$isActiveClient ? 'collapsed' : '' }}" 
                                data-toggle="collapse" 
                                data-target="#client_ecommerce_{{ $client->client_id }}" 
                                aria-expanded="{{ $isActiveClient ? 'true' : 'false' }}">
                                    <span class="text-truncate"><i class="far fa-user-circle mr-2 text-secondary"></i>{{ $client->nama }}</span>
                                    <i class="fas fa-caret-right arrow-icon-xs"></i>
                                </a>

                                {{-- Bagian Isi Sub-Menu (File / Submenu opsional) --}}
                                <div id="client_ecommerce_{{ $client->client_id }}" class="collapse {{ $isActiveClient ? 'show' : '' }}">
                                    <div class="platform-sub-links pl-3 pt-1 pb-2">
                                        <div class="client-item-wrapper mb-1">
                                            <a class="d-flex align-items-center text-dark py-1 px-2 rounded text-decoration-none transition-hover {{ Route::is('PDF') && $isActiveClient ? 'active-menu bg-light font-weight-bold' : '' }}" href="{{ route('PDF', ['id_produk' => $client->id]) }}">
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