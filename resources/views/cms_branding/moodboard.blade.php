    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahModalMoodboard" tabindex="-1" role="dialog" aria-labelledby="tambahModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                
                {{-- MODAL HEADER --}}
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="tambahModalLabel">
                        <i class="fas fa-image mr-2"></i> Tambah Moodboard
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                {{-- Tambahkan enctype untuk upload file --}}
                <form action="{{ route('moodboard.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- MODAL BODY --}}
                    <div class="modal-body p-4">
                        <div class="row">
                            <input type="hidden" name="produk" value="{{ $produk->id }}">
                            <input type="hidden" name="platform" value="{{ $platform }}">
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold text-dark">Pilih Gambar Moodboard</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-upload text-primary"></i></span>
                                    </div>
                                    {{-- Tambahkan accept="image/*" --}}
                                    <input type="file" name="image" id="inputImageMoodboard" class="form-control" accept="image/*" required>
                                </div>
                            </div>

                            {{-- AREA PREVIEW --}}
                            <div class="col-md-12 mb-2 text-center">
                                <div id="previewContainerMoodboard" style="display: none;">
                                    <label class="d-block font-weight-bold text-muted mb-2">Preview Gambar:</label>
                                    <img id="imagePreviewMoodboard" src="#" alt="Preview" class="img-fluid rounded shadow-sm border" style="max-height: 300px; width: auto;">
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info py-2 mb-0 mt-2">
                            <small><i class="fas fa-info-circle mr-1"></i> Format yang didukung: JPG, PNG, JPEG, WEBP. Dan Ukuran Maksimal 5MB!</small>
                        </div>
                    </div>

                    {{-- MODAL FOOTER --}}
                    <div class="modal-footer bg-light border-top-0">
                        <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fas fa-save mr-1"></i> Simpan Moodboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($moodboard)
        <div class="modal fade" id="editModalMoodboard_{{ $moodboard->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-2"></i> Edit Moodboard
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form action="{{ route('moodboard.update', $moodboard->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-body p-4">
                            <div class="row">
                                {{-- Hidden ID Produk --}}
                                <input type="hidden" name="produk" value="{{ $produk->id }}">
                                <input type="hidden" name="platform" value="{{ $platform }}">
                                <div class="col-md-12 mb-3">
                                    <label class="font-weight-bold text-dark">Ganti Gambar Moodboard</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light"><i class="fas fa-image text-warning"></i></span>
                                        </div>
                                        <input type="file" 
                                            name="image" 
                                            class="form-control" 
                                            accept="image/*" 
                                            onchange="previewEditImage(this, '{{ $moodboard->id }}')">
                                    </div>
                                    <small class="text-muted mt-1 d-block italic">
                                        *Kosongkan jika tidak ingin mengubah gambar. Maksimal 5MB.
                                    </small>
                                </div>

                                {{-- AREA PREVIEW --}}
                                <div class="col-md-12 mb-2 text-center">
                                    <label class="d-block font-weight-bold text-muted mb-2">Tampilan Gambar:</label>
                                    <div class="preview-wrapper border rounded p-2 bg-light">
                                        {{-- Menampilkan gambar lama sebagai default --}}
                                        <img id="preview-display-moodboard-{{ $moodboard->id }}" 
                                            src="{{ asset('storage/' . $moodboard->moodboard) }}" 
                                            data-original="{{ asset('storage/' . $moodboard->moodboard) }}"
                                            alt="Preview" 
                                            class="img-fluid rounded shadow-sm" 
                                            style="max-height: 300px; width: auto;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning px-4 shadow-sm text-white">
                                <i class="fas fa-sync-alt mr-1"></i> Update Moodboard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="hapusModalMoodboard_{{ $moodboard->id }}" tabindex="-1" role="dialog" aria-labelledby="hapusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-trash-alt mr-2"></i> Konfirmasi Hapus
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form action="{{ route('moodboard.destroy', $moodboard->id) }}" method="POST">
                        @csrf

                        <div class="modal-body text-center p-4">
                            <p class="mb-3">Apakah Anda yakin ingin menghapus gambar moodboard ini?</p>
                            
                            {{-- Tampilkan thumbnail agar user yakin mana yang dihapus --}}
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $moodboard->moodboard) }}" 
                                    class="img-fluid rounded border shadow-sm" 
                                    style="max-height: 150px;" alt="Hapus Moodboard">
                            </div>
                            
                            <p class="text-danger small mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Tindakan ini tidak dapat dibatalkan dan file akan dihapus permanen.
                            </p>
                        </div>

                        <div class="modal-footer bg-light border-top-0 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger px-4 shadow-sm">
                                <i class="fas fa-trash mr-1"></i> Ya, Hapus Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.getElementById('inputImageMoodboard').onchange = function (evt) {
            const [file] = this.files;
            const previewContainerMoodboard = document.getElementById('previewContainerMoodboard');
            const previewImage = document.getElementById('imagePreviewMoodboard');

            if (file) {
                const reader = new FileReader();
                
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewContainerMoodboard.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                previewContainerMoodboard.style.display = 'none';
            }
        };

        function previewEditImage(input, id) {
            const file = input.files[0];
            const preview = document.getElementById('preview-display-moodboard-' + id);
            const originalSrc = preview.getAttribute('data-original');

            if (file) {
                const fileType = file.type;
                const fileSize = file.size / 1024 / 1024; // Hitung MB

                // 1. Validasi: Harus Gambar
                if (!fileType.startsWith('image/')) {
                    alert("Gagal: File harus berupa gambar (JPG, PNG, JPEG, WEBP)!");
                    input.value = ""; // Reset input
                    preview.src = originalSrc; // Kembalikan ke gambar lama
                    return;
                }

                // 2. Validasi: Maksimal 5MB
                if (fileSize > 5) {
                    alert("Gagal: Ukuran file terlalu besar! Maksimal 5MB (File Anda: " + fileSize.toFixed(2) + " MB)");
                    input.value = ""; // Reset input
                    preview.src = originalSrc; // Kembalikan ke gambar lama
                    return;
                }

                // 3. Jika Lolos, Tampilkan Preview Baru
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                // Jika tidak ada file yang dipilih (batal pilih), kembalikan ke gambar lama
                preview.src = originalSrc;
            }
        }
    </script>