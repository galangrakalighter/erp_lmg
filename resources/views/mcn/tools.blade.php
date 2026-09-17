@extends('layout.app')
@section('content')
@include('mcn.partials.theme')
<main class="mcn-workspace"><div class="mx-auto max-w-[1600px]">
@include('mcn.partials.header', ['heading' => 'Tool Integrasi', 'description' => 'Jalankan sinkronisasi data MCN dari Google Sheet melalui satu workspace yang terstruktur.'])
@include('mcn.tools.form')
</div></main>
@endsection
@push('script')
@include('mcn.tools.script')
@endpush
