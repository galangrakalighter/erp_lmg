<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CampaignCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class CampaignCrudController extends Controller
{
    public function __construct(private readonly CampaignCrudService $service) {}

    public function confirmation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['create', 'update', 'archive'])],
            'client_id' => ['required', 'integer', 'min:1'],
            'record_id' => ['sometimes', 'integer', 'min:0'],
            'payload' => ['required', 'array'],
        ]);

        return response()->json(['data' => $this->service->confirmation($request->user(), $data['action'], (int) $data['client_id'], (int) ($data['record_id'] ?? 0), $data['payload'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));

        return response()->json(['data' => $this->service->create($request->user(), $data), 'meta' => ['operation' => 'create']], 201);
    }

    public function update(Request $request, int $campaign): JsonResponse
    {
        $data = $request->validate($this->rules(false));

        return response()->json(['data' => $this->service->update($request->user(), $campaign, $data), 'meta' => ['operation' => 'update']]);
    }

    public function archive(Request $request, int $campaign): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', 'min:1'],
            'confirmation_token' => ['required', 'string', 'size:64'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        return response()->json(['data' => $this->service->archive($request->user(), $campaign, $data), 'meta' => ['operation' => 'archive']]);
    }

    private function rules(bool $create): array
    {
        $required = $create ? 'required' : 'sometimes';

        return [
            'client_id' => ['required', 'integer', 'min:1'],
            'confirmation_token' => ['required', 'string', 'size:64'],
            'owner_user_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'name' => [$required, 'string', 'min:2', 'max:200'],
            'status' => [$required, Rule::in(['draft', 'planned', 'active', 'paused', 'completed', 'cancelled'])],
            'platform' => ['sometimes', 'nullable', Rule::in(['instagram', 'facebook', 'tiktok', 'youtube', 'linkedin', 'x', 'website', 'other'])],
            'source' => [$required, Rule::in(['organic', 'paid'])],
            'start_date' => [$required, 'date_format:Y-m-d'],
            'end_date' => [$required, 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'objective' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'budget' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999999999.99'],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'target' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
