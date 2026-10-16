<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BrandGuidelineCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BrandGuidelineCrudController extends Controller
{
    public function __construct(private readonly BrandGuidelineCrudService $service) {}
    public function confirmation(Request $request): JsonResponse { $d = $request->validate(['action' => ['required','in:create,update,archive'], 'client_id' => ['required','integer','min:1'], 'record_id' => ['sometimes','integer','min:0'], 'payload' => ['required','array']]); return response()->json(['data' => $this->service->confirmation($request->user(), $d['action'], (int) $d['client_id'], (int) ($d['record_id'] ?? 0), $d['payload'])]); }
    public function store(Request $request): JsonResponse { $d = $request->validate($this->rules(true)); return response()->json(['data' => $this->service->create($request->user(), $d), 'meta' => ['operation' => 'create']], 201); }
    public function update(Request $request, int $brandGuideline): JsonResponse { $d = $request->validate($this->rules(false)); return response()->json(['data' => $this->service->update($request->user(), $brandGuideline, $d), 'meta' => ['operation' => 'update']]); }
    public function archive(Request $request, int $brandGuideline): JsonResponse { $d = $request->validate(['confirmation_token' => ['required','string','size:64'], 'reason' => ['required','string','min:5','max:500']]); return response()->json(['data' => $this->service->archive($request->user(), $brandGuideline, $d), 'meta' => ['operation' => 'archive']]); }
    private function rules(bool $create): array { return ['client_id' => ['required','integer','min:1'], 'confirmation_token' => ['required','string','size:64'], 'identify' => [$create ? 'required' : 'sometimes','string','max:1000'], 'platform' => [$create ? 'required' : 'sometimes','string','max:50']]; }
}
