@include('partials.header')

<div class="container-fluid mt-4 pb-5">
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
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-dark">Dari Tanggal:</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="small font-weight-bold text-dark">Sampai Tanggal:</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
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
                            <th>Tanggal Chat</th>
                            <th>Client Name</th>
                            <th>No Telp</th>
                            <th>Layanan</th>
                            <th>Project Value</th>
                            <th>Stage</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-dark">
                        @foreach($leads as $lead)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap italic">
                                {{ $lead->tanggal_chat ? $lead->tanggal_chat->format('Y-m-d') : '-' }}
                            </td>
                            <td class="font-weight-bold text-left">{{ $lead->name ?? 'No Name' }}</td>
                            <td>
                                <small class="badge badge-light border text-muted">
                                    {{ $lead->phone ? +$lead->phone : "" }}
                                </small>
                            </td>
                            <td class="text-left small italic">{{ Str::limit($lead->layanan, 50) }}</td>
                            <td class="font-weight-bold text-success text-nowrap">
                                {{ $lead->project_value ?? 'Rp 0' }}
                            </td>
                            <td>
                                {{-- Menggunakan class badge Bootstrap sesuai stage --}}
                                @php
                                    $badgeClass = [
                                        1 => 'badge-primary', // In Progress
                                        2 => 'badge-warning text-white', // Follow Up
                                        3 => 'badge-danger', // Lost
                                        4 => 'badge-secondary', // Schedule
                                        5 => 'badge-success' // Completed
                                    ][$lead->project_stage] ?? 'badge-light';
                                @endphp
                                <span class="badge {{ $badgeClass }} px-3 py-2 text-uppercase" style="font-size: 0.7rem;">
                                    {{ $stages[$lead->project_stage]['label'] ?? 'Unknown' }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal_{{ $lead->id }}">
                                    <i class="fas fa-edit fa-sm"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal_{{ $lead->id }}">
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
</div>
@foreach ($leads as $lead)
    {{-- Modal Edit --}}
    <div class="modal fade" id="editModal_{{ $lead->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel_{{ $lead->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('crm.update', $lead->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-header bg-gradient-warning text-white py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-edit text-warning"></i>
                            </div>
                            <h5 class="modal-title font-weight-bold">Edit Data Lead: {{ $lead->name }}</h5>
                        </div>
                        <button type="button" class="close text-white outline-none" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body p-4" style="background-color: #f8f9fc;">
                        
                        <div class="section-title mb-3 d-flex align-items-center">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Informasi Client</h6>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Nama Client</label>
                                    <input type="text" name="name" class="form-control" value="{{ $lead->name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ $lead->email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">WA ID / Proteksi</label>
                                    <input type="text" name="id_proteksi" class="form-control" value="{{ $lead->id_proteksi }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="section-title mb-3 d-flex align-items-center mt-4">
                            <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                            <h6 class="text-warning font-weight-bold mb-0">Progress & Project Value</h6>
                        </div>

                        <div class="card border-0 shadow-sm rounded-lg mb-4">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Project Stage</label>
                                            <select name="project_stage_id" class="form-control custom-select bg-light border-0">
                                                @foreach([1 => 'IN PROGRESS', 2 => 'FOLLOW UP', 3 => 'LOST', 4 => 'SCHEDULE', 5 => 'COMPLETED'] as $id => $label)
                                                    <option value="{{ $id }}" {{ $lead->project_stage_id == $id ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Phone Number</label>
                                            <input type="text" name="phone" class="form-control bg-light border-0" value="{{ $lead->phone }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-0">
                                            <label class="text-dark font-weight-bold small">Project Value</label>
                                            <input type="text" name="project_value" class="form-control bg-light border-0" value="{{ $lead->project_value }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Tanggal Chat</label>
                                    <input type="date" name="tanggal_chat" class="form-control" value="{{ $lead->tanggal_chat ? $lead->tanggal_chat->format('Y-m-d') : '' }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-dark font-weight-bold small">Layanan</label>
                                    <input type="text" name="layanan" class="form-control" value="{{ $lead->layanan }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="text-dark font-weight-bold small">Chat Terakhir / Note</label>
                                    <textarea name="chat_ketiga" class="form-control" rows="4" readonly>{{ $lead->chat_ketiga }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-0 py-3">
                        <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning text-white px-5 font-weight-bold shadow-sm">
                            <i class="fas fa-sync-alt mr-2"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Hapus --}}
    <div class="modal fade" id="deleteModal_{{ $lead->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <form action="{{ route('crm.destroy', $lead->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body p-5 text-center">
                        <div class="mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: #fff5f5;">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x animate__animated animate__pulse animate__infinite"></i>
                            </div>
                        </div>
                        
                        <h4 class="font-weight-bold text-dark">Hapus Lead Ini?</h4>
                        <p class="text-muted">Apakah Anda yakin ingin menghapus data lead:</p>
                        <div class="bg-light p-3 rounded-lg mb-4 border">
                            <strong class="text-danger">{{ $lead->name ?? 'No Name' }}</strong><br>
                            <small class="text-muted">{{ $lead->id_proteksi }}</small>
                        </div>
                        <p class="small text-danger italic"><i class="fas fa-info-circle mr-1"></i> Data yang sudah dihapus dari CRM LMG tidak bisa dikembalikan.</p>
                        
                        <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
                            <button type="button" class="btn btn-light px-4 font-weight-bold border" data-dismiss="modal" style="border-radius: 10px;">Batal</button>
                            <button type="submit" class="btn btn-danger px-4 font-weight-bold shadow-sm" style="border-radius: 10px;">
                                Ya, Hapus Permanen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@include('partials.footer')