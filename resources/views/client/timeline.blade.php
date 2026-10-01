@extends('layout.app')

@section('content')
<main class="min-h-screen bg-[#f5f5f3] px-4 py-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1800px] space-y-5">
        <header class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white px-5 py-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[.2em] text-zinc-400">Timeline Client</p>
                <h1 class="mt-1 text-xl font-semibold tracking-tight text-zinc-950">Timeline KPI {{ $client->nama }}</h1>
                <p class="mt-1 text-xs text-zinc-500">Rentang KPI ditampilkan dari tanggal mulai hingga tanggal deadline.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-[10px] font-semibold">
                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-amber-700">Belum selesai</span>
                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-emerald-700">Selesai</span>
            </div>
        </header>

        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Jadwal KPI</h2>
                    <p class="mt-0.5 text-[11px] text-zinc-500">Tanggal tersusun ke bawah. KPI yang bertabrakan ditempatkan berdampingan.</p>
                </div>
                <form id="timelineFilter" class="flex flex-wrap items-end gap-2">
                    <label class="block">
                        <span class="mb-1 block text-[9px] font-bold uppercase tracking-[.12em] text-zinc-400">Mulai</span>
                        <input id="timelineStart" type="date" value="{{ now()->startOfMonth()->toDateString() }}" class="h-9 rounded-xl border border-zinc-200 bg-white px-3 text-[11px] font-semibold text-zinc-700 outline-none focus:border-zinc-500">
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-[9px] font-bold uppercase tracking-[.12em] text-zinc-400">Selesai</span>
                        <input id="timelineEnd" type="date" value="{{ now()->endOfMonth()->toDateString() }}" class="h-9 rounded-xl border border-zinc-200 bg-white px-3 text-[11px] font-semibold text-zinc-700 outline-none focus:border-zinc-500">
                    </label>
                    <button type="submit" class="h-9 rounded-xl bg-zinc-950 px-4 text-[11px] font-semibold text-white hover:bg-zinc-800">Terapkan</button>
                </form>
            </div>
            <div id="clientTimelineViewport" class="max-h-[72vh] overflow-auto p-4 sm:p-5">
                <div class="grid min-h-[360px] place-items-center text-sm text-zinc-500">Menyiapkan timeline...</div>
            </div>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rawItems = @json($kpis);
    const viewport = document.getElementById('clientTimelineViewport');
    const dayMs = 86400000;
    const rowHeight = 66;
    const dateWidth = 118;
    const laneWidth = 260;
    const escapeHtml = value => String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
    const parseDate = value => {
        const parsed = new Date(`${String(value || '').slice(0, 10)}T00:00:00`);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    };
    const dateLabel = value => parseDate(value)?.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) || '-';

    const items = rawItems.map(item => {
        const start = parseDate(item.tanggal_mulai);
        const rawDeadline = parseDate(item.tanggal_deadline);
        if (!start || !rawDeadline) return null;
        return { ...item, start, deadline: rawDeadline < start ? start : rawDeadline };
    }).filter(Boolean).sort((a, b) => a.start - b.start || a.deadline - b.deadline);

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    function renderTimeline() {
        const rangeStart = parseDate(document.getElementById('timelineStart').value);
        const rangeEnd = parseDate(document.getElementById('timelineEnd').value);
        if (!rangeStart || !rangeEnd || rangeEnd < rangeStart) {
            viewport.innerHTML = '<div class="grid min-h-[360px] place-items-center text-sm font-semibold text-red-600">Rentang tanggal tidak valid.</div>';
            return;
        }

        const dayCount = Math.floor((rangeEnd - rangeStart) / dayMs) + 1;
        const visibleItems = items.filter(item => item.start <= rangeEnd && item.deadline >= rangeStart).map(item => ({
            ...item,
            visibleStart: item.start < rangeStart ? rangeStart : item.start,
            visibleDeadline: item.deadline > rangeEnd ? rangeEnd : item.deadline,
        }));
        const lanes = [];
        visibleItems.forEach(item => {
            let lane = lanes.findIndex(end => end < item.visibleStart.getTime());
            if (lane < 0) { lane = lanes.length; lanes.push(item.visibleDeadline.getTime()); }
            else lanes[lane] = item.visibleDeadline.getTime();
            item.lane = lane;
        });

        const dates = Array.from({ length: dayCount }, (_, index) => {
            const date = new Date(rangeStart.getTime() + index * dayMs);
            const isToday = date.getTime() === today.getTime();
            const weekend = date.getDay() === 0 || date.getDay() === 6;
            const dotClass = isToday ? 'right-[8px] h-[11px] w-[11px] bg-zinc-950' : `right-[9px] h-[9px] w-[9px] ${weekend ? 'bg-zinc-400' : 'bg-zinc-300'}`;
            return `<div style="height:${rowHeight}px;width:${dateWidth}px" class="relative flex shrink-0 items-center pr-7 text-zinc-700"><span class="absolute bottom-0 right-[13px] top-0 w-px ${isToday ? 'bg-zinc-400' : 'bg-zinc-200'}"></span><span class="absolute top-1/2 -translate-y-1/2 rounded-full border-2 border-white ${dotClass}"></span><div class="w-full text-right"><span class="block text-[9px] font-semibold uppercase text-zinc-400">${date.toLocaleDateString('id-ID', { weekday: 'long' })}${isToday ? ' · hari ini' : ''}</span><strong class="mt-0.5 block text-[11px] ${isToday ? 'font-bold text-zinc-950' : 'font-semibold text-zinc-700'}">${date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</strong></div></div>`;
        }).join('');

        const cards = visibleItems.map(item => {
            const top = Math.floor((item.visibleStart - rangeStart) / dayMs) * rowHeight + 6;
            const span = Math.floor((item.visibleDeadline - item.visibleStart) / dayMs) + 1;
            const height = Math.max(rowHeight - 12, span * rowHeight - 12);
            const left = item.lane * laneWidth + 8;
            const done = Number(item.status) === 1;
            const pics = (item.pics || []).map(pic => pic.name || pic.email).filter(Boolean).join(', ') || 'Belum ada PIC';
            const title = escapeHtml(item.nama_kpi || 'KPI');
            const tooltip = escapeHtml(`${item.nama_kpi || 'KPI'} | ${pics} | ${dateLabel(item.tanggal_mulai)} – ${dateLabel(item.tanggal_deadline)}`);
            return `<article title="${tooltip}" style="left:${left}px;top:${top}px;width:${laneWidth - 16}px;height:${height}px" class="absolute overflow-hidden rounded-xl border px-3 py-2.5 shadow-sm ${done ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-amber-200 bg-amber-50 text-amber-950'}"><div class="flex items-start justify-between gap-2"><p class="truncate text-xs font-bold">${title}</p><span class="shrink-0 rounded-full bg-white/70 px-1.5 py-0.5 text-[8px] font-bold">${escapeHtml(item.point ?? 0)} pt</span></div><p class="mt-1 truncate text-[9px] opacity-75">${escapeHtml(pics)}</p><p class="mt-0.5 truncate text-[9px] font-semibold opacity-70">${dateLabel(item.tanggal_mulai)} – ${dateLabel(item.tanggal_deadline)}</p></article>`;
        }).join('');

        const emptyMessage = visibleItems.length ? '' : '<div class="absolute inset-0 grid place-items-center px-6 text-center text-xs text-zinc-400"><span>Belum ada KPI pada rentang tanggal ini.</span></div>';
        const bodyHeight = dayCount * rowHeight;
        const contentWidth = Math.max(760, Math.max(1, lanes.length) * laneWidth);
        const boardWidth = dateWidth + contentWidth;
        viewport.innerHTML = `<div class="flex overflow-hidden rounded-xl border border-zinc-200 bg-white" style="width:${boardWidth}px;min-height:${bodyHeight}px"><div class="sticky left-0 z-20 shrink-0 bg-white" style="width:${dateWidth}px">${dates}</div><div class="relative" style="width:${contentWidth}px;height:${bodyHeight}px;background:repeating-linear-gradient(to bottom,transparent 0,transparent ${rowHeight - 1}px,#e4e4e7 ${rowHeight - 1}px,#e4e4e7 ${rowHeight}px)">${cards}${emptyMessage}</div></div>`;
        viewport.scrollTo({ top: 0, left: 0 });
    }

    document.getElementById('timelineFilter')?.addEventListener('submit', function (event) {
        event.preventDefault();
        renderTimeline();
    });

    renderTimeline();
});
</script>
@endsection
