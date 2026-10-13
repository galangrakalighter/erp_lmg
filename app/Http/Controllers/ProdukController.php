<?php

namespace App\Http\Controllers;

use App\Models\ProdukClient;
use App\Models\OAuthConnection;
use App\Models\KategoriJasa;
use App\Models\UserClient;
use App\Models\userDivision as UserDivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Task;
use App\Models\FilePdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\MetaConnectionSyncService;

class ProdukController extends Controller
{
    private const DEFAULT_MARKETING_PLATFORMS = [
        'Facebook', 'Instagram', 'TikTok', 'Threads', 'Google', 'YouTube',
    ];
    public function index(Request $request, $nama)
    {
        $divisiId = ($nama == "LGTR") ? 1 : 2;

        $divisi = User::find(Auth::user()->id)->userDivisions;
        $RoleSekarang = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name') : "Super Admin";
        if ($request->ajax()) {
            $data = ProdukClient::with(['teams.userDivisions.role'])
                ->external()
                ->join('client_division', 'client_division.client_id', '=', 'produk_client.id')
                ->leftJoin('kategori_jasa', 'kategori_jasa.id', '=', 'produk_client.kategori_jasa_id')
                ->select('produk_client.*', 'kategori_jasa.category', 'client_division.status as division_status')
                ->where('client_division.division_id', $divisiId)
                // Keanggotaan client di CMS mengikuti client_division, sama seperti sidebar.
                // PIC boleh belum diatur; client tetap harus terlihat untuk divisinya.
                ->get()
                ->map(function($client) use ($divisiId) {
                    $teams = $client->teams->filter(fn ($team) => ! $team->is_locked
                        && $team->userDivisions->contains(
                            fn ($membership) => (int) $membership->division_id === (int) $divisiId
                        ));
                    
                    return [
                        'id'       => $client->id,
                        'nama'     => $client->nama,
                        'category' => $client->category,
                        'status'   => $client->division_status,
                        // Filter berdasarkan ID Role yang benar
                        'buh'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 1)->pluck('email'),
                        'ads'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 4)->pluck('email'),
                        'dm'      => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->pluck('email'),
                        'ccm'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 5)->pluck('email'), 
                        'cc'       => $teams->filter(fn($u) => $u->userDivisions->first()?->role_id == 9)->pluck('email'), 
                        'team_ids' => $teams->pluck('id')->toArray(),
                    ];
                });
            
            return response()->json(['data' => $data]);
        }

        // Ambil semua user yang masuk di divisi ini (Eager Load user_divisions)
        $usersInDivisi = User::unlocked()
            ->with('userDivisions')
            ->whereHas('userDivisions', fn ($query) => $query->where('division_id', $divisiId))
            ->get();
        
        $kategori = KategoriJasa::where('id', $divisiId)->get();
        // dd($usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 1));
        // dd($kategori);
        // dd($usersInDivisi->filter(fn($u) => $u->userDivisions->role_id == 1));
        // $divisions = $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3)->flatMap->userDivisions;
        return view('cms.produk.index', [
            'title' => "Manajemen Client",
            'buh'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 1),
            'dm'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 3),
            'ads'   => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 4),
            'ccm'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 5),
            'cc'    => $usersInDivisi->filter(fn($u) => $u->userDivisions->first()?->role_id == 9),
            'nama'  => $nama,
            'role'  => $RoleSekarang,
            'divisi' => $divisiId,
            'kategori' => $kategori
        ]);
    }


    public function getDetail($id, $id_user, $folderName)
    {
        try {
            $produk = ProdukClient::with('oauthConnections')->findOrFail($id);
            $divisionId = match ($folderName) {
                'cms_LGTR' => 1,
                'cms_BD' => 2,
                default => null,
            };
            $currentTeamIds = $produk->teams()
                ->where('users.is_locked', false)
                ->when($divisionId, fn ($query) => $query->whereHas(
                    'userDivisions', fn ($memberships) => $memberships->where('division_id', $divisionId)
                ))
                ->pluck('users.id')
                ->toArray();

            // Mengambil Role Sekarang sesuai logika Anda
            $user = User::find($id_user);
            $divisi = $user->userDivisions;

            if ($user->isAdmin) {
                $kategori = KategoriJasa::where('id', 1)->get();
                if($folderName == "cms_BD"){
                    $kategori = KategoriJasa::where('id', 2)->get();
                }
            } else {
                $kategori = KategoriJasa::where('id', $divisi[0]->division_id)->get();
                if($folderName == "cms_LGTR"){
                    $kategori = KategoriJasa::where('id', 1)->get();
                }
            }
            
            // Cek apakah user punya divisi untuk menghindari error $divisi[0]
            $roleName = "Guest"; 
            if ($user->isAdmin) {
                $roleName = "Super Admin";
            } elseif ($divisi->isNotEmpty()) {
                $roleName = DB::table('roles')->where('id', $divisi[0]->role_id)->value('role_name');
            }

            // Tentukan hak akses (Bisa tambah role lain yang dianggap 'setara admin' di sini)
            $isUserAdmin = $user->isAdmin == true;

            $usersForRole = function (string $roleName) use ($divisionId) {
                return User::unlocked()
                    ->with('userDivisions')
                    ->whereHas('userDivisions', function ($memberships) use ($divisionId, $roleName) {
                        $memberships->where('division_id', $divisionId)
                            ->whereHas('role', fn ($roles) => $roles->where('role_name', $roleName));
                    })
                    ->get(['id', 'name']);
            };
            $options = [
                'buh' => $usersForRole('Busines Unit Head'),
                'dm' => $usersForRole('Digital Marketing')->map(function ($u) use ($divisionId) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->firstWhere('division_id', $divisionId)?->parent_id ?? 0];
                }),
                'ads' => $usersForRole('Advertising')->map(function ($u) use ($divisionId) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->firstWhere('division_id', $divisionId)?->parent_id ?? 0];
                }),
                'ccm' => $usersForRole('Content Creator Manager')->map(function ($u) use ($divisionId) {
                    return ['id' => $u->id, 'name' => $u->name, 'parent' => $u->userDivisions->firstWhere('division_id', $divisionId)?->parent_id ?? 0];
                }),
            ];
            $divisionStatus = $divisionId
                ? DB::table('client_division')->where('client_id', $produk->id)->where('division_id', $divisionId)->value('status')
                : $produk->status;
            $oauthConnections = $produk->oauthConnections;
            $produk->unsetRelation('oauthConnections');

            return response()->json([
                'status'        => 'success',
                'produk'        => $produk,
                'division_id'   => $divisionId,
                'division_status' => $divisionStatus ?? 1,
                'current_teams' => $currentTeamIds,
                'options'       => $options,
                'is_admin'      => $isUserAdmin,
                'kategori'      => $kategori,
                'my_role'       => $roleName,
                'oauth_connections' => $oauthConnections->mapWithKeys(function ($connection) {
                    $assets = $connection->assets ?? [];
                    $pages = collect($assets['pages'] ?? []);
                    $selectedPageId = $connection->selected_assets['page_id'] ?? null;
                    $selectedPage = $pages->firstWhere('id', $selectedPageId) ?? $pages->first();
                    $instagramAccounts = $pages
                        ->filter(fn ($page) => filled(data_get($page, 'instagram_business_account.id')))
                        ->map(fn ($page) => [
                            'id' => data_get($page, 'instagram_business_account.id'),
                            'username' => data_get($page, 'instagram_business_account.username'),
                            'name' => data_get($page, 'instagram_business_account.name'),
                            'page_id' => $page['id'] ?? null,
                            'page_name' => $page['name'] ?? 'Facebook Page',
                        ])->values();
                    return [$connection->provider => [
                        'connected' => filled($connection->access_token),
                        'ready' => filled($connection->access_token) && (bool) $selectedPage,
                        'has_instagram' => $instagramAccounts->isNotEmpty(),
                        'provider_user_name' => $connection->provider_user_name,
                        'expires_at' => $connection->expires_at?->toIso8601String(),
                        'scopes' => $connection->scopes ?? [],
                        'selected_assets' => $connection->selected_assets ?? [],
                        'pages' => $pages->map(fn ($page) => [
                            'id' => $page['id'] ?? null,
                            'name' => $page['name'] ?? 'Facebook Page',
                            'instagram' => $page['instagram_business_account'] ?? null,
                        ])->values(),
                        'instagram_accounts' => $instagramAccounts,
                        'ad_accounts' => collect($assets['ad_accounts'] ?? [])->map(fn ($account) => [
                            'id' => $account['id'] ?? null,
                            'name' => $account['name'] ?? ($account['account_id'] ?? 'Akun iklan'),
                            'account_id' => $account['account_id'] ?? null,
                            'page_ids' => $account['page_ids'] ?? [],
                        ])->values(),
                    ]];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request, MetaConnectionSyncService $metaConnectionSync)
    {
        $request->validate([
            'client' => ['required', 'string', 'max:255'],
            'divisi' => ['required', 'integer', 'exists:divisions,id'],
            'status' => ['required', 'integer'],
            'oauth_draft_key' => ['nullable', 'uuid'],
        ]);

        $accessTokenArray = $this->withDefaultMarketingPlatforms([
            'platform' => (array) $request->input('platform', []),
            'access'   => (array) $request->input('access', []),
        ]);

        try {
            DB::beginTransaction();

            // Nama client bersifat unik. Jika sudah ada, pakai produk yang sama
            // dan tambahkan divisi serta tim baru ke relasinya.
            $produk = $this->findClientByName($request->client);
            $isExistingClient = $produk !== null;

            if (!$produk) {
                $produk = ProdukClient::create([
                    'kategori_jasa_id' => $request->filled('kategori_jasa_id') ? (int) $request->kategori_jasa_id : null,
                    'nama'              => trim($request->client),
                    'status'            => (int) $request->status,
                    'access_token'      => $accessTokenArray,
                ]);
            } else {
                $mergedTokens = $this->withDefaultMarketingPlatforms($this->mergeAccessTokens(
                    $produk->access_token,
                    (array) $request->input('platform', []),
                    (array) $request->input('access', [])
                ));

                $produk->update(['access_token' => $mergedTokens]);
            }

            // 2. Kumpulkan semua ID user dari form
            $allUserIds = collect()
                ->merge(collect($request->input('buh_ids', [])))
                ->merge(collect($request->input('dm_ids', [])))
                ->merge(collect($request->input('ads_ids', [])))
                ->merge(collect($request->input('ccm_ids', [])))
                ->merge(collect($request->input('cc_ids', [])));

            // 3. Tentukan user acuan untuk mencari HOD
            $currentUserDivision = Auth::user()
                ->userDivisions
                ->first();

            $targetUserId = (
                $currentUserDivision &&
                $currentUserDivision->role_id !== null
            )
                ? Auth::id()
                : collect($request->input('buh_ids', []))->first();

            // 4. Ambil hanya kolom user_id agar tidak ambigu
            if ($targetUserId) {
                $hod = UserDivision::query()
                    ->where('user_division.user_id', $targetUserId)
                    ->select('user_division.user_id')
                    ->first();

                if ($hod) {
                    // Tambahkan hanya user_id.
                    // Jangan menambahkan $hod->id.
                    $allUserIds->push((int) $hod->user_id);
                }
            }

            // 5. Tambahkan user login jika bukan admin
            if (!Auth::user()->isAdmin) {
                $allUserIds->push((int) Auth::id());
            }

            // 6. unique harus dijalankan setelah seluruh ID ditambahkan
            $allUserIds = $allUserIds
                ->filter(fn ($userId) => $userId !== null && $userId !== '')
                ->map(fn ($userId) => (int) $userId)
                ->filter(fn ($userId) => $userId > 0)
                ->unique()
                ->values();

            // 7. Cegah pasangan user_id dan client_id dimasukkan dua kali
            foreach ($allUserIds as $userId) {
                UserClient::firstOrCreate(
                    [
                        'user_id'   => $userId,
                        'client_id' => (int) $produk->id,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }

            DB::table('client_division')->updateOrInsert(
                ['client_id' => (int) $produk->id, 'division_id' => (int) $request->divisi],
                ['status' => (int) $request->status, 'created_at' => now(), 'updated_at' => now()]
            );

            $this->ensureDefaultClientDocuments($produk, (int) $request->divisi);

            $draftKey = $request->input('oauth_draft_key');
            $pendingMeta = $draftKey ? $request->session()->get('meta_oauth_pending.'.$draftKey) : null;
            $pendingThreads = $draftKey ? $request->session()->get('threads_oauth_pending.'.$draftKey) : null;
            $pendingTikTok = $draftKey ? $request->session()->get('tiktok_oauth_pending.'.$draftKey) : null;
            if (is_array($pendingMeta)) {
                OAuthConnection::updateOrCreate(
                    ['client_id' => $produk->id, 'provider' => 'meta'],
                    $pendingMeta
                );
                $metaConnectionSync->refreshSiblingConnections($produk->id, $pendingMeta);
                $this->ensureOAuthPlatformLabels($produk, $pendingMeta['assets']['pages'] ?? []);
            }
            if (is_array($pendingThreads)) {
                OAuthConnection::updateOrCreate(
                    ['client_id' => $produk->id, 'provider' => 'threads'],
                    $pendingThreads
                );
                $this->ensureOAuthPlatformLabel($produk, 'Threads');
            }
            if (is_array($pendingTikTok)) {
                OAuthConnection::updateOrCreate(
                    ['client_id' => $produk->id, 'provider' => 'tiktok'],
                    $pendingTikTok
                );
                $this->ensureOAuthPlatformLabel($produk, 'TikTok');
            }

            DB::commit();

            if (is_array($pendingMeta) && $draftKey) {
                $request->session()->forget('meta_oauth_pending.'.$draftKey);
            }
            if (is_array($pendingThreads) && $draftKey) {
                $request->session()->forget('threads_oauth_pending.'.$draftKey);
            }
            if (is_array($pendingTikTok) && $draftKey) {
                $request->session()->forget('tiktok_oauth_pending.'.$draftKey);
            }

            return redirect()
                ->back()
                ->with('success', $isExistingClient
                    ? 'Client sudah tersedia. Divisi dan tim berhasil ditambahkan ke client tersebut.'
                    : 'Produk dan Tim berhasil disimpan!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    private function ensureOAuthPlatformLabels(ProdukClient $client, array $pages): void
    {
        $current = is_array($client->access_token) ? $client->access_token : [];
        $platforms = collect($current['platform'] ?? []);
        $access = collect($current['access'] ?? []);

        foreach (['Facebook', 'Instagram'] as $platform) {
            if ($platform === 'Facebook' && empty($pages)) {
                continue;
            }
            if ($platform === 'Instagram' && ! collect($pages)->contains(
                fn ($page) => filled(data_get($page, 'instagram_business_account.id'))
            )) {
                continue;
            }
            if (! $platforms->contains($platform)) {
                $platforms->push($platform);
                $access->push('');
            }
        }

        $client->update(['access_token' => [
            'platform' => $platforms->values()->all(),
            'access' => $access->values()->all(),
        ]]);
    }

    private function ensureOAuthPlatformLabel(ProdukClient $client, string $platform): void
    {
        $current = is_array($client->access_token) ? $client->access_token : [];
        $platforms = collect($current['platform'] ?? []);
        $access = collect($current['access'] ?? []);
        if (! $platforms->contains($platform)) {
            $platforms->push($platform);
            $access->push('');
            $client->update(['access_token' => [
                'platform' => $platforms->values()->all(),
                'access' => $access->values()->all(),
            ]]);
        }
    }

    public function update($id, Request $request)
    {
        $id = (int) $id;

        try {
            DB::beginTransaction();

            // 1. Update data produk
            $produk = ProdukClient::findOrFail($id);

            $accessTokenArray = $this->withDefaultMarketingPlatforms($this->mergeAccessTokens(
                $produk->access_token,
                $request->input('platform', []),
                $request->input('access', [])
            ));

            $produk->update([
                'kategori_jasa_id' => (int) $request->kategori_jasa_id,
                'nama'              => $request->nama,
                'access_token'      => $accessTokenArray,
            ]);

            $metaConnection = $produk->oauthConnections()->where('provider', 'meta')->first();
            if ($metaConnection && $request->hasAny(['meta_page_id', 'meta_instagram_account_id', 'meta_ad_account_id'])) {
                $metaConnection->saveSelectedMetaAssets([
                    'page_id' => $request->input('meta_page_id'),
                    'instagram_account_id' => $request->input('meta_instagram_account_id'),
                    'ad_account_id' => $request->input('meta_ad_account_id'),
                ]);
            }

            // 2. Kumpulkan semua ID user dari form edit
            $allUserIds = collect()
                ->merge(collect($request->input('buh_ids', [])))
                ->merge(collect($request->input('dm_ids', [])))
                ->merge(collect($request->input('ads_ids', [])))
                ->merge(collect($request->input('ccm_ids', [])))
                ->merge(collect($request->input('cc_ids', [])));

            // Akun reviewer pada client App Review harus tetap memiliki akses
            // walaupun tidak ikut terkirim dari pilihan tim pada form edit.
            $reviewerEmail = match (strtolower(trim((string) $produk->nama))) {
                'meta app review' => 'reviewer.meta@lightermediagroup.com',
                'tiktok app review' => 'reviewer.tiktok@lightermediagroup.com',
                default => null,
            };
            if ($reviewerEmail) {
                $reviewerId = User::query()->where('email', $reviewerEmail)->value('id');
                if ($reviewerId) {
                    $allUserIds->push((int) $reviewerId);
                }
            }

            // 3. Tentukan user acuan
            $targetUserId = !Auth::user()->isAdmin
                ? Auth::id()
                : collect($request->input('buh_ids', []))->first();

            // 4. Tambahkan HOD
            if ($targetUserId) {
                $hod = UserDivision::query()
                    ->where('user_division.user_id', $targetUserId)
                    ->select('user_division.user_id')
                    ->first();

                if ($hod) {
                    // Masukkan user_id, bukan id tabel user_division.
                    $allUserIds->push((int) $hod->user_id);
                }
            }

            // 5. Pastikan user login tetap memiliki akses jika bukan admin
            if (!Auth::user()->isAdmin) {
                $allUserIds->push((int) Auth::id());
            }

            // 6. Bersihkan dan hilangkan ID duplikat
            $allUserIds = $allUserIds
                ->filter(function ($userId) {
                    return $userId !== null && $userId !== '';
                })
                ->map(function ($userId) {
                    return (int) $userId;
                })
                ->filter(function ($userId) {
                    return $userId > 0;
                })
                ->unique()
                ->values();

            /*
            * 7. Ambil ID user yang sudah tersimpan.
            * Dipakai untuk membandingkan data lama dengan data baru.
            */
            $divisionId = (int) $request->divisi;
            $existingUserIds = UserClient::query()
                ->join('user_division', 'user_division.user_id', '=', 'user_client.user_id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('user_client.client_id', $produk->id)
                ->where('roles.division_id', $divisionId)
                ->distinct()
                ->pluck('user_client.user_id')
                ->map(function ($userId) {
                    return (int) $userId;
                });

            /*
            * 8. Cari perbedaannya.
            *
            * $userIdsToAdd:
            * Ada di form baru, tetapi belum ada di database.
            *
            * $userIdsToDelete:
            * Ada di database, tetapi sudah tidak dipilih di form.
            *
            * ID yang ada di keduanya tidak akan di-update.
            */
            $userIdsToAdd = $allUserIds
                ->diff($existingUserIds)
                ->values();

            $userIdsToDelete = $existingUserIds
                ->diff($allUserIds)
                ->values();

            // 9. Hapus hanya user yang sudah tidak dipilih
            if ($userIdsToDelete->isNotEmpty()) {
                UserClient::query()
                    ->where('client_id', $produk->id)
                    ->whereIn('user_id', $userIdsToDelete->all())
                    ->delete();
            }

            // 10. Tambahkan hanya user yang belum tersimpan
            foreach ($userIdsToAdd as $userId) {
                UserClient::firstOrCreate(
                    [
                        'user_id'   => (int) $userId,
                        'client_id' => (int) $produk->id,
                    ],
                    [
                        'assigned_at' => now(),
                    ]
                );
            }

            DB::table('client_division')->updateOrInsert(
                ['client_id' => (int) $produk->id, 'division_id' => $divisionId],
                ['status' => (int) $request->status, 'created_at' => now(), 'updated_at' => now()]
            );

            DB::commit();

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Perubahan produk dan tim berhasil diperbarui!'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Terjadi kesalahan: '.$e->getMessage()
                );
        }
    }

    /**
     * Pertahankan token lama ketika input token pada form edit kosong.
     */
    private function mergeAccessTokens(?array $currentTokens, array $platforms, array $accessTokens): ?array
    {
        $currentByPlatform = [];

        if (isset($currentTokens['platform'], $currentTokens['access'])) {
            foreach ($currentTokens['platform'] as $index => $platform) {
                if ($platform !== null && $platform !== '') {
                    $currentByPlatform[$platform] = $currentTokens['access'][$index] ?? '';
                }
            }
        } elseif (is_array($currentTokens)) {
            // Tetap mendukung data lama yang tersimpan sebagai daftar objek.
            foreach ($currentTokens as $item) {
                if (is_array($item) && !empty($item['platform'])) {
                    $currentByPlatform[$item['platform']] = $item['access'] ?? '';
                }
            }
        }

        $mergedPlatforms = [];
        $mergedAccessTokens = [];

        foreach ($platforms as $index => $platform) {
            if ($platform === null || $platform === '') {
                continue;
            }

            $submittedToken = $accessTokens[$index] ?? '';
            $mergedPlatforms[] = $platform;
            $mergedAccessTokens[] = $submittedToken !== ''
                ? $submittedToken
                : ($currentByPlatform[$platform] ?? '');
        }

        // Form tanpa baris platform tidak boleh menghapus seluruh token secara tidak sengaja.
        if ($mergedPlatforms === []) {
            return $currentTokens;
        }

        return [
            'platform' => $mergedPlatforms,
            'access' => $mergedAccessTokens,
        ];
    }

    private function withDefaultMarketingPlatforms(?array $tokens): array
    {
        $tokens = is_array($tokens) ? $tokens : [];
        $platforms = collect($tokens['platform'] ?? [])->values();
        $access = collect($tokens['access'] ?? [])->values();

        foreach (self::DEFAULT_MARKETING_PLATFORMS as $defaultPlatform) {
            if (! $platforms->contains(fn ($platform) => strcasecmp((string) $platform, $defaultPlatform) === 0)) {
                $platforms->push($defaultPlatform);
                $access->push('');
            }
        }

        return ['platform' => $platforms->all(), 'access' => $access->all()];
    }

    private function findClientByName(?string $name): ?ProdukClient
    {
        $normalizedName = mb_strtolower(trim((string) $name));

        if ($normalizedName === '') {
            return null;
        }

        return ProdukClient::query()
            ->whereRaw('LOWER(TRIM(nama)) = ?', [$normalizedName])
            ->lockForUpdate()
            ->first();
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $produk = ProdukClient::findOrFail($id);

            // Putus semua hubungan user dengan produk ini di tabel pivot
            // (Opsional jika di migration sudah pakai onDelete('cascade'))
            $produk->teams()->detach(); 

            // Hapus produk
            $produk->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Produk berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function docs(){
        $title = "Access Token";
        return view('cms.produk.access_token', compact("title"));
    }

    // public function getBawahanDM($id){
    //     $dm = User::where('role', 'digital_marketing')
    //           ->where('dmm', $id)
    //           ->get(['id', 'email']);
    //     return $dm;
    // }

    // public function getBawahanCC($id){
    //     $cc = User::where('role', 'content_creator')
    //           ->where('dm', $id)
    //           ->get(['id', 'email']);
    //     return $cc;
    // }
    public function indexDivision($divisi){
        $title = "CMS Produk";
        $user = Auth::user()->id;
        $divisiName = User::find(Auth::user()->id)->userDivisions;
        $RoleSekarang = Auth::user()->isAdmin == false ? DB::table('roles')->where('id', $divisiName[0]->role_id)->value('role_name') : "Super Admin";
        $bawahanSemua = User::unlocked()->join('user_division', 'user_division.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_division.role_id')
            ->where('user_division.division_id', $divisi)
            ->whereIn('roles.level', [1, 2, 3])
            ->get();

        // Lalu pisahkan di controller atau langsung kirim semua ke view
        $buh = $bawahanSemua->where('level', 1);
        $bawahan2 = $bawahanSemua->where('level', 2);
        $bawahan3 = $bawahanSemua->where('level', 3);

        $return = [
            'title' => $title,
            'role' => $RoleSekarang,
            'divisi' => $divisi,
            'user' => $user,
            'buh' => $buh,
            'bawahan2' => $bawahan2,
            'bawahan3' => $bawahan3
        ];
        $requestedDivisionName = strtolower(trim((string) DB::table('divisions')->where('id', $divisi)->value('name')));
        if ($requestedDivisionName === 'multimedia') {
            $return['divisionLabel'] = 'Multimedia';
            $return['level2Label'] = 'Content Creator Manager';
            $return['level3Label'] = 'Content Creator';

            return view('cms.produk.division.ecommerce', $return);
        }

        switch($divisi){
            case 3:
                return view('cms.produk.division.mcn', $return);
                break;
            case 5:
                return view('cms.produk.division.tech', $return);
                break;
            case 6:
                return view('cms.produk.division.finance', $return);
                break;
            case 7:
                $return['divisionLabel'] = 'Ecommerce';
                $return['level2Label'] = 'Project Manager';
                $return['level3Label'] = 'Developer';
                return view('cms.produk.division.ecommerce', $return);
                break;
            default:
        }
    }

    public function getDataDivision($user, $divisi)
    {
        $user = User::findOrFail($user);

        $query = ProdukClient::with(['teams.userDivisions.role'])
            ->external()
            ->join('client_division', 'client_division.client_id', '=', 'produk_client.id')
            ->where('client_division.division_id', $divisi)
            ->select('produk_client.*', 'client_division.status as division_status');

        // Gunakan client_division sebagai sumber daftar client seluruh CMS divisi.
        // Relasi tim/PIC hanya untuk informasi penanggung jawab, bukan syarat tampil.

        $data = $query->get()->map(function ($client) use ($divisi) {

            $teams = $client->teams->filter(function ($team) use ($divisi) {
                return ! $team->is_locked && $team->userDivisions->contains(
                    fn ($membership) => (int) $membership->division_id === (int) $divisi
                );
            });

            return [
                'id'       => $client->id,
                'nama'     => $client->nama,
                'category' => $client->category,
                'status'   => $client->division_status,

                // Business Unit Head
                'buh' => $teams
                    ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $divisi) === 1)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist Manager
                'bawahan2' => $teams
                    ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $divisi) === 2)
                    ->pluck('email')
                    ->values(),

                // MCN Specialist
                'bawahan3' => $teams
                    ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $divisi) === 3)
                    ->pluck('email')
                    ->values(),

                'team_ids' => $teams->pluck('id')->toArray(),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }



    public function tambahDataClient(Request $request, $divisi)
    {
        $validated = $request->validate([
            'client' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer'],
            'buh' => ['nullable', 'array'],
            'buh.*' => ['integer', 'exists:users,id'],
            'mcn' => ['nullable', 'array'],
            'mcn.*' => ['integer', 'exists:users,id'],
            'ms' => ['nullable', 'array'],
            'ms.*' => ['integer', 'exists:users,id'],
        ]);

        return DB::transaction(function () use ($request, $validated, $divisi) {
            $produk = $this->findClientByName($validated['client']);
            $isExistingClient = $produk !== null;

            if (!$produk) {
                $produk = ProdukClient::create([
                    'kategori_jasa_id' => null,
                    'nama' => trim($validated['client']),
                    'status' => (int) $validated['status'],
                    'access_token' => null,
                ]);
            }

            $allUserIds = collect($request->input('buh', []))
                ->merge($request->input('mcn', []))
                ->merge($request->input('ms', []))
                ->filter()
                ->map(fn ($userId) => (int) $userId)
                ->filter(fn ($userId) => $userId > 0)
                ->unique();

            $allUserIds = $this->validateDivisionTeamIds($allUserIds, (int) $divisi);

            foreach ($allUserIds as $userId) {
                UserClient::firstOrCreate(
                    ['user_id' => $userId, 'client_id' => $produk->id],
                    ['assigned_at' => now()]
                );
            }

            DB::table('client_division')->updateOrInsert(
                ['client_id' => $produk->id, 'division_id' => (int) $divisi],
                ['status' => (int) $validated['status'], 'created_at' => now(), 'updated_at' => now()]
            );

            $this->ensureDefaultClientDocuments($produk, (int) $divisi);

            return response()->json([
                'success' => $isExistingClient
                    ? 'Client sudah tersedia. Divisi dan tim berhasil ditambahkan.'
                    : 'Client berhasil dibuat.',
                'client_id' => $produk->id,
                'existing_client' => $isExistingClient,
            ]);
        });
    }

    private function ensureDefaultClientDocuments(ProdukClient $client, int $divisionId): void
    {
        $documents = [
            'Contract Status.pdf',
            'Core SOW.pdf',
            'Contract Operational Notes.pdf',
            'Project Handover.pdf',
        ];

        foreach ($documents as $index => $fileName) {
            $exists = FilePdf::query()
                ->where('client', $client->id)
                ->where('division_id', $divisionId)
                ->whereRaw('LOWER(nama_file) = ?', [mb_strtolower($fileName)])
                ->exists();

            if ($exists) {
                continue;
            }

            $documentName = pathinfo($fileName, PATHINFO_FILENAME);
            $content = '<p><br></p>';
            $storedName = (string) str()->uuid().'.pdf';
            $path = 'pdf/'.$client->id.'/'.$divisionId.'/'.$storedName;
            $pdfContent = Pdf::loadView(
                'pdf.client_document',
                compact('client', 'documentName', 'content')
            )->setPaper('a4', 'portrait')->output();

            abort_unless(
                Storage::disk('local')->put($path, $pdfContent),
                500,
                'Dokumen bawaan client gagal dibuat.'
            );

            $file = FilePdf::create([
                'nama_file' => $fileName,
                'client' => $client->id,
                'division_id' => $divisionId,
                'uploaded_by' => Auth::id(),
                'location' => $path,
                'editor_content' => $content,
                'is_generated_document' => true,
                'is_system_template' => true,
                'is_pinned' => true,
                'pin_order' => $index + 1,
            ]);

            $file->versions()->create([
                'version_number' => 1,
                'nama_file' => $fileName,
                'location' => $path,
                'editor_content' => $content,
                'changed_by' => Auth::id(),
            ]);
        }
    }

    public function updateDataClient(Request $request, $id)
    {
        $validated = $request->validate([
            'client' => ['required', 'string', 'max:255'],
            'status' => ['required', 'integer'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'buh' => ['nullable', 'array'],
            'buh.*' => ['integer', 'exists:users,id'],
            'mcn' => ['nullable', 'array'],
            'mcn.*' => ['integer', 'exists:users,id'],
            'ms' => ['nullable', 'array'],
            'ms.*' => ['integer', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            $produk = ProdukClient::findOrFail($id);

            // Update data client
            $produk->update(['nama' => $request->client]);

            // Gabungkan seluruh user yang dipilih
            $allId = array_merge(
                $request->buh ?? [],
                $request->mcn ?? [],
                $request->ms ?? []
            );

            // Hilangkan ID yang sama jika ada
            $allId = $this->validateDivisionTeamIds(
                collect(array_unique($allId))->map(fn ($userId) => (int) $userId),
                (int) $validated['division_id']
            )->all();

            // Hapus hanya penugasan user dari divisi yang sedang diedit.
            // Penugasan divisi lain pada client yang sama harus tetap dipertahankan.
            $divisionUserIds = DB::table('user_client')
                ->join('user_division', 'user_division.user_id', '=', 'user_client.user_id')
                ->join('roles', 'roles.id', '=', 'user_division.role_id')
                ->where('user_client.client_id', $produk->id)
                ->where('roles.division_id', (int) $validated['division_id'])
                ->whereNotIn('user_client.user_id', $allId ?: [0])
                ->distinct()
                ->pluck('user_client.user_id');

            if ($divisionUserIds->isNotEmpty()) {
                UserClient::where('client_id', $produk->id)
                    ->whereIn('user_id', $divisionUserIds)
                    ->delete();
            }

            // Insert relasi baru tanpa menggandakan pasangan user dan client.
            foreach ($allId as $userId) {
                UserClient::firstOrCreate(
                    ['user_id' => (int) $userId, 'client_id' => $produk->id],
                    ['assigned_at' => now()]
                );
            }

            DB::table('client_division')->updateOrInsert(
                ['client_id' => $produk->id, 'division_id' => (int) $validated['division_id']],
                ['status' => (int) $validated['status'], 'created_at' => now(), 'updated_at' => now()]
            );

            DB::commit();

            return response()->json([
                'success' => 'Data berhasil diupdate'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getProduk(Request $request, $id)
    {
        $produk = ProdukClient::with(['teams.userDivisions.role'])
            ->findOrFail($id);

        $teams = $produk->teams;
        if ($request->filled('division_id')) {
            $divisionId = (int) $request->integer('division_id');
            $teams = $teams->filter(fn ($user) => ! $user->is_locked && $user->userDivisions
                ->contains(fn ($membership) => (int) $membership->division_id === $divisionId));
        }

        $divisionStatus = $request->filled('division_id')
            ? DB::table('client_division')->where('client_id', $produk->id)->where('division_id', (int) $request->integer('division_id'))->value('status')
            : $produk->status;

        return response()->json([
            'id'        => $produk->id,
            'client_id' => $produk->nama,
            'status'    => $divisionStatus ?? 1,

            'buh' => $teams
                ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $request->integer('division_id')) === 1)
                ->pluck('id')
                ->values(),

            'bawahan2' => $teams
                ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $request->integer('division_id')) === 2)
                ->pluck('id')
                ->values(),

            'bawahan3' => $teams
                ->filter(fn($u) => $this->roleLevelInDivision($u, (int) $request->integer('division_id')) === 3)
                ->pluck('id')
                ->values(),
        ]);
    }

    private function roleLevelInDivision(User $user, int $divisionId): int
    {
        return (int) ($user->userDivisions
            ->first(fn ($membership) => (int) $membership->division_id === $divisionId)
            ?->role?->level ?? 0);
    }

    private function validateDivisionTeamIds(\Illuminate\Support\Collection $userIds, int $divisionId): \Illuminate\Support\Collection
    {
        $userIds = $userIds->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $validIds = User::unlocked()
            ->whereIn('id', $userIds)
            ->whereHas('userDivisions', fn ($query) => $query->where('division_id', $divisionId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($validIds->count() !== $userIds->count()) {
            throw ValidationException::withMessages([
                'team' => 'Tim hanya boleh berisi akun aktif dari divisi yang sedang dikelola.',
            ]);
        }

        return $validIds;
    }

    public function hapusDataClient($id)
    {
        DB::beginTransaction();

        try {

            $produk = ProdukClient::findOrFail($id);

            // Hapus relasi user
            UserClient::where('client_id', $id)->delete();

            // Hapus relasi divisi
            DB::table('client_division')
                ->where('client_id', $id)
                ->delete();

            // Hapus client
            $produk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Client berhasil dihapus.'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }
}
