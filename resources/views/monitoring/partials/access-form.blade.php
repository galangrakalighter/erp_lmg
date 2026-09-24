<div>
    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600">Pengguna membagikan layar</label>
    <select name="target_user_id" required class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none focus:border-zinc-950">
        <option value="">Pilih pengguna target</option>
        @foreach($users as $user)<option value="{{ $user->id }}" @selected(old('target_user_id', $access?->target_user_id) == $user->id)>{{ $user->name ?: $user->email }} — {{ $user->email }}</option>@endforeach
    </select>
</div>
<div>
    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-600">Pengguna yang memantau</label>
    <select name="viewer_user_id" required class="w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-sm text-zinc-800 outline-none focus:border-zinc-950">
        <option value="">Pilih pengguna pemantau</option>
        @foreach($users as $user)<option value="{{ $user->id }}" @selected(old('viewer_user_id', $access?->viewer_user_id) == $user->id)>{{ $user->name ?: $user->email }} — {{ $user->email }}</option>@endforeach
    </select>
</div>
