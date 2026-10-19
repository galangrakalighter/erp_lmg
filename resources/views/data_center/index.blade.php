@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px] space-y-6">
        <header class="overflow-hidden rounded-[28px] bg-zinc-950 px-6 py-7 text-white shadow-xl shadow-zinc-950/10 sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[.22em] text-zinc-400">Administration · Khusus Admin</p>
                    <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Data Center</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Simpan informasi akun ERP, media sosial, email, marketplace, dan layanan digital secara terpusat.</p>
                </div>
                <button type="button" data-open-account-modal class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-white px-5 text-xs font-bold text-zinc-950 transition hover:bg-zinc-100">
                    <span class="text-lg leading-none">+</span> Tambah Akun
                </button>
            </div>
        </header>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <div class="font-semibold">Data belum dapat disimpan.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total Akun</p>
                <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">{{ number_format($totalAccounts) }}</p>
            </div>
            @foreach(['ERP', 'Instagram', 'Facebook'] as $summaryCategory)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">{{ $summaryCategory }}</p>
                    <p class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">{{ number_format($categoryCounts[$summaryCategory] ?? 0) }}</p>
                </div>
            @endforeach
        </section>

        <section class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" class="grid gap-3 lg:grid-cols-[1fr_240px_auto]">
                <label>
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Cari akun</span>
                    <input name="search" value="{{ $search }}" placeholder="Nama akun, provider, username, atau email" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-4 text-sm outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10">
                </label>
                <label>
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-zinc-500">Kategori</span>
                    <select name="category" class="h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-sm outline-none focus:border-zinc-950">
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)<option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>@endforeach
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button class="h-11 rounded-xl bg-zinc-950 px-5 text-xs font-bold text-white">Terapkan</button>
                    <a href="{{ route('data-center.index') }}" class="inline-flex h-11 items-center rounded-xl border border-zinc-300 px-4 text-xs font-semibold text-zinc-700">Reset</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-zinc-950">Informasi Akun</h2>
                <p class="mt-1 text-xs text-zinc-500">Password dienkripsi dan hanya dapat dibuka setelah verifikasi password ERP admin.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1100px] w-full text-left">
                    <thead class="bg-zinc-950 text-[10px] uppercase tracking-wider text-zinc-300">
                        <tr><th class="px-5 py-3">Akun</th><th class="px-4 py-3">Kategori</th><th class="px-4 py-3">Login</th><th class="px-4 py-3">Tautan</th><th class="px-4 py-3">Diperbarui</th><th class="px-5 py-3 text-right">Tindakan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                    @forelse($accounts as $account)
                        @php
                            $accountPayload = [
                                'id' => $account->id,
                                'category' => $account->category,
                                'provider' => $account->provider,
                                'account_name' => $account->account_name,
                                'username' => $account->username,
                                'email' => $account->email,
                                'recovery_email' => $account->recovery_email,
                                'phone' => $account->phone,
                                'url' => $account->url,
                                'notes' => $account->notes,
                                'update_url' => route('data-center.update', $account),
                                'delete_url' => route('data-center.destroy', $account),
                                'reveal_url' => route('data-center.reveal', $account),
                            ];
                        @endphp
                        <tr class="align-top hover:bg-zinc-50/80" data-account-row data-account="{{ json_encode($accountPayload) }}">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-zinc-950">{{ $account->account_name }}</div>
                                <div class="mt-1 text-xs text-zinc-500">{{ $account->provider ?: 'Tanpa provider' }}</div>
                                @if($account->notes)<div class="mt-2 max-w-xs truncate text-xs text-zinc-400" title="{{ $account->notes }}">{{ $account->notes }}</div>@endif
                            </td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-[10px] font-bold text-zinc-700">{{ $account->category }}</span></td>
                            <td class="px-4 py-4 text-xs text-zinc-700">
                                <div>{{ $account->username ?: '-' }}</div>
                                @if($account->email)<div class="mt-1 text-zinc-500">{{ $account->email }}</div>@endif
                                @if($account->phone)<div class="mt-1 text-zinc-500">{{ $account->phone }}</div>@endif
                            </td>
                            <td class="px-4 py-4 text-xs">
                                @if($account->url)<a href="{{ $account->url }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-blue-600 hover:underline">Buka layanan</a>@else<span class="text-zinc-400">-</span>@endif
                            </td>
                            <td class="px-4 py-4 text-xs text-zinc-500">
                                <div>{{ $account->updated_at->format('d M Y H:i') }}</div>
                                <div class="mt-1">{{ $account->updater?->name ?: $account->creator?->name ?: '-' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button type="button" data-reveal-account class="rounded-lg border border-zinc-300 px-3 py-2 text-[10px] font-bold text-zinc-700 hover:bg-zinc-100">Lihat Password</button>
                                    <button type="button" data-edit-account class="rounded-lg bg-zinc-950 px-3 py-2 text-[10px] font-bold text-white hover:bg-zinc-800">Edit</button>
                                    <button type="button" data-delete-account class="rounded-lg border border-red-200 px-3 py-2 text-[10px] font-bold text-red-600 hover:bg-red-50">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><div class="text-sm font-semibold text-zinc-700">Belum ada informasi akun</div><div class="mt-1 text-xs text-zinc-500">Tambahkan akun pertama untuk memulai Data Center.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<div id="accountModal" class="fixed inset-0 z-[1200] hidden items-center justify-center bg-black/60 p-4" aria-hidden="true">
    <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-zinc-200 px-6 py-5">
            <div><h2 id="accountModalTitle" class="text-lg font-semibold text-zinc-950">Tambah Informasi Akun</h2><p class="mt-1 text-xs text-zinc-500">Field sensitif akan dienkripsi sebelum masuk database.</p></div>
            <button type="button" data-close-account-modal class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100">&times;</button>
        </div>
        <form id="accountForm" method="POST" action="{{ route('data-center.store') }}" class="space-y-5 px-6 py-5">
            @csrf
            <div id="accountMethod"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-xs font-semibold text-zinc-700">Kategori *<select name="category" required class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 bg-white px-3 text-sm font-normal">@foreach($categories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></label>
                <label class="text-xs font-semibold text-zinc-700">Provider / Platform<input name="provider" maxlength="100" placeholder="Contoh: Meta, Google, ERP LMG" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700 sm:col-span-2">Nama Akun *<input name="account_name" required maxlength="255" placeholder="Contoh: Instagram Brand Utama" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700">Username<input name="username" maxlength="255" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700">Email Login<input name="email" type="email" maxlength="255" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700">Password <span id="passwordRequired">*</span><input name="password" type="password" maxlength="2000" autocomplete="new-password" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"><span id="passwordHint" class="mt-1 hidden text-[10px] font-normal text-zinc-500">Kosongkan jika password tidak berubah.</span></label>
                <label class="text-xs font-semibold text-zinc-700">Email Pemulihan<input name="recovery_email" type="email" maxlength="255" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700">Nomor Telepon<input name="phone" maxlength="50" class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700">URL Login<input name="url" type="url" maxlength="2000" placeholder="https://..." class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm font-normal"></label>
                <label class="text-xs font-semibold text-zinc-700 sm:col-span-2">Catatan<textarea name="notes" rows="4" maxlength="5000" placeholder="Informasi tambahan, pemilik akun, atau petunjuk akses" class="mt-1.5 w-full rounded-xl border border-zinc-300 px-3 py-2.5 text-sm font-normal"></textarea></label>
            </div>
            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4"><button type="button" data-close-account-modal class="rounded-xl border border-zinc-300 px-4 py-2.5 text-xs font-semibold text-zinc-700">Batal</button><button class="rounded-xl bg-zinc-950 px-5 py-2.5 text-xs font-bold text-white">Simpan</button></div>
        </form>
    </div>
</div>

<div id="revealModal" class="fixed inset-0 z-[1250] hidden items-center justify-center bg-black/65 p-4" aria-hidden="true">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
        <h2 class="text-lg font-semibold text-zinc-950">Verifikasi Admin</h2>
        <p class="mt-1 text-sm leading-6 text-zinc-500">Masukkan password akun ERP Anda untuk membuka password <strong id="revealAccountName"></strong>.</p>
        <form id="revealForm" class="mt-5">
            <label class="text-xs font-semibold text-zinc-700">Password ERP<input id="currentPassword" type="password" autocomplete="current-password" required class="mt-1.5 h-11 w-full rounded-xl border border-zinc-300 px-3 text-sm"></label>
            <p id="revealError" class="mt-2 hidden text-xs text-red-600"></p>
            <div id="revealedPasswordBox" class="mt-4 hidden rounded-xl bg-zinc-950 p-4 text-white"><div class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Password tersimpan</div><div class="mt-2 flex items-center gap-2"><code id="revealedPassword" class="min-w-0 flex-1 break-all text-sm"></code><button id="copyRevealedPassword" type="button" class="rounded-lg bg-white px-3 py-2 text-[10px] font-bold text-zinc-950">Salin</button></div></div>
            <div class="mt-5 flex justify-end gap-2"><button type="button" data-close-reveal-modal class="rounded-xl border border-zinc-300 px-4 py-2.5 text-xs font-semibold">Tutup</button><button id="revealSubmit" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-xs font-bold text-white">Buka Password</button></div>
        </form>
    </div>
</div>

<form id="deleteAccountForm" method="POST" class="hidden">@csrf @method('DELETE')</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accountModal = document.getElementById('accountModal');
    const accountForm = document.getElementById('accountForm');
    const fields = ['category','provider','account_name','username','email','recovery_email','phone','url','notes'];
    const setModal = open => { accountModal.classList.toggle('hidden', !open); accountModal.classList.toggle('flex', open); accountModal.setAttribute('aria-hidden', open ? 'false' : 'true'); };
    document.querySelector('[data-open-account-modal]')?.addEventListener('click', () => {
        accountForm.reset(); accountForm.action = @json(route('data-center.store')); document.getElementById('accountMethod').innerHTML = '';
        document.getElementById('accountModalTitle').textContent = 'Tambah Informasi Akun';
        accountForm.elements.password.required = true; document.getElementById('passwordRequired').classList.remove('hidden'); document.getElementById('passwordHint').classList.add('hidden'); setModal(true);
    });
    document.querySelectorAll('[data-close-account-modal]').forEach(button => button.addEventListener('click', () => setModal(false)));
    accountModal.addEventListener('click', event => { if (event.target === accountModal) setModal(false); });

    document.querySelectorAll('[data-edit-account]').forEach(button => button.addEventListener('click', () => {
        const data = JSON.parse(button.closest('[data-account-row]').dataset.account);
        accountForm.reset(); accountForm.action = data.update_url; document.getElementById('accountMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        fields.forEach(name => { accountForm.elements[name].value = data[name] || ''; });
        accountForm.elements.password.required = false; accountForm.elements.password.value = '';
        document.getElementById('passwordRequired').classList.add('hidden'); document.getElementById('passwordHint').classList.remove('hidden');
        document.getElementById('accountModalTitle').textContent = 'Edit Informasi Akun'; setModal(true);
    }));

    document.querySelectorAll('[data-delete-account]').forEach(button => button.addEventListener('click', () => {
        const data = JSON.parse(button.closest('[data-account-row]').dataset.account);
        if (!window.confirm(`Hapus informasi akun "${data.account_name}"?`)) return;
        const form = document.getElementById('deleteAccountForm'); form.action = data.delete_url; form.submit();
    }));

    const revealModal = document.getElementById('revealModal'); let revealUrl = '';
    const setReveal = open => { revealModal.classList.toggle('hidden', !open); revealModal.classList.toggle('flex', open); revealModal.setAttribute('aria-hidden', open ? 'false' : 'true'); };
    document.querySelectorAll('[data-reveal-account]').forEach(button => button.addEventListener('click', () => {
        const data = JSON.parse(button.closest('[data-account-row]').dataset.account); revealUrl = data.reveal_url;
        document.getElementById('revealAccountName').textContent = data.account_name; document.getElementById('revealForm').reset();
        document.getElementById('revealError').classList.add('hidden'); document.getElementById('revealedPasswordBox').classList.add('hidden'); setReveal(true);
        setTimeout(() => document.getElementById('currentPassword').focus(), 50);
    }));
    document.querySelectorAll('[data-close-reveal-modal]').forEach(button => button.addEventListener('click', () => setReveal(false)));
    revealModal.addEventListener('click', event => { if (event.target === revealModal) setReveal(false); });
    document.getElementById('revealForm').addEventListener('submit', async event => {
        event.preventDefault(); const submit = document.getElementById('revealSubmit'); const error = document.getElementById('revealError');
        submit.disabled = true; submit.textContent = 'Memverifikasi...'; error.classList.add('hidden');
        try {
            const response = await fetch(revealUrl, { method: 'POST', credentials: 'same-origin', headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({current_password: document.getElementById('currentPassword').value}) });
            const payload = await response.json().catch(() => ({})); if (!response.ok) throw new Error(payload.errors?.current_password?.[0] || payload.message || 'Verifikasi gagal.');
            document.getElementById('revealedPassword').textContent = payload.password || '(password kosong)'; document.getElementById('revealedPasswordBox').classList.remove('hidden'); document.getElementById('currentPassword').value = '';
        } catch (exception) { error.textContent = exception.message; error.classList.remove('hidden'); }
        finally { submit.disabled = false; submit.textContent = 'Buka Password'; }
    });
    document.getElementById('copyRevealedPassword').addEventListener('click', async event => {
        await navigator.clipboard.writeText(document.getElementById('revealedPassword').textContent); const original = event.currentTarget.textContent; event.currentTarget.textContent = 'Tersalin'; setTimeout(() => event.currentTarget.textContent = original, 1200);
    });
});
</script>
@endsection
