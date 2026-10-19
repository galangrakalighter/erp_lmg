@extends('layout.app')
@section('content')
@php
    $platform = 'Threads';
    $clientName = preg_replace('/^Analisis Konten\s+/i', '', $title ?? 'Client');
    $followerMetric = $threadsFollowers ?? 0;
    $organicItems = collect($posts ?? [])->map(fn ($item) => [
        'judul' => $item['judul'] ?? '', 'link' => $item['link'] ?? '#', 'jenis' => $item['jenis'] ?? 'THREADS',
        'tanggal_posting' => $item['tanggal_posting'] ?? '-', 'reach' => (int) ($item['views'] ?? 0),
        'views' => (int) ($item['views'] ?? 0), 'impressions' => (int) ($item['views'] ?? 0),
        'accounts_reached' => (int) ($item['views'] ?? 0),
        'engagement' => (int) (($item['likes'] ?? 0) + ($item['replies'] ?? 0) + ($item['reposts'] ?? 0) + ($item['quotes'] ?? 0) + ($item['shares'] ?? 0)),
        'saved' => 0, 'shares' => (int) ($item['shares'] ?? 0), 'likes' => (int) ($item['likes'] ?? 0),
        'comments' => (int) ($item['replies'] ?? 0), 'replies' => (int) ($item['replies'] ?? 0),
        'reposts' => (int) ($item['reposts'] ?? 0), 'quotes' => (int) ($item['quotes'] ?? 0),
    ])->values();
@endphp
@if(filled($threadsError ?? null))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">{{ $threadsError }}</div>
@endif
@include('analisis.partials.meta-dashboard')
@endsection
