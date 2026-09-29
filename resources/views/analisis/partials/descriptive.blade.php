@php
    $descriptivePosts = collect($analysisPosts ?? []);
    $postCount = $descriptivePosts->count();
    $averageReach = $postCount ? (int) round($descriptivePosts->avg(fn ($post) => data_get($post, 'reach', 0))) : 0;
    $averageEngagement = $postCount ? (int) round($descriptivePosts->avg(fn ($post) => data_get($post, 'engagement', 0))) : 0;
    $engagementRate = $totalReach > 0 ? ($totalEngagement / $totalReach) * 100 : 0;
    $impressionFrequency = $totalReach > 0 ? $totalImpressions / $totalReach : 0;
    $topPosts = $descriptivePosts->sortByDesc(fn ($post) => data_get($post, 'engagement', 0))->take(3)->values();
    $bestPost = $topPosts->first();
    $recommendations = [];

    if ($postCount === 0) {
        $recommendations[] = 'Belum ada posting yang dapat dianalisis pada periode ini. Periksa rentang tanggal atau koneksi akun.';
    } else {
        $recommendations[] = 'Gunakan pola topik dan format dari konten dengan engagement tertinggi sebagai referensi konten berikutnya.';
        if ($engagementRate < 1) {
            $recommendations[] = 'Rasio engagement masih di bawah 1%. Perkuat pembuka, ajakan bertindak, dan relevansi topik untuk audiens.';
        } elseif ($engagementRate >= 3) {
            $recommendations[] = 'Rasio engagement tergolong kuat. Pertahankan pola konten terbaik dan uji variasi distribusinya.';
        }
        if ($impressionFrequency > 2.5) {
            $recommendations[] = 'Frekuensi impresi cukup tinggi. Pantau kejenuhan audiens dan variasikan materi kreatif.';
        } else {
            $recommendations[] = 'Perluas distribusi konten terbaik untuk meningkatkan jumlah paparan per audiens yang dijangkau.';
        }
    }
@endphp

<div class="mb-4 rounded-xl border border-zinc-200 bg-white p-1 shadow-sm" data-analysis-tabs>
    <div class="flex flex-wrap gap-1" role="tablist" aria-label="Jenis tampilan analisis">
        <button type="button" data-analysis-tab="descriptive" class="analysis-view-tab rounded-lg bg-zinc-950 px-4 py-2.5 text-xs font-bold text-white">Deskriptif</button>
        <button type="button" data-analysis-tab="metrics" class="analysis-view-tab rounded-lg px-4 py-2.5 text-xs font-bold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-950">Metrik & Tabel</button>
    </div>
</div>

<style>
    [data-analysis-panel][hidden] { display: none !important; }
</style>

