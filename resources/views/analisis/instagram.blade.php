@extends('layout.app')
@section('content')
@php
    $platform = 'Instagram';
    $clientName = preg_replace('/^Analisis Konten\s+/i', '', $title ?? 'Client');
    $followerMetric = $insightsRaw[1]['Followers_sebulan'] ?? 0;
    $organicItems = collect($posts ?? [])->map(fn ($item) => [
        'judul' => $item['judul'] ?? '', 'link' => $item['link'] ?? '#', 'jenis' => $item['jenis'] ?? '-',
        'tanggal_posting' => $item['tanggal_posting'] ?? '-', 'reach' => (int) ($item['reach'] ?? 0),
        'impressions' => (int) ($item['impressions'] ?? 0), 'engagement' => (int) ($item['engagement'] ?? 0),
        'saved' => (int) ($item['saved'] ?? 0), 'shares' => (int) ($item['shares'] ?? 0),
    ])->values();
@endphp
@include('analisis.partials.meta-dashboard')
@endsection
