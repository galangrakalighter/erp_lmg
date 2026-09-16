@extends('layout.app')

@section('content')
    <div class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

        {{-- =========================================================
            HEADER
        ========================================================== --}}
        <div class="mb-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                <div>
                    <h1 class="text-3xl font-semibold tracking-[-0.03em] text-zinc-950 sm:text-4xl">
                        Dashboard
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                        Ekosistem media digital terintegrasi untuk mendorong pertumbuhan
                        bisnis secara terukur dan berkelanjutan.
                    </p>
                </div>

            </div>
        </div>


        {{-- =========================================================
            VISION & MISSION
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            {{-- =====================================================
                VISION
            ====================================================== --}}
            <section
                class="relative min-h-[390px] overflow-hidden rounded-xl border border-zinc-200 bg-white"
            >

                <div class="relative flex h-full flex-col p-6 sm:p-8">

                    {{-- Header --}}
                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-zinc-400">
                                Vision
                            </p>

                            <h2 class="mt-2 text-xl font-semibold tracking-tight text-zinc-950">
                                Visi
                            </h2>
                        </div>

                        {{-- Icon --}}
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-950 text-white">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z"
                                />
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="2.75"
                                />
                            </svg>
                        </div>

                    </div>


                    {{-- Main Content --}}
                    <div class="mt-12 max-w-xl">

                        <p class="text-lg font-medium leading-8 tracking-[-0.015em] text-zinc-800 sm:text-xl">
                            "Menjadi ekosistem media digital terintegrasi yang profesional
                            dan terpercaya untuk mendorong pertumbuhan bisnis klien secara
                            terukur dan berkelanjutan di Indonesia."
                        </p>

                    </div>


                    {{-- Footer --}}
                    <div class="mt-auto flex items-center gap-3 pt-10">

                        <div class="h-px flex-1 bg-zinc-200"></div>

                        <span class="text-[9px] font-medium uppercase tracking-[0.25em] text-zinc-400">
                            LMG
                        </span>

                    </div>

                </div>
            </section>


            {{-- =====================================================
                MISSION
            ====================================================== --}}
            <section
                class="relative min-h-[390px] overflow-hidden rounded-xl bg-zinc-950 text-white"
            >


                <div class="relative flex h-full flex-col p-6 sm:p-8">

                    {{-- Header --}}
                    <div class="flex items-start justify-between">

                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-zinc-500">
                                Mission
                            </p>

                            <h2 class="mt-2 text-xl font-semibold tracking-tight text-white">
                                Misi
                            </h2>
                        </div>

                        {{-- Icon --}}
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white text-zinc-950">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                class="h-[18px] w-[18px]"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14.25 6.75 17.25 3.75M17.25 3.75h3v3M17.25 3.75l-7.5 7.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.25 8.25 4.5 12l3.75 3.75 3.75-3.75-3.75-3.75Z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 15.75 8.25 19.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M4.5 19.5h3"
                                />
                            </svg>
                        </div>

                    </div>


                    {{-- Mission List --}}
                    <div class="mt-9 space-y-4">

                        {{-- 01 --}}
                        <div class="flex items-start gap-3.5">

                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-zinc-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <p class="text-[13px] leading-5 text-zinc-300">
                                Mengintegrasikan media, layanan spesialis, teknologi,
                                dan commerce dalam satu sistem kerja.
                            </p>

                        </div>


                        {{-- 02 --}}
                        <div class="flex items-start gap-3.5">

                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-zinc-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <p class="text-[13px] leading-5 text-zinc-300">
                                Menjadikan media sebagai aset
                                <em class="text-white">traffic</em> dan
                                <em class="text-white">trust</em>
                                untuk pertumbuhan bisnis klien.
                            </p>

                        </div>


                        {{-- 03 --}}
                        <div class="flex items-start gap-3.5">

                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-zinc-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <p class="text-[13px] leading-5 text-zinc-300">
                                Mengembangkan strategi berbasis data, riset,
                                dan performa yang terukur.
                            </p>

                        </div>


                        {{-- 04 --}}
                        <div class="flex items-start gap-3.5">

                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-zinc-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <p class="text-[13px] leading-5 text-zinc-300">
                                Memanfaatkan teknologi dan AI untuk efisiensi
                                dan skalabilitas.
                            </p>

                        </div>


                        {{-- 05 --}}
                        <div class="flex items-start gap-3.5">

                            <span
                                class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-zinc-700"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    class="h-2.5 w-2.5"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m5 12 4 4L19 6"
                                    />
                                </svg>
                            </span>

                            <p class="text-[13px] leading-5 text-zinc-300">
                                Membangun tim spesialis yang solid untuk
                                eksekusi berkualitas.
                            </p>

                        </div>

                    </div>


                    {{-- Footer --}}
                    <div class="mt-auto flex items-center gap-3 pt-8">

                        <div class="h-px flex-1 bg-zinc-800"></div>

                        <span class="text-[9px] font-medium uppercase tracking-[0.25em] text-zinc-600">
                            Integrated
                        </span>

                    </div>

                </div>
            </section>

        </div>

    </div>
@endsection