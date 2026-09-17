<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\User;
use App\Services\ActivityLogFormatter;
use App\Services\ActivityPageDescription;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ActivityLogController extends Controller
{
    public function index(
        Request $request,
        FirestoreService $firestore,
        ActivityLogFormatter $formatter,
        ActivityPageDescription $pageDescription,
    ): View {
        $logs = [];
        $error = null;
        $perPage = 25;
        $currentPage = max(1, $request->integer('page', 1));
        $total = 0;
        $lastPage = 1;
        $actionCounts = [];
        $uniqueUsers = 0;
        $selectedDivision = $request->string('division')->toString();

        try {
            $collection = (string) config('firestore.activity_log_collection', 'logs');
            $selectedEmail = $request->string('email')->trim()->toString();
            $snapshotKey = 'activity_logs.firestore_snapshot.'.sha1($collection.'|'.$selectedEmail);

            try {
                if ($selectedEmail !== '') {
                    $documents = $firestore->queryByField(
                        $collection,
                        'email',
                        $selectedEmail,
                        1000,
                    );
                    usort(
                        $documents,
                        fn (array $left, array $right): int => strcmp(
                            (string) data_get($right, 'data.created_at', ''),
                            (string) data_get($left, 'data.created_at', ''),
                        ),
                    );
                    $result = ['documents' => $documents, 'next_page_token' => null];
                } else {
                    $result = $firestore->list(
                        $collection,
                        1000,
                        null,
                        'created_at desc',
                    );
                }
                Cache::put($snapshotKey, $result, now()->addDay());
            } catch (Throwable $firestoreException) {
                $result = Cache::get($snapshotKey);
                if (! is_array($result)) {
                    throw $firestoreException;
                }
                $error = 'Koneksi Firestore sedang terganggu. Data yang ditampilkan adalah snapshot terakhir.';
                Log::warning('Activity log menggunakan snapshot Firestore.', [
                    'error' => $firestoreException->getMessage(),
                ]);
            }
            $divisionEmails = $selectedDivision === ''
                ? []
                : User::query()
                    ->whereHas('userDivisions', fn ($query) => $query->where('division_id', $selectedDivision))
                    ->pluck('email')
                    ->filter()
                    ->map(fn (string $email): string => strtolower($email))
                    ->all();
            $documents = $this->filterDocuments(
                $result['documents'],
                $selectedDivision,
                $request->string('start_date')->toString(),
                $request->string('end_date')->toString(),
                $divisionEmails,
            );
            $allLogs = array_map(
                function (array $document) use ($formatter, $pageDescription): array {
                    $log = array_merge(
                        ['document_id' => $document['id']],
                        $document['data'] ?? [],
                    );
                    $log['description'] = $pageDescription->describeStoredLog($log);
                    $log['human_detail'] = $formatter->format($log);

                    return $log;
                },
                $documents,
            );
            $total = count($allLogs);
            $lastPage = max(1, (int) ceil($total / $perPage));
            $currentPage = min($currentPage, $lastPage);
            $logs = array_slice($allLogs, ($currentPage - 1) * $perPage, $perPage);
            $actionCounts = array_count_values(array_map(
                fn (array $log): string => strtoupper((string) ($log['action'] ?? 'OTHER')),
                $allLogs,
            ));
            $uniqueUsers = count(array_unique(array_filter(array_column($allLogs, 'email'))));
        } catch (Throwable $exception) {
            Log::warning('Activity log belum dapat dimuat dari Firestore.', [
                'error' => $exception->getMessage(),
            ]);
            $error = 'Activity log belum dapat dimuat. Periksa koneksi Firestore pada server.';
        }

        $emailQuery = User::query()->orderBy('email');

        if ($selectedDivision !== '') {
            $emailQuery->whereHas(
                'userDivisions',
                fn ($query) => $query->where('division_id', $selectedDivision),
            );
        }

        return view('activity_logs.index', [
            'title' => 'Activity Log',
            'logs' => $logs,
            'error' => $error,
            'emails' => $emailQuery->pluck('email'),
            'divisions' => Division::query()->orderBy('name')->get(['id', 'name']),
            'selectedEmail' => $request->string('email')->toString(),
            'selectedDivision' => $request->string('division')->toString(),
            'startDate' => $request->string('start_date')->toString(),
            'endDate' => $request->string('end_date')->toString(),
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'perPage' => $perPage,
            'total' => $total,
            'from' => $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0,
            'to' => min($currentPage * $perPage, $total),
            'actionCounts' => $actionCounts,
            'uniqueUsers' => $uniqueUsers,
        ]);
    }

    private function filterDocuments(
        array $documents,
        string $division,
        string $startDate,
        string $endDate,
        array $divisionEmails = [],
    ): array {
        return array_values(array_filter($documents, function (array $document) use ($division, $startDate, $endDate, $divisionEmails): bool {
            $data = $document['data'] ?? [];

            if ($division !== '') {
                $divisionIds = array_map('strval', (array) ($data['division_ids'] ?? []));
                $primaryDivision = isset($data['divisi']) ? (string) $data['divisi'] : null;

                $emailMatchesDivision = in_array(strtolower((string) ($data['email'] ?? '')), $divisionEmails, true);

                if ($primaryDivision !== $division
                    && ! in_array($division, $divisionIds, true)
                    && ! $emailMatchesDivision) {
                    return false;
                }
            }

            if ($startDate === '' && $endDate === '') {
                return true;
            }

            try {
                $logDate = Carbon::parse($data['created_at'] ?? null)
                    ->timezone('Asia/Jakarta')
                    ->toDateString();
            } catch (Throwable) {
                return false;
            }

            return ($startDate === '' || $logDate >= $startDate)
                && ($endDate === '' || $logDate <= $endDate);
        }));
    }
}
