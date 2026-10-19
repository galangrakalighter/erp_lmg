<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingApprovalService;
use App\Support\MarketingApprovalRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class MarketingApprovalController extends Controller
{
    public function __construct(private readonly MarketingApprovalService $service) {}

    public function confirmation(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));

        return response()->json(['data' => $this->service->confirmation($request->user(), $data)]);
    }

    public function submit(Request $request): JsonResponse
    {
        return $this->mutate($request, 'submit_for_approval');
    }

    public function approve(Request $request): JsonResponse
    {
        return $this->mutate($request, 'approve');
    }

    public function reject(Request $request): JsonResponse
    {
        return $this->mutate($request, 'reject');
    }

    public function requestRevision(Request $request): JsonResponse
    {
        return $this->mutate($request, 'request_revision');
    }

    private function mutate(Request $request, string $action): JsonResponse
    {
        $rules = $this->rules(false);
        if (in_array($action, ['reject', 'request_revision'], true)) {
            $rules['note'] = ['required', 'string', 'min:3', 'max:2000'];
        }
        $data = $request->validate($rules) + ['action' => $action];

        return response()->json(['data' => $this->service->append($request->user(), $action, $data), 'meta' => ['operation' => $action]], 201);
    }

    private function rules(bool $confirmation): array
    {
        return [
            ...($confirmation ? ['action' => ['required', Rule::in(['submit_for_approval', 'approve', 'reject', 'request_revision'])]] : []),
            'client_id' => ['required', 'integer', 'min:1'],
            'record_type' => ['required', Rule::in(MarketingApprovalRecords::types())],
            'record_id' => ['required', 'integer', 'min:1'],
            'note' => ['sometimes', 'nullable', 'string', 'max:2000'],
            ...($confirmation ? [] : ['confirmation_token' => ['required', 'string', 'size:64']]),
        ];
    }
}
