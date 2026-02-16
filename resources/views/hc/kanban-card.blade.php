<div class="card shadow-sm border-left-{{ $task->priority == 3 ? 'danger' : ($task->priority == 2 ? 'warning' : 'info') }} task-entry mb-2" 
     id="card-task-{{ $task->id }}"
     style="height: {{ $task->menit }}px; min-height: 50px; z-index: 10; margin-top: 5px;">
    
    <div class="card-body p-2 position-relative">
        <button onclick="removeCard({{ $task->id }}, this)" class="btn btn-xs text-danger position-absolute" style="top: 2px; right: 2px;">
            <i class="fas fa-times"></i>
        </button>
        
        <strong style="font-size: 10px;" class="d-block pr-3 text-truncate">{{ $task->nama_task }}</strong>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <small class="text-muted font-weight-bold" style="font-size: 9px;">{{ $task->menit }} Menit</small>
            <div class="btn-group">
                <i class="fas fa-play fa-xs text-success mr-1 cursor-pointer" onclick="alert('Start #{{ $task->id }}')"></i>
                <i class="fas fa-pause fa-xs text-warning cursor-pointer" onclick="alert('Revisi #{{ $task->id }}')"></i>
            </div>
        </div>
    </div>
</div>