<section data-analysis-panel="descriptive" class="mb-4 space-y-4">
    <div class="rounded-2xl border border-zinc-200 bg-zinc-950 p-6 text-white shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-[.2em] text-lime-300">Ringkasan {{ $platform }}</p>
        <h2 class="mt-2 text-xl font-semibold">Gambaran performa konten</h2>
        @if($postCount)
            <p class="mt-3 max-w-4xl text-sm leading-7 text-zinc-300">
                Pada periode yang dipilih terdapat <strong class="text-white">{{ number_format($postCount, 0, ',', '.') }} posting</strong>
                dengan total jangkauan <strong class="text-white">{{ number_format($totalReach, 0, ',', '.') }}</strong> dan
                engagement <strong class="text-white">{{ number_format($totalEngagement, 0, ',', '.') }}</strong>.
                Rata-rata setiap posting menjangkau {{ number_format($averageReach, 0, ',', '.') }} akun dan menghasilkan
                {{ number_format($averageEngagement, 0, ',', '.') }} interaksi.
            </p>
        @else
            <p class="mt-3 text-sm leading-7 text-zinc-300">Belum ada data posting yang berhasil dibaca untuk periode yang dipilih.</p>
        @endif
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Jumlah posting', 'value' => number_format($postCount, 0, ',', '.'), 'note' => 'Konten dalam periode'],
            ['label' => 'Rata-rata reach', 'value' => number_format($averageReach, 0, ',', '.'), 'note' => 'Per posting'],
            ['label' => 'Engagement rate', 'value' => number_format($engagementRate, 2, ',', '.').'%', 'note' => 'Engagement ÷ reach'],
            ['label' => 'Frekuensi impresi', 'value' => number_format($impressionFrequency, 2, ',', '.').'x', 'note' => 'Impresi ÷ reach'],
        ] as $item)
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">{{ $item['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold text-zinc-950">{{ $item['value'] }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $item['note'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-[1.35fr_.65fr]">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <div class="mb-4"><p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Konten unggulan</p><h3 class="mt-1 text-base font-semibold text-zinc-950">Posting dengan engagement tertinggi</h3></div>
            <div class="space-y-2">
                @forelse($topPosts as $post)
                    @php
                        $postLink = data_get($post, 'link', data_get($post, 'link_content', '#'));
                        $postImpressions = data_get($post, 'impressions', data_get($post, 'impresi', 0));
                    @endphp
                    <a href="{{ $postLink }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-4 rounded-xl border border-zinc-100 bg-zinc-50 px-4 py-3 text-decoration-none transition hover:border-zinc-300">
                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-zinc-900">{{ data_get($post, 'judul', 'Konten tanpa judul') ?: 'Konten tanpa judul' }}</span><span class="mt-1 block text-xs text-zinc-500">{{ data_get($post, 'tanggal_posting', '-') }} · Reach {{ number_format(data_get($post, 'reach', 0), 0, ',', '.') }} · Impresi {{ number_format($postImpressions, 0, ',', '.') }}</span></span>
                        <span class="shrink-0 rounded-lg bg-zinc-950 px-3 py-1.5 text-xs font-bold text-white">{{ number_format(data_get($post, 'engagement', 0), 0, ',', '.') }} interaksi</span>
                    </a>
                @empty
                    <div class="rounded-xl border border-dashed border-zinc-200 p-8 text-center text-sm text-zinc-500">Belum ada konten untuk dibandingkan.</div>
                @endforelse
            </div>
        </div>

        <aside class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-400">Catatan tindak lanjut</p>
            <h3 class="mt-1 text-base font-semibold text-zinc-950">Rekomendasi</h3>
            <ol class="mt-4 space-y-3">
                @foreach($recommendations as $recommendation)
                    <li class="flex gap-3 text-sm leading-6 text-zinc-600"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-lime-100 text-[10px] font-bold text-lime-800">{{ $loop->iteration }}</span><span>{{ $recommendation }}</span></li>
                @endforeach
            </ol>
        </aside>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function activateAnalysisView(active) {
        document.querySelectorAll('[data-analysis-panel]').forEach(function (panel) {
            const selected = panel.dataset.analysisPanel === active;
            panel.hidden = !selected;
            panel.style.display = selected ? '' : 'none';
            panel.classList.toggle('hidden', !selected);
        });
        document.querySelectorAll('[data-analysis-tab]').forEach(function (tab) {
            const selected = tab.dataset.analysisTab === active;
            tab.classList.toggle('bg-zinc-950', selected);
            tab.classList.toggle('text-white', selected);
            tab.classList.toggle('text-zinc-500', !selected);
        });
    }

    activateAnalysisView('descriptive');

    document.querySelectorAll('[data-analysis-tab]').forEach(function (button) {
        button.addEventListener('click', function () {
            const active = button.dataset.analysisTab;
            activateAnalysisView(active);
            requestAnimationFrame(function () {
                window.dispatchEvent(new CustomEvent('analysis:tab-changed', { detail: { active } }));
                window.dispatchEvent(new Event('resize'));
            });
        });
    });
});
</script>
