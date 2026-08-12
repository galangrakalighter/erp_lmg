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
                            <th>Email Client</th>
                            <th>Nama Usaha</th>
                            <th>No Telp</th>
                            <th>Layanan</th>
                            <th>Jadwal Meeting</th>
                            <th>Status</th>
                            <th>Hot Prospek</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @foreach($leads as $lead)
                        <tr>
                            <td class="align-middle">{{ $loop->iteration }}</td>
                            
                            {{-- Chat Terbaru (chat_terbaru) --}}
                            <td class="text-nowrap italic align-middle">
                                {{ $lead->chat_terbaru ? $lead->chat_terbaru->format('d M Y, H:i') . ' WIB' : '-' }}
                            </td>
                            
                            {{-- Pertama Kali Chat (created_at) --}}
                            <td class="text-nowrap align-middle">
                                {{ $lead->created_at ? $lead->created_at->format('d M Y, H:i') . ' WIB' : '-' }}
                            </td>
                            
                            {{-- Nama Client (nama) --}}
                            <td class="font-weight-bold text-left align-middle">{{ $lead->nama ?? 'No Name' }}</td>
                            
                            <td class="font-weight-bold text-left align-middle">{{ $lead->email ?? '-' }}</td>

                            {{-- Nama Usaha (nama_usaha) --}}
                            <td class="text-left align-middle">{{ $lead->nama_usaha ?? '-' }}</td>

                            {{-- No Telepon (phone) --}}
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

                            {{-- Jadwal Meeting (meeting) --}}
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

                            {{-- Status (status) --}}
                            <td class="align-middle">
                                @php
                                    $statusBadge = [
                                        1 => 'badge-primary', // In Progress
                                        2 => 'badge-warning text-white', // Meeting Schedule
                                    ][$lead->status] ?? 'badge-light';
                                @endphp
                                <span class="badge {{ $statusBadge }} px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                    {{ $stages[$lead->status]['label'] ?? 'Unknown' }}
                                </span>
                            </td>

                            <td class="align-middle text-center">
                                @if($lead->hot_prospek)
                                    <span class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check mr-1"></i> Yes
                                    </span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1" style="opacity: 0.6;">
                                        <i class="fas fa-times mr-1"></i> No
                                    </span>
                                @endif
                            </td>

                            {{-- Action Buttons --}}
                            <td class="text-nowrap align-middle">
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal_{{ $lead->id }}">
                                    <i class="fas fa-edit fa-sm"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal_{$lead->id }}">
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

@endsection