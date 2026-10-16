<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingTaskCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class MarketingTaskCrudController extends Controller
{
    public function __construct(private readonly MarketingTaskCrudService $service) {}

    public function confirmation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['create', 'update', 'archive', 'change_pic', 'change_deadline', 'change_status', 'complete'])],
            'client_id' => ['required', 'integer', 'min:1'], 'record_id' => ['sometimes', 'integer', 'min:0'], 'payload' => ['required', 'array'],
        ]);
        return response()->json(['data' => $this->service->confirmation($request->user(), $data['action'], (int) $data['client_id'], (int) ($data['record_id'] ?? 0), $data['payload'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->baseRules(true));
        return response()->json(['data' => $this->service->create($request->user(), $data), 'meta' => ['operation' => 'create']], 201);
    }

    public function update(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->baseRules(false));
        return response()->json(['data' => $this->service->update($request->user(), $task, $data), 'meta' => ['operation' => 'update']]);
    }

    public function archive(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->mutationRules(['reason' => ['required', 'string', 'min:5', 'max:500']]));
        return response()->json(['data' => $this->service->archive($request->user(), $task, $data), 'meta' => ['operation' => 'archive']]);
    }

    public function changePic(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->mutationRules(['pic_id' => ['required', 'integer', 'min:1']]));
        return response()->json(['data' => $this->service->changePic($request->user(), $task, $data), 'meta' => ['operation' => 'change_pic']]);
    }

    public function changeDeadline(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->mutationRules(['deadline' => ['required', 'date_format:Y-m-d']]));
        return response()->json(['data' => $this->service->changeDeadline($request->user(), $task, $data), 'meta' => ['operation' => 'change_deadline']]);
    }

    public function changeStatus(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->mutationRules(['status' => ['required', 'integer', Rule::in([0, 1, 2, 3, 7, 8, 9])]]));
        return response()->json(['data' => $this->service->changeStatus($request->user(), $task, $data), 'meta' => ['operation' => 'change_status']]);
    }

    public function complete(Request $request, int $task): JsonResponse
    {
        $data = $request->validate($this->mutationRules(['note' => ['sometimes', 'nullable', 'string', 'max:2000']]));
        return response()->json(['data' => $this->service->complete($request->user(), $task, $data), 'meta' => ['operation' => 'complete']]);
    }

    private function baseRules(bool $create): array
    {
        $required = $create ? 'required' : 'sometimes';
        return $this->mutationRules([
            'name' => [$required, 'string', 'min:2', 'max:255'],
            'pic_id' => [$create ? 'required' : 'prohibited', 'integer', 'min:1'],
            'priority' => [$required, 'integer', 'between:1,3'],
            'estimated_minutes' => [$required, 'integer', 'min:1', 'max:1440'],
            'content_plan_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'start_date' => [$required, 'date_format:Y-m-d'],
            'deadline' => [$create ? 'required' : 'prohibited', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'start_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'note' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);
    }

    private function mutationRules(array $extra): array
    {
        return ['client_id' => ['required', 'integer', 'min:1'], 'confirmation_token' => ['required', 'string', 'size:64'], ...$extra];
    }
}
