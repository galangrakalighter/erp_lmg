@extends('layout.app')

@section('content')
<div class="mx-auto w-full max-w-[1200px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <header class="border-b border-zinc-200 pb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-950 sm:text-3xl">Dashboard</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600">
            Ringkasan arah kerja Lighter Media Group dan prinsip yang menjadi dasar kolaborasi setiap divisi.
        </p>
    </header>

    <div class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-lg border border-zinc-200 bg-white p-6 sm:p-8" aria-labelledby="vision-heading">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                        <circle cx="12" cy="12" r="2.75" />
                    </svg>
                </div>
                <h2 id="vision-heading" class="text-lg font-semibold text-zinc-950">Visi</h2>
            </div>

            <blockquote class="mt-8 border-l-2 border-zinc-900 pl-5 text-lg font-medium leading-8 text-zinc-800">
                Menjadi ekosistem media digital terintegrasi yang profesional dan terpercaya untuk mendorong pertumbuhan bisnis klien secara terukur dan berkelanjutan di Indonesia.
            </blockquote>
        </section>

        <section class="rounded-lg border border-zinc-200 bg-white p-6 sm:p-8" aria-labelledby="mission-heading">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-950 text-white" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-[18px] w-[18px]">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m5 12 4 4L19 6" />
                    </svg>
                </div>
                <h2 id="mission-heading" class="text-lg font-semibold text-zinc-950">Misi</h2>
            </div>

            <ol class="mt-6 divide-y divide-zinc-100">
                @foreach ([
                    'Mengintegrasikan media, layanan spesialis, teknologi, dan commerce dalam satu sistem kerja.',
                    'Menjadikan media sebagai aset traffic dan trust untuk pertumbuhan bisnis klien.',
                    'Mengembangkan strategi berbasis data, riset, dan performa yang terukur.',
                    'Memanfaatkan teknologi dan AI untuk efisiensi dan skalabilitas.',
                    'Membangun tim spesialis yang solid untuk eksekusi berkualitas.',
                ] as $mission)
                    <li class="flex gap-4 py-4 first:pt-0 last:pb-0">
                        <span class="text-sm font-semibold tabular-nums text-zinc-400">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <p class="text-sm leading-6 text-zinc-700">{{ $mission }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</div>
@endsection
