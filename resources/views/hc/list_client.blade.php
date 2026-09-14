@extends('layout.app')

@section('content')

<div class="min-h-screen bg-[#f7f7f5] px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1600px]">

        {{-- =====================================================
            PAGE HEADER
        ====================================================== --}}
        <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            {{-- Title + Filter --}}
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="h-px w-6 bg-zinc-950"></span>

                    <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-zinc-500">
                        Project Management
                    </span>
                </div>

                <h1 class="text-3xl font-semibold tracking-[-0.03em] text-zinc-950 sm:text-4xl">
                    {{ $title }}
                </h1>

                <p class="mt-2 text-sm text-zinc-500">
                    Memantau progres seluruh project client secara real-time.
                </p>


                {{-- Filter Divisi --}}
                @if(Auth::user()->isAdmin)
                    <div class="mt-5 w-full sm:w-[280px]">

                        <label
                            for="filterDivisi"
                            class="mb-1.5 block text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-500"
                        >
                            Filter Divisi
                        </label>

                        <div class="relative">

                            {{-- Filter icon --}}
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-zinc-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-4 w-4"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path d="M22 3H2l8 9.46V19l4 2v-6.54L22 3z"/>
                                </svg>
                            </div>

                            <select
                                id="filterDivisi"
                                class="block w-full appearance-none rounded-xl border border-zinc-300 bg-white py-2.5 pl-10 pr-10 text-sm text-zinc-800 outline-none transition focus:border-zinc-950 focus:ring-2 focus:ring-zinc-950/10"
                            >
                                <option value="all">
                                    Semua Divisi
                                </option>

                                @foreach($projects->unique('nama_divisi') as $p)
                                    <option value="{{ $p['nama_divisi'] }}">
                                        {{ $p['nama_divisi'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Chevron --}}
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-zinc-400">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-4 w-4"
                                     viewBox="0 0 24 24"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </div>

                        </div>
                    </div>
                @endif
            </div>


            {{-- Total --}}
            <div class="shrink-0">

                <div class="inline-flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-4 py-2.5 shadow-[0_4px_20px_rgba(0,0,0,0.03)]">

                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-zinc-950 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-3.5 w-3.5"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <path d="M4 19h16"/>
                            <path d="M6 17V9M12 17V5M18 17v-7"/>
                        </svg>
                    </span>

                    <span class="text-sm font-medium text-zinc-700">
                        Total:
                    </span>

                    <span class="text-sm font-semibold text-zinc-950">
                        {{ $projects->count() }} Client
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
            PROJECT GRID
        ====================================================== --}}
        <div
            id="project-container"
            class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3"
        >

            @foreach($projects as $p)

                <div
                    class="project-card"
                    data-divisi="{{ $p['nama_divisi'] }}"
                >

                    <a
                        href="{{ route('hc_overview.index', $p['project_id']) }}"
                        class="group block h-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-[0_8px_30px_rgba(0,0,0,0.035)] transition duration-300 hover:-translate-y-1 hover:border-zinc-300 hover:shadow-[0_16px_40px_rgba(0,0,0,0.07)]"
                    >

                        {{-- Card body --}}
                        <div class="p-5 sm:p-6">

                            {{-- Top --}}
                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0 flex-1">

                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-zinc-400">
                                        Client
                                    </p>

                                    <h2
                                        class="truncate text-lg font-semibold tracking-[-0.02em] text-zinc-950 transition group-hover:text-zinc-600"
                                        title="{{ $p['nama_client'] }}"
                                    >
                                        {{ $p['nama_client'] }}
                                    </h2>

                                    @if(Auth::user()->isAdmin)
                                        <p class="mt-1 truncate text-xs text-zinc-500">
                                            {{ $p['nama_divisi'] }}
                                        </p>
                                    @endif

                                </div>


                                {{-- Percentage --}}
                                <div class="shrink-0 text-right">

                                    <span class="text-2xl font-semibold tracking-[-0.04em] text-zinc-950">
                                        {{ $p['percentage'] }}%
                                    </span>

                                    <p class="mt-0.5 text-[10px] uppercase tracking-[0.12em] text-zinc-400">
                                        Progress
                                    </p>

                                </div>

                            </div>


                            @if($p['total'] > 0)

                                {{-- =================================================
                                    PROGRESS
                                ================================================== --}}
                                <div class="mt-6">

                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-[10px] font-medium uppercase tracking-[0.12em] text-zinc-400">
                                            Completion
                                        </span>

                                        <span class="text-xs font-medium text-zinc-600">
                                            {{ $p['done'] }} / {{ $p['total'] }}
                                        </span>
                                    </div>

                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-zinc-100"
                                        role="progressbar"
                                        aria-valuenow="{{ $p['percentage'] }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <div
                                            class="h-full rounded-full bg-zinc-950 transition-all duration-500"
                                            style="width: {{ $p['percentage'] }}%"
                                        ></div>
                                    </div>

                                </div>


                                {{-- =================================================
                                    META
                                ================================================== --}}
                                <div class="mt-5 grid grid-cols-2 divide-x divide-zinc-200 border-t border-zinc-100 pt-4">

                                    {{-- Total KPI --}}
                                    <div class="pr-4">

                                        <div class="flex items-center gap-2">

                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-4 w-4"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                                                    <path d="M8 9h8M8 13h8M8 17h5"/>
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-[10px] uppercase tracking-[0.1em] text-zinc-400">
                                                    Total KPI
                                                </p>

                                                <p class="mt-0.5 text-sm font-semibold text-zinc-900">
                                                    {{ $p['total'] }}
                                                </p>
                                            </div>

                                        </div>

                                    </div>


                                    {{-- Selesai --}}
                                    <div class="pl-4">

                                        <div class="flex items-center gap-2">

                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-4 w-4"
                                                     viewBox="0 0 24 24"
                                                     fill="none"
                                                     stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <path d="M5 12l4 4L19 6"/>
                                                </svg>
                                            </div>

                                            <div>
                                                <p class="text-[10px] uppercase tracking-[0.1em] text-zinc-400">
                                                    Selesai
                                                </p>

                                                <p class="mt-0.5 text-sm font-semibold text-zinc-900">
                                                    {{ $p['done'] }}
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @else

                                {{-- =================================================
                                    EMPTY KPI
                                ================================================== --}}
                                <div class="mt-6 rounded-xl border border-dashed border-zinc-200 bg-zinc-50/70 px-4 py-7 text-center">

                                    <div class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-white text-zinc-400 ring-1 ring-zinc-200">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-5 w-5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="1.6">
                                            <path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9z"/>
                                            <path d="M3 7.5 12 12l9-4.5M12 12v9"/>
                                        </svg>

                                    </div>

                                    <p class="text-sm font-medium text-zinc-600">
                                        Belum ada KPI
                                    </p>

                                    <p class="mt-1 text-xs text-zinc-400">
                                        Belum ada KPI yang ditambahkan.
                                    </p>

                                </div>

                            @endif

                        </div>


                        {{-- =================================================
                            COMPLETED FOOTER
                        ================================================== --}}
                        @if($p['percentage'] == 100 && $p['total'] > 0)

                            <div class="border-t border-zinc-200 bg-zinc-950 px-5 py-3 sm:px-6">

                                <div class="flex items-center justify-between">

                                    <span class="text-[10px] font-semibold uppercase tracking-[0.18em] text-white">
                                        Project Completed
                                    </span>

                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white text-zinc-950">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-3.5 w-3.5"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2">
                                            <path d="M5 12l4 4L19 6"/>
                                        </svg>

                                    </span>

                                </div>

                            </div>

                        @else

                            {{-- Subtle bottom interaction --}}
                            <div class="flex items-center justify-between border-t border-zinc-100 px-5 py-3 sm:px-6">

                                <span class="text-[10px] font-medium uppercase tracking-[0.14em] text-zinc-400">
                                    View Project
                                </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-zinc-400 transition duration-300 group-hover:translate-x-1 group-hover:text-zinc-950"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path d="M5 12h14"/>
                                    <path d="m13 6 6 6-6 6"/>
                                </svg>

                            </div>

                        @endif

                    </a>

                </div>

            @endforeach

        </div>


        {{-- =====================================================
            EMPTY PROJECT STATE
        ====================================================== --}}
        @if($projects->count() === 0)

            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center">

                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.6">
                        <path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9z"/>
                        <path d="M3 7.5 12 12l9-4.5M12 12v9"/>
                    </svg>

                </div>

                <h2 class="text-base font-semibold text-zinc-900">
                    Belum ada project
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    Belum ada client project yang tersedia.
                </p>

            </div>

        @endif

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterSelect = document.getElementById('filterDivisi');

    if (!filterSelect) {
        return;
    }


    filterSelect.addEventListener('change', function () {

        const selectedDivisi = this.value;

        const cards = document.querySelectorAll('.project-card');


        cards.forEach(function (card) {

            const cardDivisi = card.getAttribute('data-divisi');

            const shouldShow =
                selectedDivisi === 'all' ||
                cardDivisi === selectedDivisi;


            if (shouldShow) {

                card.classList.remove('hidden');

            } else {

                card.classList.add('hidden');

            }

        });

    });

});
</script>

@endsection