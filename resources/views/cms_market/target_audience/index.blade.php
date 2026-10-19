
<div class="modal fade" id="tambahModalTarget" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <form action="{{ route('target_audience_create') }}" method="POST">
                @csrf

                <!-- MODAL HEADER -->
                <div class="modal-header border-0 bg-primary text-white p-4" style="border-radius: 15px 15px 0 0;">
                    <div class="d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fas fa-users-cog fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="tambahModalLabel">Tambah Target Audience Baru</h5>
                            <small class="text-white-50">Tentukan target pasar Anda secara mendetail</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- MODAL BODY -->
                <div class="modal-body p-4 bg-light">
                    <!-- SECTION: PILIH PRODUK -->
                    <input type="hidden" name="produk" value="{{ $produk->id }}">
                    <input type="hidden" name="platform" value="{{ $platform }}">
                    <div class="row g-4">
                        <!-- KOLOM KIRI: DEMOGRAFI & GEOGRAFI -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-user-friends me-2 text-info"></i>Profil Demografi & Lokasi</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Rentang Usia</label>
                                            <textarea 
                                                id="myInput" 
                                                placeholder="Ketik sesuatu... (Shift+Enter untuk baris baru)" 
                                                style="width: 100%; min-height: 50px; padding: 10px; resize: vertical;">
                                            </textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Gender</label>
                                            <select name="gender" class="form-select bg-light border-0">
                                                <option value="Laki-laki">Laki-laki</option>
                                                <option value="Perempuan">Perempuan</option>
                                                <option value="Semua">Semua Gender</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Negara</label>
                                            <input type="text" name="negara" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Wilayah</label>
                                            <input type="text" name="wilayah" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Profile</label>
                                            <input type="text" name="pekerjaan" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Indikator</label>
                                            <input type="text" name="indikator" class="form-control bg-light border-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- KOLOM KANAN: PSIKOGRAFI & PERILAKU -->
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-brain me-2 text-warning"></i>Psikografi & Karakter</h6>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Gaya Hidup</label>
                                            <input type="text" name="gaya_hidup" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Status Sosial</label>
                                            <input type="text" name="status_sosial" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label small fw-bold">Penggunaan Produk</label>
                                            <input type="text" name="penggunaan" class="form-control bg-light border-0">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label small fw-bold">Manfaat</label>
                                            <input type="text" name="manfaat" class="form-control bg-light border-0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL FOOTER -->
                <div class="modal-footer border-0 p-4 bg-light shadow-sm" style="border-radius: 0 0 15px 15px;">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none me-auto" data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm fw-bold" style="border-radius: 8px;">
                        <i class="fas fa-save me-2"></i> Simpan Segmentasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@foreach ($data['data'][4] as $item)
    <div class="modal fade" id="editModalTarget_{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <form action="{{ route('target_audience_edit', $item->id) }}" method="POST">
                    @csrf

                    <!-- MODAL HEADER: Menggunakan tema warna Primary untuk Edit -->
                    <div class="modal-header border-0 bg-warning text-white p-4" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3 text-warning d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-user-edit fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="editModalLabel">Edit Segmentasi Pasar</h5>
                                <small class="text-white-50">Perbarui detail target pasar untuk produk Anda</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- MODAL BODY -->
                    <div class="modal-body p-4 bg-light">
                        
                        <!-- SECTION: PILIH PRODUK -->
                        <input type="hidden" name="produk" value="{{ $produk->id }}">
                        <input type="hidden" name="platform" value="{{ $platform }}">
                        <div class="row g-4">
                            <!-- KOLOM KIRI: DEMOGRAFI & GEOGRAFI -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                    <div class="card-header bg-white py-3 border-0">
                                        <h6 class="mb-0 fw-bold text-primary">
                                            <i class="fas fa-map-marker-alt me-2"></i>Profil Demografi & Lokasi
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Rentang Usia</label>
                                                <input type="text" name="usia" class="form-control bg-light border-0" 
                                                    value="{{ $item->usia }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Gender</label>
                                                <select name="gender" class="form-select bg-light border-0">
                                                    <option value="Laki-laki" {{ $item->gender == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                                    <option value="Perempuan" {{ $item->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                                    <option value="Semua" {{ $item->gender == 'Semua' ? 'selected' : '' }}>Semua Gender</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Negara</label>
                                                <input type="text" name="negara" class="form-control bg-light border-0" 
                                                    value="{{ $item->negara }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Wilayah</label>
                                                <input type="text" name="wilayah" class="form-control bg-light border-0" 
                                                    value="{{ $item->wilayah }}">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label small fw-bold text-muted">Profil / Pekerjaan</label>
                                                <input type="text" name="pekerjaan" class="form-control bg-light border-0" 
                                                    value="{{ $item->pekerjaan }}">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label small fw-bold text-muted">Indikator</label>
                                                <input type="text" name="indikator" class="form-control bg-light border-0" 
                                                    value="{{ $item->indikator }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KOLOM KANAN: PSIKOGRAFI & PERILAKU -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                                    <div class="card-header bg-white py-3 border-0">
                                        <h6 class="mb-0 fw-bold text-warning">
                                            <i class="fas fa-brain me-2"></i>Psikografi & Karakter
                                        </h6>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Gaya Hidup</label>
                                                <input type="text" name="gaya_hidup" class="form-control bg-light border-0" 
                                                    value="{{ $item->gaya_hidup }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label small fw-bold text-muted">Status Sosial</label>
                                                <input type="text" name="status_sosial" class="form-control bg-light border-0" 
                                                    value="{{ $item->status_sosial }}">
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label small fw-bold text-muted">Penggunaan Produk</label>
                                                <input type="text" name="penggunaan" class="form-control bg-light border-0" 
                                                    value="{{ $item->penggunaan }}">
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label class="form-label small fw-bold text-muted">Manfaat Utama</label>
                                                <input type="text" name="manfaat" class="form-control bg-light border-0" 
                                                    value="{{ $item->manfaat }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL FOOTER -->
                    <div class="modal-footer border-0 p-4 bg-light shadow-sm" style="border-radius: 0 0 15px 15px;">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none me-auto" data-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm fw-bold" style="border-radius: 8px;">
                            <i class="fas fa-save me-2"></i> Perbarui Target Audience
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteModalTarget_{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog modal-md">
            <div class="modal-content">

                <form action="{{ route('target_audience_delete', $item->id) }}" method="POST">
                    @csrf

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Hapus Target Audience</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            &times;
                        </button>
                    </div>

                    <div class="modal-body text-center">
                        <p class="mb-2">
                            Apakah kamu yakin ingin menghapus Target Audience Produk
                        </p>
                        <strong>{{ $item->nama }}</strong>?
                        <p class="text-muted mt-2">
                            Data yang dihapus tidak dapat dikembalikan.
                        </p>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button class="btn btn-secondary" data-dismiss="modal">
                            Batal
                        </button>
                        <button class="btn btn-danger">
                            Ya, Hapus
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endforeach