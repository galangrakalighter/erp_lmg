<div
    class="modal fade"
    id="leadFormModal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
>
    <div
        class="modal-dialog modal-lg modal-dialog-centered"
        role="document"
    >
        <div class="modal-content">
            <form id="leadForm">

                <div class="modal-header bg-primary text-white">
                    <h5 id="leadFormTitle" class="modal-title">
                        Tambah Lead
                    </h5>

                    <button
                        type="button"
                        class="close text-white"
                        data-dismiss="modal"
                    >
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div
                        id="leadFormError"
                        class="alert alert-danger d-none"
                    ></div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Nama</label>

                            <input
                                type="text"
                                id="leadNama"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Brand/Nama Usaha</label>

                            <input
                                type="text"
                                id="leadBrand"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Nomor WhatsApp</label>

                            <input
                                type="text"
                                id="leadPhone"
                                class="form-control"
                            >
                        </div>

                        <div
                            id="emailField"
                            class="col-md-6 mb-3"
                        >
                            <label>Email</label>

                            <input
                                type="email"
                                id="leadEmail"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Kategori</label>

                            <input
                                type="text"
                                id="leadKategori"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Layanan</label>

                            <input
                                type="text"
                                id="leadLayanan"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Prospek</label>

                            <select
                                id="leadProspek"
                                class="form-control"
                            >
                                <option value="cold">Cold</option>
                                <option value="warm">Warm</option>
                                <option value="hot">Hot</option>
                            </select>
                        </div>

                        {{-- Status khusus AI --}}
                        <div
                            id="aiStatusField"
                            class="col-md-6 mb-3"
                        >
                            <label for="leadAiStatus">
                                Status
                            </label>

                            <select
                                id="leadAiStatus"
                                class="form-control"
                            >
                                <option value="">
                                    Pilih Status
                                </option>

                                <option value="1">
                                    In Progress
                                </option>

                                <option value="2">
                                    Meeting Schedule
                                </option>
                            </select>
                        </div>

                        {{-- Status khusus Human --}}
                        <div
                            id="humanStatusField"
                            class="col-md-6 mb-3 d-none"
                        >
                            <label for="leadStatus">
                                Status
                            </label>

                            <select
                                id="leadStatus"
                                class="form-control"
                            >
                                <option value="">
                                    Pilih Status
                                </option>

                                <option value="meeting confirm">
                                    Meeting Confirm
                                </option>

                                <option value="meeting schedule">
                                    Meeting Schedule
                                </option>

                                <option value="on progress">
                                    On Progress
                                </option>

                                <option value="after meeting">
                                    After Meeting
                                </option>

                                <option value="ghosting">
                                    Ghosting
                                </option>

                                <option value="no respon">
                                    No Respon
                                </option>
                                <option value="deals">
                                    Deals
                                </option>
                                <option value="no deals">
                                    No Deals
                                </option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Instagram</label>

                            <input
                                type="url"
                                id="leadInstagram"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Facebook</label>

                            <input
                                type="url"
                                id="leadFacebook"
                                class="form-control"
                            >
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Shopee</label>

                            <input
                                type="url"
                                id="leadShopee"
                                class="form-control"
                            >
                        </div>

                        <div class="col-12 mb-3">
                            <label>Kebutuhan</label>

                            <textarea
                                id="leadKebutuhan"
                                class="form-control"
                                rows="3"
                            ></textarea>
                        </div>

                        {{-- Field khusus AI --}}
                        <div id="aiFields" class="col-12">
                            <div class="form-group">
                                <label>Kendala</label>

                                <textarea
                                    id="leadKendala"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label>Kesimpulan</label>

                                <textarea
                                    id="leadKesimpulan"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <label>Waktu Meeting</label>

                                <input
                                    type="datetime-local"
                                    id="leadMeeting"
                                    class="form-control"
                                >
                            </div>
                        </div>

                        {{-- Field khusus Human --}}
                        <div
                            id="humanFields"
                            class="col-12 d-none"
                        >
                            <div class="form-group">
                                <label>Note</label>

                                <textarea
                                    id="leadNote"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    id="leadCanPush"
                                    class="form-check-input"
                                >

                                <label
                                    for="leadCanPush"
                                    class="form-check-label"
                                >
                                    Can Push
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="crmTextDetailModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="crmTextDetailModalLabel"
    aria-hidden="true"
