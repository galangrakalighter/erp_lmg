<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingAssetCrudService;
use App\Services\MarketingDocumentUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class MarketingAssetCrudController extends Controller
{
    public function __construct(private readonly MarketingAssetCrudService $service, private readonly MarketingDocumentUploadService $uploadService) {}

    public function uploadDocument(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', 'min:1'],
            'document_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required_without:document_id', 'nullable', 'string', 'min:2', 'max:255'],
            'file_name' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', 'file', 'max:'.config('marketing-mcp.document_upload_max_kb', 20480)],
        ]);
        $result = $this->uploadService->upload($request->user(), (int) $data['client_id'], isset($data['document_id']) ? (int) $data['document_id'] : null, $request->file('file'), $data);
        return response()->json(['data' => $result, 'meta' => ['operation' => 'upload_document_file']], 201);
    }

    public function confirmation(Request $request): JsonResponse
    {
        $data = $request->validate(['action' => ['required', Rule::in(['create', 'update', 'archive'])], 'client_id' => ['required', 'integer', 'min:1'], 'record_id' => ['sometimes', 'integer', 'min:0'], 'payload' => ['required', 'array']]);
        return response()->json(['data' => $this->service->confirmation($request->user(), $data['action'], (int) $data['client_id'], (int) ($data['record_id'] ?? 0), $data['payload'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(true));
        return response()->json(['data' => $this->service->create($request->user(), $data), 'meta' => ['operation' => 'create']], 201);
    }

    public function update(Request $request, int $asset): JsonResponse
    {
        $data = $request->validate($this->rules(false));
        return response()->json(['data' => $this->service->update($request->user(), $asset, $data), 'meta' => ['operation' => 'update']]);
    }

    public function archive(Request $request, int $asset): JsonResponse
    {
        $data = $request->validate(['client_id' => ['required', 'integer', 'min:1'], 'confirmation_token' => ['required', 'string', 'size:64'], 'reason' => ['required', 'string', 'min:5', 'max:500']]);
        return response()->json(['data' => $this->service->archive($request->user(), $asset, $data), 'meta' => ['operation' => 'archive']]);
    }

    private function rules(bool $create): array
    {
        $required = $create ? 'required' : 'sometimes';
        return [
            'client_id' => ['required', 'integer', 'min:1'], 'confirmation_token' => ['required', 'string', 'size:64'],
            'project_id' => ['sometimes', 'nullable', 'integer', 'min:1'], 'campaign_id' => ['sometimes', 'nullable', 'integer', 'min:1'], 'content_plan_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'name' => [$required, 'string', 'min:2', 'max:255'], 'asset_type' => [$required, Rule::in(['asset', 'document'])],
            'mime_type' => ['sometimes', 'nullable', 'string', 'max:100', 'regex:/^[a-z0-9.+-]+\/[a-z0-9.+-]+$/i'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'], 'version_label' => ['sometimes', 'nullable', 'string', 'max:100'], 'version_note' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
