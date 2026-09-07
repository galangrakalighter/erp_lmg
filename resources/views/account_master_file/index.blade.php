@extends('layout.app')

@section('content')
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
    <div>
        <h1 class="h4 text-gray-800 font-weight-bold mb-1"><i class="fas fa-file-alt text-primary mr-2"></i>File Master</h1>
        <p class="small text-muted mb-0">File Anda dan file bawahan yang berada dalam jalur pelaporan Anda.</p>
    </div>
    <form method="GET" class="form-inline mt-3 mt-lg-0">
        <select name="division_id" class="form-control form-control-sm mr-2">
            <option value="">Semua Divisi</option>
            @foreach($divisions as $division)<option value="{{ $division->id }}" @selected((int) request('division_id') === (int) $division->id)>{{ $division->name }}</option>@endforeach
        </select>
        <select name="role_id" class="form-control form-control-sm mr-2">
            <option value="">Semua Jabatan</option>
            @foreach($roles as $role)<option value="{{ $role->id }}" @selected((int) request('role_id') === (int) $role->id)>{{ $role->role_name }} · {{ $role->division->name ?? '-' }}</option>@endforeach
        </select>
        <button class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Filter</button>
        @if(request()->hasAny(['division_id','role_id']))<a href="{{ route('file-master.index') }}" class="btn btn-sm btn-light border ml-2">Reset</a>@endif
    </form>
</div>

<div class="row">
@forelse($users as $account)
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('file-master.show', $account) }}" class="card shadow-sm border-0 h-100 text-decoration-none">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mr-3" style="width:44px;height:44px"><i class="fas fa-user"></i></div>
                    <div class="overflow-hidden"><h6 class="font-weight-bold text-gray-800 mb-0 text-truncate">{{ $account->name }}</h6><small class="text-muted">{{ $account->email }}</small></div>
                </div>
                @foreach($account->userDivisions as $assignment)
                    <span class="badge badge-light border mb-1">{{ $assignment->division->name ?? '-' }} · {{ $assignment->role->role_name ?? '-' }}</span>
                @endforeach
                <div class="small text-primary mt-3"><i class="fas fa-file mr-1"></i>{{ $account->account_master_files_count }} file</div>
            </div>
        </a>
    </div>
@empty
    <div class="col-12"><div class="card shadow-sm border-0"><div class="card-body text-center text-muted py-5">Tidak ada akun yang sesuai atau dapat Anda akses.</div></div></div>
@endforelse
</div>
@endsection
