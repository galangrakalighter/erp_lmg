@extends('layout.app')
@section('content')
@php
    $platform = 'TikTok';
    $clientName = preg_replace('/^Analisis Konten\s+/i', '', $title ?? 'Client');
    $followerMetric = (int) data_get($tiktokProfile ?? [], 'follower_count', 0);
    $organicItems = collect($posts ?? [])->map(fn ($item) => [
        'judul' => $item['judul'] ?? '', 'link' => $item['link'] ?? '#', 'jenis' => $item['jenis'] ?? 'VIDEO',
        'tanggal_posting' => $item['tanggal_posting'] ?? '-', 'reach' => (int) ($item['views'] ?? 0),
        'views' => (int) ($item['views'] ?? 0), 'impressions' => (int) ($item['views'] ?? 0),
        'accounts_reached' => (int) ($item['views'] ?? 0), 'engagement' => (int) ($item['engagement'] ?? 0),
        'saved' => 0, 'shares' => (int) ($item['shares'] ?? 0), 'likes' => (int) ($item['likes'] ?? 0),
        'comments' => (int) ($item['comments'] ?? 0), 'video_views' => (int) ($item['views'] ?? 0),
        'duration' => (int) ($item['duration'] ?? 0),
    ])->values();
@endphp
@if(filled($tiktokError ?? null))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">{{ $tiktokError }}</div>
@endif
@include('analisis.partials.meta-dashboard')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.meta-kpi small').forEach(function (label) {
        if (label.textContent.trim() === 'Data realtime Meta') label.textContent = 'Data realtime TikTok';
    });
});
</script>
@endsection
