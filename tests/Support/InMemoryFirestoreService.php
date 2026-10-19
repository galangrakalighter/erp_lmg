<?php

namespace Tests\Support;

use App\Services\FirestoreService;

final class InMemoryFirestoreService extends FirestoreService
{
    public array $records = [];

    public function create(string $collection, array $data, ?string $documentId = null): array
    {
        $this->records[] = ['collection' => $collection, 'data' => $data, 'document_id' => $documentId];
        return $data;
    }
}
