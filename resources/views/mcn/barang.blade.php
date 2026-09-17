@extends('layout.app')

@section('content')
@include('mcn.partials.theme')

<div class="mcn-workspace">

    <div class="mx-auto max-w-[1600px]">

        @include('mcn.partials.header', ['heading' => 'Project MCN', 'description' => 'Kelola project, daftar barang, shipping, dan seluruh komponen biaya.', 'actionLabel' => 'Tambah Project', 'actionAttributes' => 'onclick="document.getElementById(\'modalTambahToggle\').checked=true"'])


        {{-- MAIN CARD --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">

            {{-- CARD HEADER --}}
            <div class="border-b border-zinc-200 px-5 py-5 sm:px-6">

                <div class="flex items-center justify-between gap-4">

                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-zinc-400">
                            Project
                        </p>

                        <h2 class="mt-1 text-lg font-bold text-zinc-950">
                            Daftar Project
                        </h2>
                    </div>

                    <div class="hidden rounded-lg bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-500 sm:block">
                        Data dimuat otomatis
                    </div>

                </div>

            </div>


            {{-- TABLE --}}
            <div class="overflow-x-auto">

                <table
                    id="projectMcnTable"
                    data-native-table="project"
                    data-api="{{ route('api.mcn.projects') }}"
                    class="w-full min-w-[1100px] border-collapse text-left"
                >

                    <thead>
                        <tr class="border-b border-zinc-200 bg-[#161616]">

                            <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                No
                            </th>

                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Project
                            </th>

                            <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Barang
                            </th>

                            <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Shipping
                            </th>

                            <th class="px-4 py-4 text-right text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Ongkir
                            </th>

                            <th class="px-4 py-4 text-right text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Biaya Layanan
                            </th>

                            <th class="px-4 py-4 text-right text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Biaya Penanganan
                            </th>

                            <th class="px-4 py-4 text-center text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Action
                            </th>

                        </tr>
                    </thead>


                    <tbody class="divide-y divide-zinc-100">

                        @forelse ($data as $d)

                            @php
                                $barangArray = is_array($d->barang)
                                    ? $d->barang
                                    : json_decode($d->barang, true);

                                $shippingArray = is_array($d->shipping)
                                    ? $d->shipping
                                    : json_decode($d->shipping, true);

                                $itemBarang = $barangArray[0] ?? [];
                                $itemShipping = $shippingArray[0] ?? [];
                            @endphp

                            <tr class="transition hover:bg-zinc-50">

                                {{-- NO --}}
                                <td class="px-4 py-5 text-center text-sm font-semibold text-zinc-400">
                                    {{ $loop->iteration }}
                                </td>


                                {{-- PROJECT --}}
                                <td class="px-4 py-5">

                                    <div class="max-w-[220px]">
                                        <p class="font-bold text-zinc-900">
                                            {{ $d->project }}
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-400">
                                            Project #{{ $d->id }}
                                        </p>
                                    </div>

                                </td>


                                {{-- BARANG --}}
                                <td class="px-4 py-5">

                                    <div class="flex flex-wrap justify-center gap-2">

                                        @if(!empty($itemBarang))

                                            @foreach($itemBarang as $key => $val)

                                                @if(str_contains($key, 'kategori_barang'))

                                                    @php
                                                        $index = str_replace('kategori_barang_', '', $key);
                                                        $hargaKey = 'harga_barang_' . $index;
                                                        $harga = $itemBarang[$hargaKey] ?? 0;
                                                    @endphp

                                                    <div class="flex min-w-[145px] flex-col items-center rounded-xl border border-zinc-200 bg-white px-3 py-3 shadow-sm">

                                                        <div class="flex items-center gap-1.5 text-center text-xs font-bold text-zinc-800">
                                                            <i
                                                                data-lucide="package"
                                                                class="h-3.5 w-3.5 shrink-0 text-zinc-500"
                                                            ></i>

                                                            <span>
                                                                {{ is_array($val) ? implode(', ', $val) : $val }}
                                                            </span>
                                                        </div>

                                                        <div class="mt-1.5 text-xs font-bold text-zinc-950">
                                                            Rp {{ number_format((float)$harga, 0, ',', '.') }}
                                                        </div>

                                                    </div>

                                                @endif

                                            @endforeach

                                        @else

                                            <span class="text-xs italic text-zinc-400">
                                                Tidak ada data barang
                                            </span>

                                        @endif

                                    </div>

                                </td>


                                {{-- SHIPPING --}}
                                <td class="px-4 py-5">

                                    <div class="flex flex-wrap justify-center gap-2">

                                        @if(!empty($itemShipping))

                                            @foreach($itemShipping as $key => $val)

                                                @if(str_contains($key, 'kategori_shipping'))

                                                    @php
                                                        $indexShipping = str_replace('kategori_shipping_', '', $key);
                                                        $hargaShippingKey = 'harga_shipping_' . $indexShipping;
                                                        $hargaShipping = $itemShipping[$hargaShippingKey] ?? 0;
                                                    @endphp

                                                    <div class="flex min-w-[145px] flex-col items-center rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-3">

                                                        <div class="flex items-center gap-1.5 text-center text-xs font-bold text-zinc-800">

                                                            <i
                                                                data-lucide="truck"
                                                                class="h-3.5 w-3.5 shrink-0 text-zinc-500"
                                                            ></i>

                                                            <span>
                                                                {{ is_array($val) ? implode(', ', $val) : $val }}
                                                            </span>

                                                        </div>

                                                        <div class="mt-1.5 text-xs font-bold text-zinc-950">
                                                            Rp {{ number_format((float)$hargaShipping, 0, ',', '.') }}
                                                        </div>

                                                    </div>

                                                @endif

                                            @endforeach

                                        @else

                                            <span class="text-xs italic text-zinc-400">
                                                Tidak ada data shipping
                                            </span>

                                        @endif

                                    </div>

                                </td>


                                {{-- ONGKIR --}}
                                <td class="whitespace-nowrap px-4 py-5 text-right">
                                    <span class="text-sm font-bold text-zinc-900">
                                        Rp {{ number_format((float)$d->ongkir, 0, ',', '.') }}
                                    </span>
                                </td>


                                {{-- LAYANAN --}}
                                <td class="whitespace-nowrap px-4 py-5 text-right">
                                    <span class="text-sm font-bold text-zinc-900">
                                        Rp {{ number_format((float)$d->biaya_layanan_pembeli, 0, ',', '.') }}
                                    </span>
                                </td>


                                {{-- PENANGANAN --}}
                                <td class="whitespace-nowrap px-4 py-5 text-right">
                                    <span class="text-sm font-bold text-zinc-900">
                                        Rp {{ number_format((float)$d->biaya_penanganan, 0, ',', '.') }}
                                    </span>
                                </td>


                                {{-- ACTION --}}
                                <td class="px-4 py-5">

                                    <div class="flex items-center justify-center gap-2">

                                        <label
                                            for="modalEditToggle{{ $d->id }}"
                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 transition hover:border-zinc-900 hover:bg-zinc-950 hover:text-white"
                                            title="Edit"
                                        >
                                            <i data-lucide="pencil" class="h-4 w-4"></i>
                                        </label>


                                        <label
                                            for="modalHapusToggle{{ $d->id }}"
                                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                                            title="Hapus"
                                        >
                                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                                        </label>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="8"
                                    class="px-6 py-16 text-center"
                                >
                                    <div class="mx-auto flex max-w-sm flex-col items-center">

                                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                            <i data-lucide="folder-open" class="h-6 w-6"></i>
                                        </div>

                                        <h3 class="font-bold text-zinc-900">
                                            Belum ada project
                                        </h3>

                                        <p class="mt-1 text-sm text-zinc-500">
                                            Tambahkan project baru untuk mulai mengelola data.
                                        </p>

                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div id="projectMcnTablePagination" class="native-table-pagination"></div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- MODAL TAMBAH --}}
{{-- ========================================================= --}}

