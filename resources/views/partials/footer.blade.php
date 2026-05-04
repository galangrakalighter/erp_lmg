            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Your Website 2021</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-footer">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left py-3 px-4 rounded-lg bg-gold text-black font-bold shadow-lg shadow-gold/20 transition duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('vendor/jquery/jquery.min.js')}}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js')}}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js')}}"></script>
    <script src="{{ asset('vendor/chart.js/Chart.min.js')}}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap4.min.js')}}"></script>
    <script src="https://cdn.rawgit.com/ashl1/datatables-rowsgroup/v1.0.0/dataTables.rowsGroup.js"></script>
    <script src="{{ asset('js/demo/datatables-demo.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/split.js/dist/split.min.js"></script>
    <script>
        function editProduk(id) {
            let $modal = $('#modalEditGlobal');
            let $container = $('#modalEditContent');
            
            // Tampilkan Loading
            $modal.modal('show');
            $container.html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Memuat data produk...</p>
                </div>
            `);

            $userId = "{{ Auth::user()->id }}"

            const path = window.location.pathname; // Hasil: "/cms_BD/produk"
            const segments = path.split('/').filter(Boolean); // ["cms_BD", "produk"]

            // Ambil indeks ke-0 (segmen pertama)
            const folderName = segments[0];
            // Ganti URL sesuai dengan route web.php Anda
            let urlDetail = `{{ url('api/produk/detail') }}/${id}/${$userId}/${folderName}`;
            let urlUpdate = `{{ url('cms/produk/edit') }}/${id}`;

            $('#formEditProduk').attr('action', urlUpdate);

            $.ajax({
                url: urlDetail,
                method: 'GET',
                success: function(res) {
                    console.log(res);
                    if(res.status === 'success') {
                        const p = res.produk;
                        const currentTeams = res.current_teams;
                        const opt = res.options;
                        const isAdmin = res.is_admin;

                        // Parsing Platform & Token (Asumsi data disimpan dalam JSON di DB)
                        let platformData = [];
                        try {
                            platformData = JSON.parse(p.access_token) || [];
                        } catch(e) { platformData = []; }

                        let html = `
                            <div class="modal-header bg-warning text-white py-3">
                                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Edit Produk Jasa</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body p-4" style="background-color: #f8f9fc;">
                                <div class="section-title mb-3 d-flex align-items-center">
                                    <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                                    <h6 class="text-warning font-weight-bold mb-0">Informasi Dasar</h6>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Kategori Jasa <span class="text-danger">*</span></label>
                                            <select name="kategori_jasa_id" class="form-control custom-select" required>
                                                <option value="">-- Pilih Kategori --</option>
                                                ${(res.kategori || []).map(k => `<option value="${k.id}" ${p.kategori_jasa_id == k.id ? 'selected' : ''}>${k.category}</option>`).join('')}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Nama Produk <span class="text-danger">*</span></label>
                                            <input type="text" name="client" class="form-control" value="${p.nama}" placeholder="Contoh: Social Media Management" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="text-dark font-weight-bold small">Status <span class="text-danger">*</span></label>
                                            <select name="status" class="form-control custom-select" required>
                                                <option value="1" ${p.status == 1 ? 'selected' : ''}>Ditampilkan</option>
                                                <option value="2" ${p.status == 2 ? 'selected' : ''}>Disembunyikan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="section-title mb-3 d-flex align-items-center mt-4">
                                    <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                                    <h6 class="text-warning font-weight-bold mb-0">Platform & Akses Token</h6>
                                </div>

                                <div class="card border-0 shadow-sm rounded-lg mb-4">
                                    <div class="card-body p-3" id="platform-container-edit">
                                        ${platformData.length > 0 ? platformData.map((item, index) => `
                                            <div class="platform-row mb-3">
                                                <div class="input-group">
                                                    <select name="platform[]" class="form-control col-md-4">
                                                        <option value="Instagram" ${item.platform == 'Instagram' ? 'selected' : ''}>Instagram</option>
                                                        <option value="Facebook" ${item.platform == 'Facebook' ? 'selected' : ''}>Facebook</option>
                                                        <option value="Tiktok" ${item.platform == 'Tiktok' ? 'selected' : ''}>Tiktok</option>
                                                    </select>
                                                    <input type="text" name="access[]" class="form-control" value="${item.access}" placeholder="Input Access Token">
                                                    <div class="input-group-append">
                                                        ${index === 0 ? 
                                                            `<button type="button" class="btn btn-success add-platform-edit">+</button>` : 
                                                            `<button type="button" class="btn btn-danger remove-platform-edit">-</button>`
                                                        }
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('') : `
                                            <div class="platform-row mb-3">
                                                <div class="input-group">
                                                    <select name="platform[]" class="form-control col-md-4">
                                                        <option value="Instagram">Instagram</option>
                                                        <option value="Facebook">Facebook</option>
                                                        <option value="Tiktok">Tiktok</option>
                                                    </select>
                                                    <input type="text" name="access[]" class="form-control" placeholder="Input Access Token">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-success add-platform-edit">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        `}
                                    </div>
                                </div>

                                <div class="section-title mb-3 d-flex align-items-center mt-4">
                                    <div class="bg-warning mr-2" style="width: 4px; height: 18px; border-radius: 2px;"></div>
                                    <h6 class="text-warning font-weight-bold mb-0">Assign Team Management</h6>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-danger">Manager (DMM)</label>
                                        ${isAdmin ? `
                                            <select name="dmm_ids[]" id="edit-select-dmm" class="form-control select2-edit" multiple data-placeholder="Pilih DMM">
                                                ${opt.dmm.map(u => `<option value="${u.id}" ${currentTeams.includes(u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                                            </select>
                                        ` : `
                                            <input type="hidden" name="dmm_ids[]" value="${p.user_id}">
                                            <div class="p-2 border rounded bg-white shadow-sm">
                                                ${opt.dmm.filter(u => currentTeams.includes(u.id)).map(u => `<span class="badge badge-danger m-1">${u.name}</span>`).join('') || '<small class="text-muted">No DMM</small>'}
                                            </div>
                                        `}
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-warning">Digital Marketing (DM)</label>
                                        <select name="dm_ids[]" id="edit-select-dm" class="form-control select2-edit" multiple data-placeholder="Pilih DM">
                                            ${opt.dm.map(u => `<option value="${u.id}" data-parent="${u.parent}" ${currentTeams.includes(u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-dark">Senior Content Creator (SCC)</label>
                                        <select name="scc_ids[]" id="edit-select-scc" class="form-control select2-edit" multiple data-placeholder="Pilih SCC">
                                            ${opt.scc.map(u => `<option value="${u.id}" data-parent="${u.parent}" ${currentTeams.includes(u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label class="font-weight-bold small text-warning">Content Creator (CC)</label>
                                        <select name="cc_ids[]" id="edit-select-cc" class="form-control select2-edit" multiple data-placeholder="Pilih CC">
                                            ${opt.cc.map(u => `<option value="${u.id}" data-parent="${u.parent}" ${currentTeams.includes(u.id) ? 'selected' : ''}>${u.name}</option>`).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer bg-white border-0 py-3">
                                <button type="button" class="btn btn-light text-dark px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-warning px-5 font-weight-bold shadow-sm">
                                    <i class="fas fa-save mr-2"></i>Update Produk
                                </button>
                            </div>
                        `;

                        $container.html(html);
                        
                        // Inisialisasi Select2
                        $('.select2-edit').select2({
                            theme: 'bootstrap4',
                            width: '100%',
                            placeholder: '-- Pilih --'
                        });

                        // Jalankan filter hierarki pertama kali
                        applyHierarchyFilter();

                        setupEditChaining();

                        let initialDmm = $('#edit-select-dmm').val() || [];
                        let initialDm = $('#edit-select-dm').val() || [];
                        let initialScc = $('#edit-select-scc').val() || [];

                        if(initialDmm.length > 0) filterOptions('#edit-select-dm', initialDmm);
                        if(initialDm.length > 0) filterOptions('#edit-select-scc', initialDm);
                        if(initialScc.length > 0) filterOptions('#edit-select-cc', initialScc);
                        
                        // Kembalikan nilai lama yang mungkin ter-reset oleh filter manual tadi
                        $('#edit-select-dm').val(res.current_dm).trigger('change.select2');
                        $('#edit-select-scc').val(res.current_scc).trigger('change.select2');
                        $('#edit-select-cc').val(res.current_cc).trigger('change.select2');

                        $('#modalEditGlobal').modal('show');
                    }
                },
                error: function(xhr) {
                    $container.html(`<div class="alert alert-danger">Error: ${xhr.statusText}</div>`);
                }
            });
        }
        function applyHierarchyFilter() {
            // 1. Ambil ID Manager (DMM) yang terpilih
            let selectedDmm = $('#edit-select-dmm').val() || [];
            if (!Array.isArray(selectedDmm)) selectedDmm = [selectedDmm];

            // 2. Filter DM berdasarkan Parent (DMM)
            $('#edit-select-dm option').each(function() {
                let parentId = $(this).data('parent').toString();
                // Jika admin tidak pilih DMM, tampilkan semua. Jika pilih, filter.
                if (selectedDmm.length === 0 || selectedDmm.includes(parentId)) {
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', true);
                    $(this).prop('selected', false); // Unselect jika tidak sesuai parent
                }
            });

            // 3. Ambil ID DM yang terpilih untuk filter SCC & CC
            let selectedDm = $('#edit-select-dm').val() || [];
            if (!Array.isArray(selectedDm)) selectedDm = [selectedDm];

            $('#edit-select-scc option, #edit-select-cc option').each(function() {
                let parentId = $(this).data('parent').toString();
                if (selectedDm.length === 0 || selectedDm.includes(parentId)) {
                    $(this).prop('disabled', false);
                } else {
                    $(this).prop('disabled', true);
                    $(this).prop('selected', false);
                }
            });

            // Refresh Select2 agar perubahan 'disabled' terlihat
            $('.select2-edit').select2({ theme: 'bootstrap4', width: '100%' });
        }
        function setupEditChaining() {
            // Filter DM berdasarkan DMM
            $('#edit-select-dmm').on('change', function() {
                let selectedParents = $(this).val() || [];
                filterOptions('#edit-select-dm', selectedParents);
                // Reset bawahannya jika parent berubah
                $('#edit-select-dm').val(null).trigger('change'); 
            });

            // Filter SCC berdasarkan DM
            $('#edit-select-dm').on('change', function() {
                let selectedParents = $(this).val() || [];
                filterOptions('#edit-select-scc', selectedParents);
                $('#edit-select-scc').val(null).trigger('change');
            });

            // Filter CC berdasarkan SCC
            $('#edit-select-scc').on('change', function() {
                let selectedParents = $(this).val() || [];
                filterOptions('#edit-select-cc', selectedParents);
                $('#edit-select-cc').val(null).trigger('change');
            });
        }

        function filterOptions(targetSelector, allowedParents) {
                let target = $(targetSelector);
                
                // Aktifkan jika ada parent yang dipilih, nonaktifkan jika tidak
                if (allowedParents.length > 0) {
                    target.prop('disabled', false);
                } else {
                    target.prop('disabled', true);
                }

                target.find('option').each(function() {
                    let parentId = $(this).data('parent');
                    // Selalu tampilkan placeholder (option tanpa value)
                    if (!$(this).val()) return; 

                // Tampilkan hanya jika parent_id user tersebut ada di dalam array selectedParents
                if (allowedParents.includes(parentId.toString())) {
                    $(this).show().prop('disabled', false);
                } else {
                    $(this).hide().prop('disabled', true);
                }
            });

            // Refresh Select2 agar perubahan (show/hide/disabled) terlihat
            target.select2({ width: '100%', placeholder: target.data('placeholder') });
        }

        function deleteProduk(id, nama) {
            // 1. Set nama produk di dalam teks modal
            $('#deleteTargetName').text(nama);

            // 2. Set action form secara dinamis ke route delete Anda
            // Sesuaikan URL-nya dengan nama route/path backend Anda
            let url = `{{ url('cms/produk/delete') }}/${id}`; 
            $('#formDeleteProduk').attr('action', url);

            // 3. Tampilkan modal
            $('#modalDeleteGlobal').modal('show');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('loadingOverlay');
            const barFill = document.getElementById('progressBar');
            const barPercent = document.getElementById('progressPercentage');
            const statusText = document.getElementById('loadingStatus');
            function startEnhancedLoading() {
                loader.style.display = 'flex';
                document.body.classList.add('loading-active');
                
                let progress = 0;
                const phases = [
                    { limit: 30, text: "Menghubungkan ke API Meta..." },
                    { limit: 60, text: "Mengunduh Data Konten..." },
                    { limit: 98, text: "Finalisasi Tampilan..." }
                ];

                let currentPhase = 0;
                const interval = setInterval(() => {
                    if (progress < phases[currentPhase].limit) {
                        progress += Math.random() * 1.5;
                    } else if (currentPhase < phases.length - 1) {
                        currentPhase++;
                        statusText.innerText = phases[currentPhase].text;
                    }
                    
                    const rounded = Math.floor(progress);
                    barFill.style.width = rounded + "%";
                    barPercent.innerText = rounded + "%";
                    
                    if (progress >= 100) clearInterval(interval);
                }, 150);
            }
            const sidebarLinks = document.querySelectorAll('#accordionSidebar a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    // Pemicu loading untuk link yang menuju halaman/client analisis
                    if (href && href !== '#' && !this.hasAttribute('data-toggle')) {
                        startEnhancedLoading()
                    }
                });
            });
        });

        function hitungJamSelesaiManual(jamMulai, durasiMenit) {
            if (!jamMulai) return "00:00";
            const [jam, menit] = jamMulai.split(':').map(Number);
            const totalMenit = (jam * 60) + menit + parseInt(durasiMenit || 0);
            console.log(totalMenit);
            
            const h = Math.floor(totalMenit / 60);
            const m = totalMenit % 60;
            return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        }

        function formatDurasi(menit) {
            if (!menit || menit <= 0) return "0 Menit";
            let jam = Math.floor(menit / 60);
            let sisaMenit = menit % 60;
            if (jam > 0) {
                return sisaMenit > 0 ? `${jam} Jam ${sisaMenit} Menit` : `${jam} Jam`;
            } else {
                return `${menit} Menit`;
            }
        }
        $(document).ready(function() {
            var tableId = '#target_audience';

            // 1. Hancurkan instance lama jika ada (mencegah error reinitialise)
            if ($.fn.DataTable.isDataTable(tableId)) {
                $(tableId).DataTable().destroy();
            }

            // 2. Inisialisasi dengan RowsGroup
            $(tableId).DataTable({
                responsive: true,
                pageLength: 25, // Satu produk = 5 baris, jadi 25 baris = 5 produk
                ordering: false, // Wajib FALSE agar baris profile tidak terpisah
                autoWidth: false,
                rowsGroup: [
                    0, // Kolom No
                    1, // Kolom Produk
                    2, // Kolom Indikator
                    5, // Kolom Gaya Hidup
                    6, // Kolom Status Sosial
                    7, // Kolom Penggunaan Produk
                    8,  // Kolom Manfaat
                    9
                ],
                columnDefs: [
                    {
                        targets: [0, 1, 2, 5, 6, 7, 8],
                        className: 'text-center align-middle' // Teks di tengah-tengah kotak besar
                    }
                ],
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
            });
        });
    </script>
</body>

</html>