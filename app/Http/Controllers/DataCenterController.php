<?php

namespace App\Http\Controllers;

use App\Models\DataCenterAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DataCenterController extends Controller
{
    public const CATEGORIES = [
        'ERP',
        'Instagram',
        'Facebook',
        'TikTok',
        'YouTube',
        'Threads',
        'Email',
        'Marketplace',
        'Hosting & Domain',
        'Lainnya',
    ];

    public function index(Request $request): View
    {
        $request->validate([
            'category' => ['nullable', Rule::in(self::CATEGORIES)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $category = trim((string) $request->query('category'));
        $search = Str::lower(trim((string) $request->query('search')));

        $accounts = DataCenterAccount::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->latest('updated_at')
            ->get()
            ->when($search !== '', function ($items) use ($search) {
                return $items->filter(function (DataCenterAccount $account) use ($search) {
                    return Str::contains(Str::lower(implode(' ', [
                        $account->account_name,
                        $account->provider,
                        $account->username,
                        $account->email,
                        $account->phone,
                        $account->url,
                        $account->notes,
                    ])), $search);
                })->values();
            });

        $categoryCounts = DataCenterAccount::query()
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('data_center.index', [
            'accounts' => $accounts,
            'categories' => self::CATEGORIES,
            'categoryCounts' => $categoryCounts,
            'selectedCategory' => $category,
            'search' => (string) $request->query('search', ''),
            'totalAccounts' => DataCenterAccount::count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request, true);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        DataCenterAccount::create($validated);

        return back()->with('success', 'Informasi akun berhasil disimpan.');
    }

    public function update(Request $request, DataCenterAccount $dataCenter): RedirectResponse
    {
        $validated = $this->validated($request, false);
        if (! $request->filled('password')) {
            unset($validated['password']);
        }
        $validated['updated_by'] = $request->user()->id;

        $dataCenter->update($validated);

        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }

    public function destroy(DataCenterAccount $dataCenter): RedirectResponse
    {
        $dataCenter->delete();

        return back()->with('success', 'Informasi akun berhasil dihapus.');
    }

    public function reveal(Request $request, DataCenterAccount $dataCenter, ActivityLogService $activityLog): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.required' => 'Password akun ERP wajib diisi.',
            'current_password.current_password' => 'Password akun ERP tidak sesuai.',
        ]);

        $activityLog->record([
            'action' => 'VIEW_SECRET',
            'module' => 'data_center_accounts',
            'description' => 'Membuka password akun di Data Center.',
            'target_id' => $dataCenter->id,
            'target_name' => $dataCenter->account_name,
            'target_type' => 'data_center_account',
        ]);

        return response()->json([
            'password' => $dataCenter->password,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'provider' => ['nullable', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'max:2000'],
            'recovery_email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'url' => ['nullable', 'url:http,https', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
