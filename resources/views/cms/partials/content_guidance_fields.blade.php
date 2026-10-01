@php
    $guidanceSuffix = isset($item) ? '_'.$item->id : '';
@endphp
<div>
    <label for="fungsi_singkat{{ $guidanceSuffix }}" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Fungsi Singkat</label>
    <textarea name="fungsi_singkat" id="fungsi_singkat{{ $guidanceSuffix }}" rows="3" maxlength="1000"
        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
        placeholder="Jelaskan fungsi atau tujuan penggunaannya secara singkat">{{ old('fungsi_singkat', $item->fungsi_singkat ?? '') }}</textarea>
</div>
<div>
    <label for="contoh{{ $guidanceSuffix }}" class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900">Contoh</label>
    <textarea name="contoh" id="contoh{{ $guidanceSuffix }}" rows="4" maxlength="3000"
        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
        placeholder="Masukkan contoh penerapan atau penulisan">{{ old('contoh', $item->contoh ?? '') }}</textarea>
</div>