>
    <div
        class="modal-dialog modal-dialog-centered modal-lg"
        role="document"
    >
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5
                    class="modal-title"
                    id="crmTextDetailModalLabel"
                >
                    Detail Data
                </h5>

                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal"
                    aria-label="Tutup"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div
                    id="crmTextDetailContent"
                    class="border rounded bg-light p-3 text-dark"
                    style="
                        white-space: pre-wrap;
                        overflow-wrap: anywhere;
                        line-height: 1.7;
                        max-height: 500px;
                        overflow-y: auto;
                    "
                ></div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal history chat --}}
<div
    class="modal fade"
    id="crmChatHistoryModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="crmChatHistoryModalLabel"
    aria-hidden="true"
>
    <div
        class="modal-dialog modal-dialog-centered modal-lg"
        role="document"
    >
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <div>
                    <h5
                        class="modal-title"
                        id="crmChatHistoryModalLabel"
                    >
                        <i class="fab fa-whatsapp mr-1"></i>
                        History Chat
                    </h5>

                    <small id="crmChatHistoryPhone"></small>
                </div>

                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal"
                    aria-label="Tutup"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div
                id="crmChatHistoryBody"
                class="modal-body bg-light"
                style="
                    min-height: 250px;
                    max-height: 600px;
                    overflow-y: auto;
                "
            >
                <div class="text-center text-muted py-5">
                    Belum ada chat yang dipilih.
                </div>
            </div>

            <div class="modal-footer">
                <a
                    id="crmOpenWhatsApp"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-success"
                    style="display: none"
                >
                    <i class="fab fa-whatsapp mr-1"></i>
                    Buka WhatsApp
                </a>

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('leadForm');
    const aiFields = document.getElementById('aiFields');
    const aiStatusField = document.getElementById('aiStatusField');
    const humanStatusField = document.getElementById('humanStatusField');
    const aiStatus = document.getElementById('leadAiStatus');
    const humanStatus = document.getElementById('leadStatus');

    if (
        !form ||
        !aiStatusField ||
        !humanStatusField ||
        !aiStatus ||
        !humanStatus
    ) {
        return;
    }

    let currentFormType = 'ai';

    function normalizeAiStatus(value) {
        const status = String(value ?? '')
            .trim()
            .toLowerCase();

        const statusMap = {
            '1': '1',
            '2': '2',
            'in progress': '1',
            'meeting schedule': '2',
            'meeting scheduled': '2',
        };

        return statusMap[status] || '';
    }

    function useStatusType(type) {
        const isAi = type === 'ai';

        currentFormType = isAi ? 'ai' : 'human';

        aiStatusField.classList.toggle('d-none', !isAi);
        humanStatusField.classList.toggle('d-none', isAi);

        aiStatus.disabled = !isAi;

        /*
         * #leadStatus tidak dinonaktifkan karena kode
         * submit lama Anda masih membacanya.
         */
        humanStatus.disabled = false;

        if (isAi) {
            const normalizedStatus = normalizeAiStatus(
                humanStatus.value
            );

            aiStatus.value = normalizedStatus;
            humanStatus.value = normalizedStatus;
        }
    }

    aiStatus.addEventListener('change', function () {
        /*
         * Sinkronkan select AI ke field lama agar payload
         * lama tetap mengirim property status.
         */
        humanStatus.value = aiStatus.value;

        humanStatus.dispatchEvent(
            new Event('change', {
                bubbles: true,
            })
        );
    });

    document.addEventListener(
        'click',
        function (event) {
            const editButton = event.target.closest('.edit-lead');

            if (!editButton) {
                return;
            }

            if (editButton.closest('#aiTableBody')) {
                useStatusType('ai');
                return;
            }

            if (editButton.closest('#humanTableBody')) {
                useStatusType('human');
            }
        },
        true
    );

    /*
     * Handler utama Anda sudah mengatur aiFields/humanFields.
     * Setelah modal muncul, status mengikuti field tersebut.
     */
    $('#leadFormModal').on('shown.bs.modal', function () {
        const isAi =
            aiFields &&
            !aiFields.classList.contains('d-none');

        useStatusType(isAi ? 'ai' : 'human');
    });

    /*
     * Capture=true membuat sinkronisasi berjalan sebelum
     * listener submit lama menyusun payload.
     */
    form.addEventListener(
        'submit',
        function () {
            if (currentFormType === 'ai') {
                humanStatus.value = aiStatus.value;
            }
        },
        true
    );
});
</script>