<input
    type="checkbox"
    id="modalTambahToggle"
    class="peer sr-only"
>

<div class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center overflow-y-auto p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100 sm:p-6">

    <label
        for="modalTambahToggle"
        class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
    ></label>

    <div
        class="relative z-10 my-auto w-full max-w-4xl scale-95 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100"
    >

        <form action="{{ route('barang_client.store') }}" method="POST">

            @csrf

            {{-- HEADER --}}
            <div class="flex items-center justify-between bg-[#161616] px-6 py-5 sm:px-7">

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                        Project Management
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-white">
                        Tambah Project Baru
                    </h2>
                </div>

                <label
                    for="modalTambahToggle"
                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                >
                    <i data-lucide="x" class="h-5 w-5"></i>
                </label>

            </div>


            {{-- BODY --}}
            <div class="max-h-[75vh] overflow-y-auto bg-zinc-50 px-6 py-6 sm:px-7">

                {{-- PROJECT --}}
                <div class="mb-6">

                    <label
                        for="project_tambah"
                        class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900"
                    >
                        Nama Project
                    </label>

                    <input
                        type="text"
                        name="project"
                        id="project_tambah"
                        class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        placeholder="Masukkan nama project..."
                        required
                    >

                </div>


                {{-- BARANG & SHIPPING --}}
                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- BARANG --}}
                    <div>

                        <div class="mb-3 flex items-center justify-between">

                            <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">
                                <i data-lucide="package" class="h-4 w-4 text-zinc-500"></i>
                                Daftar Barang
                            </label>

                        </div>

                        <div
                            id="barang-container"
                            class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4"
                        >

                            <div class="barang-row">

                                <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                                    <input
                                        type="text"
                                        name="barang[]"
                                        class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Nama Barang"
                                        required
                                    >

                                    <input
                                        type="text"
                                        name="harga_barang[]"
                                        class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Harga"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="add-barang inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl bg-[#161616] text-white transition hover:bg-zinc-800"
                                        data-target="#barang-container"
                                    >
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- SHIPPING --}}
                    <div>

                        <label class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">
                            <i data-lucide="truck" class="h-4 w-4 text-zinc-500"></i>
                            Daftar Shipping
                        </label>

                        <div
                            id="shipping-container"
                            class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4"
                        >

                            <div class="shipping-row">

                                <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                                    <input
                                        type="text"
                                        name="shipping[]"
                                        class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Nama Shipping"
                                        required
                                    >

                                    <input
                                        type="text"
                                        name="harga_shipping[]"
                                        class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                        placeholder="Harga"
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="add-shipping inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl bg-[#161616] text-white transition hover:bg-zinc-800"
                                        data-target="#shipping-container"
                                    >
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- COST --}}
                <div class="mt-6">

                    <div class="mb-4 border-t border-zinc-200 pt-6">

                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-zinc-400">
                            Biaya Project
                        </p>

                    </div>

                    <div class="grid gap-4 md:grid-cols-3">

                        <div>

                            <label
                                for="ongkir_tambah"
                                class="mb-2 block text-xs font-bold text-zinc-700"
                            >
                                Ongkos Kirim
                            </label>

                            <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                    Rp
                                </span>

                                <input
                                    type="text"
                                    name="ongkir"
                                    id="ongkir_tambah"
                                    class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                    placeholder="0"
                                >

                            </div>

                        </div>


                        <div>

                            <label
                                for="layanan_tambah"
                                class="mb-2 block text-xs font-bold text-zinc-700"
                            >
                                Biaya Layanan
                            </label>

                            <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                    Rp
                                </span>

                                <input
                                    type="text"
                                    name="biaya_layanan"
                                    id="layanan_tambah"
                                    class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                    placeholder="0"
                                >

                            </div>

                        </div>


                        <div>

                            <label
                                for="penanganan_tambah"
                                class="mb-2 block text-xs font-bold text-zinc-700"
                            >
                                Biaya Penanganan
                            </label>

                            <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                    Rp
                                </span>

                                <input
                                    type="text"
                                    name="biaya_penanganan"
                                    id="penanganan_tambah"
                                    class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                    placeholder="0"
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}
            <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 bg-white px-6 py-5 sm:flex-row sm:justify-end sm:px-7">

                <label
                    for="modalTambahToggle"
                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900"
                >
                    Batal
                </label>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#161616] px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-800"
                >
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Simpan Project
                </button>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- MODAL EDIT & DELETE --}}
{{-- ========================================================= --}}

