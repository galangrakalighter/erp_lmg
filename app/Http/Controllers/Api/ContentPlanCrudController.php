<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ContentPlanCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ContentPlanCrudController extends Controller
{
    public function __construct(private readonly ContentPlanCrudService $service) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));
        return response()->json(['data' => $this->service->create($request->user(), $data), 'meta' => ['operation' => 'create']], 201);
    }

    public function update(Request $request, int $contentPlan): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        return response()->json(['data' => $this->service->update($request->user(), $contentPlan, $data), 'meta' => ['operation' => 'update']]);
    }

    public function archive(Request $request, int $contentPlan): JsonResponse
    {
        $data = $request->validate(['confirmation_token' => ['required', 'string', 'size:64'], 'reason' => ['required', 'string', 'min:5', 'max:500']]);
        return response()->json(['data' => $this->service->archive($request->user(), $contentPlan, $data), 'meta' => ['operation' => 'archive']]);
    }

    public function confirmation(Request $request): JsonResponse
    {
        $data = $request->validate(['action' => ['required', 'in:create,update,archive'], 'client_id' => ['required', 'integer', 'min:1'], 'object_id' => ['sometimes', 'integer', 'min:0'], 'payload' => ['required', 'array']]);
        return response()->json(['data' => $this->service->confirmation($request->user(), $data['action'], (int) $data['client_id'], (int) ($data['object_id'] ?? 0), $data['payload'])]);
    }

    private function rules(bool $create): array
    {
        $rules = [
            'client_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'confirmation_token' => ['required', 'string', 'size:64'],
            'judul' => [$create ? 'required' : 'sometimes', 'string', 'max:255'], 'inspirasi' => ['nullable', 'string', 'max:255'],
            'type_produk_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'strategy_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'pilar_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'hooks_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'body_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'cta_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'],
            'platform' => ['nullable', 'string', 'max:50'], 'status_id' => [$create ? 'required' : 'sometimes', 'integer', 'min:1'], 'pic' => ['prohibited'], 'priority' => [$create ? 'required' : 'sometimes', 'integer', 'min:0', 'max:100'], 'menit' => [$create ? 'required' : 'sometimes', 'integer', 'min:0', 'max:10000'],
            'durasi' => ['nullable', 'string', 'max:100'], 'background' => ['nullable', 'string', 'max:255'], 'komposisi' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:10000'], 'link' => ['nullable', 'url', 'max:2048'], 'tanggal_posting' => ['nullable', 'date'], 'tanggal_deadline' => ['nullable', 'date'], 'tanggal_mulai' => ['nullable', 'date'],
        ];
        return $rules;
    }
}
