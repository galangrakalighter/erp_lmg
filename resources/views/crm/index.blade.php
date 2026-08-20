@extends('layout.app')
@section('content')

    {{-- CARD UTAMA --}}
   <div class="card shadow mb-4">
        {{-- CARD HEADER --}}
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">CRM Leads Ekosistem LMG</h6>
            <button type="button" class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#tambahModal">
                <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Lead
            </button>
        </div>

        {{-- FILTER SECTION --}}
        <div class="card-body border-bottom">
            <form action="{{ url()->current() }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-dark">Dari Tanggal:</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date', now()->subDay()->toDateString()) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-dark">Sampai Tanggal:</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date', now()->toDateString()) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-dark">Layanan:</label>
                        <select name="layanan" class="form-control form-control-sm">
                            <option value="">Semua Layanan</option>
                            @foreach($filter_layanan as $layanan)
                                <option value="{{ $layanan }}" {{ request('layanan') == $layanan ? 'selected' : '' }}>{{ $layanan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small font-weight-bold text-dark">Status:</label>
                        <select name="stage" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            @foreach($stages as $id => $stage)
                                <option value="{{ $id }}" {{ request('stage') == $id ? 'selected' : '' }}>{{ $stage['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter fa-sm mr-1"></i> Filter
                        </button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo fa-sm mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- TABLE SECTION --}}
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover text-center" id="dataTable" width="100%">
                    <thead class="thead-light text-nowrap">
                        <tr>
                            <th>No</th>
                            <th>Chat Terbaru</th>
                            <th>Pertama Kali Chat</th>
                            <th>Nama Client</th>
                            <th>Kategori Lead</th>
                            <th>Email Client</th>
                            <th>Nama Usaha</th>
                            <th>No Telp</th>
                            <th>Layanan</th>
                            <th>Kendala</th>
                            <th>Kebutuhan</th>
                            <th>Sosial Media</th>
                            <th style="min-width: 200px;">Kesimpulan</th> {{-- Berikan min-width agar proporsional --}}
                            <th>Jadwal Meeting</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @foreach($leads as $lead)
                        <tr>
                            <td class="align-middle">{{ $loop->iteration }}</td>
                            
                            {{-- Chat Terbaru --}}
                            <td class="text-nowrap italic align-middle">
                                {{ $lead->chat_terbaru ? $lead->chat_terbaru->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                            </td>

                            {{-- Pertama Kali Chat --}}
                            <td class="text-nowrap align-middle">
                                {{ $lead->created_at ? $lead->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                            </td>
                            
                            {{-- Nama Client --}}
                            <td class="font-weight-bold text-left align-middle">{{ $lead->nama ?? 'No Name' }}</td>
                            <td class="font-weight-bold text-left align-middle">{{ $lead->kategori ?? '-' }}</td>
                            <td class="font-weight-bold text-left align-middle">{{ $lead->email ?? '-' }}</td>

                            {{-- Nama Usaha --}}
                            <td class="text-left align-middle">{{ $lead->nama_usaha ?? '-' }}</td>

                            {{-- No Telepon --}}
                            <td class="align-middle">
                                @if($lead->phone)
                                <a href="https://wa.me/{{ $lead->phone }}" target="_blank" class="text-decoration-none">
                                    <small class="badge badge-light border text-primary">
                                        <i class="fab fa-whatsapp text-success mr-1"></i>
                                        {{ $lead->phone }}
                                    </small>
                                </a>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            
                            {{-- Layanan --}}
                            <td class="text-left align-middle">
                                @if($lead->layanan)
                                    <span class="badge badge-pill badge-light border px-3 py-2 text-secondary font-weight-normal shadow-sm" 
                                        title="{{ $lead->layanan }}" 
                                        style="font-size: 0.8rem; display: inline-flex; align-items: center; max-width: 220px;">
                                        <i class="fas fa-layer-group text-info mr-1.5 flex-shrink-0"></i>
                                        <span class="text-truncate">{{ $lead->layanan }}</span>
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <td class="font-weight-bold text-left align-middle">{{ $lead->kendala ?? '-' }}</td>
                            <td class="font-weight-bold text-left align-middle">{{ $lead->kebutuhan ?? '-' }}</td>
                            <td class="font-weight-bold text-left align-middle social-column" data-social="{{ $lead->sosial_media }}">{{ $lead->sosial_media ?? '-' }}</td>
                            
                            {{-- Kesimpulan (Dibuat Truncate + Tombol Lihat Full) --}}
                            <td class="text-left align-middle">
                                @if($lead->kesimpulan)
                                    <div style="max-height: 75px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; font-size: 0.85rem;" title="{{ $lead->kesimpulan }}">
                                        {{ $lead->kesimpulan }}
                                    </div>
                                    @if(strlen($lead->kesimpulan) > 100)
                                        <button type="button" class="btn btn-link btn-sm p-0 mt-1" style="font-size: 0.75rem;" data-toggle="modal" data-target="#detailKesimpulanModal_{{ $lead->id }}">
                                            Selengkapnya...
                                        </button>
                                    @endif
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Jadwal Meeting --}}
                            <td class="text-nowrap align-middle">
                                @if($lead->meeting)
                                    <span class="small font-weight-bold text-success">
                                        {{ $lead->meeting->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                                    </span>
                                    @if($lead->link_meet)
                                        <br>
                                        <a href="{{ $lead->link_meet }}" target="_blank" class="btn btn-outline-primary btn-sm mt-1 px-2 py-0" style="font-size: 0.75rem;">
                                            <i class="fas fa-video mr-1"></i> Join Meet
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="align-middle">
                                @php
                                    $statusBadge = [
                                        1 => 'badge-primary', 
                                        2 => 'badge-warning text-white', 
                                        3 => 'badge-success text-white', 
                                    ][$lead->status] ?? 'badge-light';
                                @endphp
                                <span class="badge {{ $statusBadge }} px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                    {{ $stages[$lead->status]['label'] ?? 'Unknown' }}
                                </span>
                            </td>

                            {{-- Action Buttons --}}
                            <td class="text-nowrap align-middle">
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal_{{ $lead->id }}" title="Edit">
                                    <i class="fas fa-edit fa-sm"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal_{{ $lead->id }}" title="Hapus">
                                    <i class="fas fa-trash fa-sm"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
   </div>

   {{-- MODAL POPUP KESIMPULAN LENGKAP (Opsional untuk setiap lead yang panjang) --}}
   @foreach($leads as $lead)
       @if($lead->kesimpulan && strlen($lead->kesimpulan) > 100)
       <div class="modal fade" id="detailKesimpulanModal_{{ $lead->id }}" tabindex="-1" role="dialog" aria-hidden="true">
           <div class="modal-dialog" role="document">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title font-weight-bold text-primary" style="font-size: 1rem;">Kesimpulan Lead: {{ $lead->nama ?? 'Client' }}</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body text-left">
                       <p style="white-space: pre-line; font-size: 0.9rem;">{{ $lead->kesimpulan }}</p>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Tutup</button>
                   </div>
               </div>
           </div>
       </div>
       @endif
   @endforeach

   <script>
        document.addEventListener("DOMContentLoaded", function () {
            const socialCells = document.querySelectorAll(".social-column");

            socialCells.forEach(cell => {
                const rawText = cell.getAttribute("data-social");
                if (!rawText || rawText === "-") return;

                // Pisahkan berdasarkan koma
                const items = rawText.split(",");
                let htmlLinks = [];

                items.forEach(item => {
                    item = item.trim();
                    if (!item) return;

                    let url = "#";
                    let label = item;

                    // Cek apakah sudah berupa URL valid
                    if (item.startsWith("http://") || item.startsWith("https://")) {
                        url = item;
                        // Rapikan label jika dari Instagram/TikTok
                        if (item.includes("instagram.com")) {
                            try {
                                const path = new URL(item).pathname;
                                label = "Instagram (" + path.replace(/\//g, "") + ")";
                            } catch (e) {
                                label = "Instagram Link";
                            }
                        } else if (item.includes("tiktok.com")) {
                            try {
                                const path = new URL(item).pathname;
                                label = "TikTok (" + path.replace(/\//g, "") + ")";
                            } catch (e) {
                                label = "TikTok Link";
                            }
                        } else {
                            try {
                                label = new URL(item).hostname;
                            } catch (e) {
                                label = item;
                            }
                        }
                    } 
                    // Cek jika formatnya handle seperti @hey.livcoffee
                    else if (item.startsWith("@")) {
                        const cleanHandle = item.replace("@", "");
                        url = "https://www.instagram.com/" + cleanHandle; // Sesuaikan default platform
                        label = item;
                    }

                    // Buat elemen anchor
                    htmlLinks.push(`<a href="${url}" target="_blank" rel="noopener noreferrer" class="text-primary mr-2">${label}</a>`);
                });

                // Masukkan kembali ke dalam sel tabel
                cell.innerHTML = htmlLinks.length > 0 ? htmlLinks.join(" | ") : "-";
            });
        });
   </script>

@endsection