@foreach ($data as $d)

    @php
        $barangData = is_array($d->barang)
            ? $d->barang
            : json_decode($d->barang, true);

        $shippingData = is_array($d->shipping)
            ? $d->shipping
            : json_decode($d->shipping, true);

        $itemBarang = (isset($barangData[0]) && is_array($barangData[0]))
            ? $barangData[0]
            : $barangData;

        $itemShipping = (isset($shippingData[0]) && is_array($shippingData[0]))
            ? $shippingData[0]
            : $shippingData;
    @endphp


    {{-- ===================================================== --}}
    {{-- MODAL EDIT --}}
    {{-- ===================================================== --}}

    <input
        type="checkbox"
        id="modalEditToggle{{ $d->id }}"
        class="peer sr-only"
    >

    <div class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center overflow-y-auto p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100 sm:p-6">

        <label
            for="modalEditToggle{{ $d->id }}"
            class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
        ></label>

        <div
            class="relative z-10 my-auto w-full max-w-4xl scale-95 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100"
        >

            <form
                action="{{ route('barang_client.update', $d->id) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                {{-- HEADER --}}
                <div class="flex items-center justify-between bg-[#161616] px-6 py-5 sm:px-7">

                    <div class="min-w-0">

                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                            Project Management
                        </p>

                        <h2 class="mt-1 truncate text-lg font-bold text-white">
                            Edit Project
                        </h2>

                        <p class="mt-1 truncate text-xs text-zinc-400">
                            {{ $d->project }}
                        </p>

                    </div>

                    <label
                        for="modalEditToggle{{ $d->id }}"
                        class="ml-4 flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                    >
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </label>

                </div>


                {{-- BODY --}}
                <div class="max-h-[75vh] overflow-y-auto bg-zinc-50 px-6 py-6 sm:px-7">

                    {{-- PROJECT --}}
                    <div class="mb-6">

                        <label
                            for="project_edit_{{ $d->id }}"
                            class="mb-2 block text-xs font-bold uppercase tracking-wider text-zinc-900"
                        >
                            Nama Project
                        </label>

                        <input
                            type="text"
                            name="project"
                            id="project_edit_{{ $d->id }}"
                            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-900 outline-none transition focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                            value="{{ $d->project }}"
                            required
                        >

                    </div>


                    {{-- BARANG + SHIPPING --}}
                    <div class="grid gap-6 lg:grid-cols-2">

                        {{-- BARANG --}}
                        <div>

                            <label class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">

                                <i
                                    data-lucide="package"
                                    class="h-4 w-4 text-zinc-500"
                                ></i>

                                Daftar Barang & Harga

                            </label>

                            <div
                                id="edit-barang-container-{{ $d->id }}"
                                class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4"
                            >

                                @php $i = 1; @endphp

                                @while(isset($barangData[0]["kategori_barang_$i"]))

                                    <div class="barang-row">

                                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                                            <input
                                                type="text"
                                                name="barang[]"
                                                class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                value="{{ $barangData[0]["kategori_barang_$i"] }}"
                                                placeholder="Nama Barang"
                                            >

                                            <input
                                                type="text"
                                                name="harga_barang[]"
                                                class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                value="Rp {{ number_format($barangData[0]["harga_barang_$i"] ?? 0, 0, ',', '.') }}"
                                                placeholder="Harga"
                                            >

                                            <button
                                                type="button"
                                                class="remove-barang inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                                            >
                                                <i data-lucide="minus" class="h-4 w-4"></i>
                                            </button>

                                        </div>

                                    </div>

                                    @php $i++; @endphp

                                @endwhile

                            </div>


                            <button
                                type="button"
                                class="add-barang mt-3 inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-bold text-zinc-700 transition hover:border-zinc-900 hover:bg-zinc-950 hover:text-white"
                                data-target="#edit-barang-container-{{ $d->id }}"
                            >
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Tambah Barang
                            </button>

                        </div>


                        {{-- SHIPPING --}}
                        <div>

                            <label class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-900">

                                <i
                                    data-lucide="truck"
                                    class="h-4 w-4 text-zinc-500"
                                ></i>

                                Daftar Shipping & Harga

                            </label>

                            <div
                                id="edit-shipping-container-{{ $d->id }}"
                                class="space-y-3 rounded-2xl border border-zinc-200 bg-white p-4"
                            >

                                @php $j = 1; @endphp

                                @while(isset($shippingData[0]["kategori_shipping_$j"]))

                                    <div class="shipping-row">

                                        <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                                            <input
                                                type="text"
                                                name="shipping[]"
                                                class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                value="{{ $shippingData[0]["kategori_shipping_$j"] }}"
                                                placeholder="Nama Shipping"
                                            >

                                            <input
                                                type="text"
                                                name="harga_shipping[]"
                                                class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                                                value="Rp {{ number_format($shippingData[0]["harga_shipping_$j"] ?? 0, 0, ',', '.') }}"
                                                placeholder="Harga"
                                            >

                                            <button
                                                type="button"
                                                class="remove-shipping inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                                            >
                                                <i data-lucide="minus" class="h-4 w-4"></i>
                                            </button>

                                        </div>

                                    </div>

                                    @php $j++; @endphp

                                @endwhile

                            </div>


                            <button
                                type="button"
                                class="add-shipping mt-3 inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-xs font-bold text-zinc-700 transition hover:border-zinc-900 hover:bg-zinc-950 hover:text-white"
                                data-target="#edit-shipping-container-{{ $d->id }}"
                            >
                                <i data-lucide="plus" class="h-4 w-4"></i>
                                Tambah Shipping
                            </button>

                        </div>

                    </div>


                    {{-- COST --}}
                    <div class="mt-7 border-t border-zinc-200 pt-6">

                        <p class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-zinc-400">
                            Biaya Project
                        </p>

                        <div class="grid gap-4 md:grid-cols-3">

                            <div>

                                <label
                                    for="ongkir_edit_{{ $d->id }}"
                                    class="mb-2 block text-xs font-bold text-zinc-700"
                                >
                                    Ongkos Kirim
                                </label>

                                <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                    <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                        Rp
                                    </span>

                                    <input
                                        type="number"
                                        name="ongkir"
                                        id="ongkir_edit_{{ $d->id }}"
                                        class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                        value="{{ number_format($d->ongkir ?? 0, 0, ',', '.') }}"
                                        placeholder="0"
                                    >

                                </div>

                            </div>


                            <div>

                                <label
                                    for="layanan_edit_{{ $d->id }}"
                                    class="mb-2 block text-xs font-bold text-zinc-700"
                                >
                                    Biaya Layanan
                                </label>

                                <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                    <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                        Rp
                                    </span>

                                    <input
                                        type="number"
                                        name="biaya_layanan"
                                        id="layanan_edit_{{ $d->id }}"
                                        class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                        value="{{ number_format($d->biaya_layanan ?? 0, 0, ',', '.') }}"
                                        placeholder="0"
                                    >

                                </div>

                            </div>


                            <div>

                                <label
                                    for="penanganan_edit_{{ $d->id }}"
                                    class="mb-2 block text-xs font-bold text-zinc-700"
                                >
                                    Biaya Penanganan
                                </label>

                                <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-[#161616] focus-within:ring-4 focus-within:ring-zinc-900/5">

                                    <span class="flex items-center border-r border-zinc-200 px-3 text-xs font-bold text-zinc-400">
                                        Rp
                                    </span>

                                    <input
                                        type="number"
                                        name="biaya_penanganan"
                                        id="penanganan_edit_{{ $d->id }}"
                                        class="format-rupiah min-w-0 flex-1 border-0 bg-transparent px-3 py-3 text-sm text-zinc-900 outline-none"
                                        value="{{ number_format($d->biaya_penanganan ?? 0, 0, ',', '.') }}"
                                        placeholder="0"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 bg-white px-6 py-5 sm:flex-row sm:justify-end sm:px-7">

                    <label
                        for="modalEditToggle{{ $d->id }}"
                        class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900"
                    >
                        Batal
                    </label>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#161616] px-5 py-3 text-sm font-bold text-white transition hover:bg-zinc-800"
                    >
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Update Project
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- MODAL HAPUS --}}
    {{-- ===================================================== --}}

    <input
        type="checkbox"
        id="modalHapusToggle{{ $d->id }}"
        class="peer sr-only"
    >

    <div class="pointer-events-none fixed inset-0 z-[100000] flex items-center justify-center overflow-y-auto p-4 opacity-0 transition duration-200 peer-checked:pointer-events-auto peer-checked:opacity-100">

        <label
            for="modalHapusToggle{{ $d->id }}"
            class="absolute inset-0 cursor-pointer bg-zinc-950/60 backdrop-blur-sm"
        ></label>

        <div
            class="relative z-10 w-full max-w-md scale-95 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl transition duration-200 peer-checked:scale-100"
        >

            {{-- HEADER --}}
            <div class="flex items-center justify-between bg-[#161616] px-6 py-5">

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-zinc-400">
                        Confirmation
                    </p>

                    <h2 class="mt-1 text-lg font-bold text-white">
                        Konfirmasi Hapus
                    </h2>
                </div>

                <label
                    for="modalHapusToggle{{ $d->id }}"
                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg text-zinc-400 transition hover:bg-white/10 hover:text-white"
                >
                    <i data-lucide="x" class="h-5 w-5"></i>
                </label>

            </div>


            {{-- BODY --}}
            <div class="px-6 py-6">

                <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                    <i data-lucide="trash-2" class="h-6 w-6"></i>
                </div>

                <p class="text-sm leading-6 text-zinc-600">
                    Apakah Anda yakin ingin menghapus project
                    <strong class="font-bold text-zinc-950">
                        {{ $d->project }}
                    </strong>
                    ?
                </p>

                <div class="mt-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3">
                    <p class="text-xs font-medium leading-5 text-red-600">
                        Data yang sudah dihapus tidak dapat dikembalikan.
                    </p>
                </div>

            </div>


            {{-- FOOTER --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-5">

                <label
                    for="modalHapusToggle{{ $d->id }}"
                    class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-zinc-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
                >
                    Batal
                </label>

                <form
                    action="{{ route('barang_client.destroy', $d->id) }}"
                    method="POST"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700"
                    >
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                        Ya, Hapus
                    </button>

                </form>

            </div>

        </div>

    </div>

@endforeach


<script>
    function formatRupiah(angka, prefix) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined
            ? rupiah + ',' + split[1]
            : rupiah;

        return prefix == undefined
            ? rupiah
            : (rupiah ? 'Rp' + rupiah : '');
    }


    $(document).on('keyup', '.format-rupiah', function() {
        $(this).val(formatRupiah($(this).val()));
    });


    $(document).on('click', '.add-barang', function() {

        let targetSelector = $(this).data('target');
        let $container = $(targetSelector);

        let newRow = `
            <div class="barang-row">

                <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                    <input
                        type="text"
                        name="barang[]"
                        class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        placeholder="Nama Barang"
                        required
                    >

                    <input
                        type="text"
                        name="harga_barang[]"
                        class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        placeholder="Harga Barang"
                        required
                    >

                    <button
                        type="button"
                        class="remove-barang inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                    >
                        <i data-lucide="minus" class="h-4 w-4"></i>
                    </button>

                </div>

            </div>
        `;

        $container.append(newRow);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });


    $(document).on('click', '.add-shipping', function() {

        let targetSelector = $(this).data('target');
        let $container = $(targetSelector);

        let newRow = `
            <div class="shipping-row">

                <div class="grid grid-cols-[1fr_1fr_auto] gap-2">

                    <input
                        type="text"
                        name="shipping[]"
                        class="min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        placeholder="Nama Barang Shipping"
                        required
                    >

                    <input
                        type="text"
                        name="harga_shipping[]"
                        class="format-rupiah min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-3 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:border-[#161616] focus:ring-4 focus:ring-zinc-900/5"
                        placeholder="Harga Barang Shipping"
                        required
                    >

                    <button
                        type="button"
                        class="remove-shipping inline-flex h-[46px] w-[46px] items-center justify-center rounded-xl border border-zinc-200 bg-white text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white"
                    >
                        <i data-lucide="minus" class="h-4 w-4"></i>
                    </button>

                </div>

            </div>
        `;

        $container.append(newRow);

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });


    $(document).on('click', '.remove-barang', function() {
        $(this).closest('.barang-row').remove();
    });


    $(document).on('click', '.remove-shipping', function() {
        $(this).closest('.shipping-row').remove();
    });


    document.addEventListener('DOMContentLoaded', function() {

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

    });
</script>

@endsection
