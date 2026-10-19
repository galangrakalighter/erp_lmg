@extends('layout.app')
@section('content')
@php
    $platform = 'Facebook';
    $clientName = preg_replace('/^Analisis Konten\s+/i', '', $title ?? 'Client');
    $followerMetric = $jumlah_follower ?? 0;
    $organicItems = collect($dataBaru ?? [])->map(fn ($item) => [
        'judul' => $item['judul'] ?? '', 'link' => $item['link_content'] ?? '#', 'jenis' => $item['jenis_konten'] ?? '-',
        'tanggal_posting' => $item['tanggal_posting'] ?? '-', 'reach' => (int) ($item['reach'] ?? 0),
        'impressions' => (int) ($item['impresi'] ?? 0), 'engagement' => (int) ($item['engagement'] ?? 0),
        'saved' => 0, 'shares' => (int) ($item['shares'] ?? 0),
        'likes' => (int) ($item['reaction'] ?? 0), 'comments' => (int) ($item['comments'] ?? 0),
    ])->values();
@endphp
@include('analisis.partials.meta-dashboard')
@endsection
