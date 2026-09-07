<?php

namespace App\Http\Controllers;

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
        $nextPageToken = null;
        $error = null;

        try {
            $result = $firestore->list(
                (string) config('firestore.activity_log_collection', 'logs'),
                50,
                $request->string('page_token')->toString() ?: null,
                'created_at desc',
            );
            $logs = array_map(
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
            $nextPageToken = $result['next_page_token'];
        } catch (Throwable $exception) {
            report($exception);
            $error = 'Activity log belum dapat dimuat. Periksa koneksi Firestore pada server.';
        }

        return view('activity_logs.index', [
            'title' => 'Activity Log',
            'logs' => $logs,
            'nextPageToken' => $nextPageToken,
            'error' => $error,
        ]);
    }
}
