<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogFormatter;
use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ActivityLogController extends Controller
{
    public function index(
        Request $request,
        FirestoreService $firestore,
        ActivityLogFormatter $formatter,
    ): View {
        $logs = [];
        $error = null;
        $perPage = 25;
        $currentPage = max(1, $request->integer('page', 1));
        $total = 0;
        $lastPage = 1;
        $actionCounts = [];
        $uniqueUsers = 0;

        try {
            $collection = (string) config('firestore.activity_log_collection', 'logs');
            $selectedEmail = $request->string('email')->trim()->toString();

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
            $allLogs = array_map(
                function (array $document) use ($formatter): array {
                    $log = array_merge(
                        ['document_id' => $document['id']],
                        $document['data'] ?? [],
                    );
                    $log['human_detail'] = $formatter->format($log);

                    return $log;
                },
                $result['documents'],
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
            report($exception);
            $error = 'Activity log belum dapat dimuat. Periksa koneksi Firestore pada server.';
        }

        return view('activity_logs.index', [
            'title' => 'Activity Log',
            'logs' => $logs,
            'error' => $error,
            'emails' => User::query()->orderBy('email')->pluck('email'),
            'selectedEmail' => $request->string('email')->toString(),
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
}
