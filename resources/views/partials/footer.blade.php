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
        function openPaymentModal(invoiceId, noInvoice) {
            // 1. Set title modal
            $('#modalNoInvoice').text(noInvoice);
            
            // 2. Set action form ke route pembayaran
            // Sesuaikan route dengan route yang ada di web.php Anda
            $('#paymentForm').attr('action', '/invoice/payment/' + invoiceId);
            
            // 3. Tampilkan modal
            $('#paymentModal').modal('show');
        }
        function editInvoice(data) {
            // 1. Tampilkan modal
            $('#invoiceModal').modal('show');
            
            // 2. Ubah URL form agar mengarah ke route UPDATE
            $('#invoiceForm').attr('action', '/invoice/' + data.id);
            
            // 3. Tambahkan hidden method PUT untuk spoofing
            $('#methodField').html('@method("PUT")');
            
            // 4. Isi field modal dengan data dari baris tabel
            $('#customer_id').val(data.customer_id); // Pastikan ada ID ini di modal
            $('#no_invoice').val(data.no_invoice);
            $('#total_amount').val(data.total_amount.replace(/\./g, '')); // Bersihkan format angka
            $('#due_date').val(data.due_date_raw); // Pastikan data JSON Anda mengirim tanggal format Y-m-d
        }
        function resetForm() {
            $('#invoiceForm').attr('action', '{{ route("invoice.store") }}');
            $('#methodContainer').html('');
            $('#no_invoice').val('');
            $('#total_amount').val('');
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

            $('#invoiceTable').DataTable({
                processing: true,
                serverSide: false, // Opsional: gunakan jika data ribuan
                ajax: '{{ route('api.invoices.data') }}',
                columns: [
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'no_invoice', name: 'no_invoice' },
                    { data: 'due_date', name: 'due_date' },
                    { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                    { data: 'remaining', name: 'remaining', className: 'text-right font-weight-bold' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            let color = data === 'paid' ? 'success' : (data === 'partial' ? 'warning' : 'danger');
                            return `<span class="badge badge-${color}">${data.toUpperCase()}</span>`;
                        }
                    },
                    { 
                        data: 'id', 
                        name: 'action',
                        orderable: false, 
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            let actions = '';

                            // 1. Tombol Bayar (Hanya muncul jika belum lunas)
                            if (row.status !== 'paid') {
                                actions += `<button type="button" class="btn btn-sm btn-primary mr-1" 
                                    onclick="openPaymentModal(${row.id}, '${row.no_invoice}')">
                                    <i class="fas fa-money-bill-wave"></i>
                                </button>`;
                            }

                            // 2. Tombol PDF (Selalu muncul)
                            actions += `<a href="/invoice/pdf/${row.id}" target="_blank" class="btn btn-sm btn-warning mr-1">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>`;

                            // 3. Tombol Edit (Selalu muncul)
                            actions += `<button type="button" class="btn btn-sm btn-info mr-1" 
                                onclick="editInvoice(${JSON.stringify(row).replace(/"/g, '&quot;')})">
                                <i class="fas fa-edit"></i>
                            </button>`;

                            // 4. Tombol Hapus (Selalu muncul)
                            actions += `<form action="/invoice/${row.id}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>`;

                            return actions;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json'
                }
            });
        });
    </script>
</body>

</html>