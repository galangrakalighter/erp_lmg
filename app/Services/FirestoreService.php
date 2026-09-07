<?php

namespace App\Services;

use DateTimeInterface;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class FirestoreService
{
    private const SCOPE = 'https://www.googleapis.com/auth/datastore';

    private ?string $cachedAccessToken = null;

    private int $accessTokenExpiresAt = 0;

    public function list(
        string $collection,
        int $pageSize = 50,
        ?string $pageToken = null,
        ?string $orderBy = null,
    ): array {
        $query = ['pageSize' => min(max($pageSize, 1), 1000)];

        if ($pageToken) {
            $query['pageToken'] = $pageToken;
        }

        if ($orderBy) {
            $query['orderBy'] = $orderBy;
        }

        $response = $this->request()->get($this->documentsUrl($collection), $query)->throw()->json();

        return [
            'documents' => array_map(fn (array $document) => $this->decodeDocument($document), $response['documents'] ?? []),
            'next_page_token' => $response['nextPageToken'] ?? null,
        ];
    }

    public function get(string $collection, string $documentId): ?array
    {
        $response = $this->request()->get($this->documentsUrl("{$collection}/{$documentId}"));

        if ($response->notFound()) {
            return null;
        }

        return $this->decodeDocument($response->throw()->json());
    }

    public function queryByField(
        string $collection,
        string $field,
        mixed $value,
        int $limit = 50,
        ?string $orderBy = null,
    ): array {
        $structuredQuery = [
            'from' => [['collectionId' => $collection]],
            'where' => [
                'fieldFilter' => [
                    'field' => ['fieldPath' => $field],
                    'op' => 'EQUAL',
                    'value' => $this->encodeValue($value),
                ],
            ],
            'limit' => min(max($limit, 1), 1000),
        ];

        if ($orderBy) {
            [$fieldPath, $direction] = array_pad(preg_split('/\s+/', trim($orderBy), 2), 2, 'asc');
            $structuredQuery['orderBy'] = [[
                'field' => ['fieldPath' => $fieldPath],
                'direction' => strtolower($direction) === 'desc' ? 'DESCENDING' : 'ASCENDING',
            ]];
        }

        $response = $this->request()
            ->post($this->runQueryUrl(), ['structuredQuery' => $structuredQuery])
            ->throw()
            ->json();

        return array_values(array_map(
            fn (array $item): array => $this->decodeDocument($item['document']),
            array_filter($response, fn (array $item): bool => isset($item['document'])),
        ));
    }

    public function create(string $collection, array $data, ?string $documentId = null): array
    {
        $query = $documentId ? ['documentId' => $documentId] : [];
        $response = $this->request()
            ->post($this->documentsUrl($collection).($query ? '?'.http_build_query($query) : ''), [
                'fields' => $this->encodeFields($data),
            ])
            ->throw()
            ->json();

        return $this->decodeDocument($response);
    }

    public function set(string $collection, string $documentId, array $data): array
    {
        $response = $this->request()
            ->patch($this->documentsUrl("{$collection}/{$documentId}"), [
                'fields' => $this->encodeFields($data),
            ])
            ->throw()
            ->json();

        return $this->decodeDocument($response);
    }

    public function delete(string $collection, string $documentId): bool
    {
        $this->request()->delete($this->documentsUrl("{$collection}/{$documentId}"))->throw();

        return true;
    }

    public function testConnection(?string $collection = null): array
    {
        $collection ??= config('firestore.default_collection');

        if (! $collection) {
            throw new InvalidArgumentException('FIREBASE_DEFAULT_COLLECTION belum diisi.');
        }

        $result = $this->list($collection, 1);

        return [
            'project_id' => $this->projectId(),
            'database_id' => config('firestore.database_id', '(default)'),
            'collection' => $collection,
            'reachable' => true,
            'sample_count' => count($result['documents']),
        ];
    }

    private function request(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->accessToken())
            ->timeout(config('firestore.timeout', 15))
            ->retry(2, 250);
    }

    private function accessToken(): string
    {
        if ($this->cachedAccessToken && $this->accessTokenExpiresAt > time() + 60) {
            return $this->cachedAccessToken;
        }

        $credentials = new ServiceAccountCredentials(self::SCOPE, $this->credentialsPath());
        $token = $credentials->fetchAuthToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Google tidak mengembalikan access token untuk service account.');
        }

        $this->cachedAccessToken = $token['access_token'];
        $this->accessTokenExpiresAt = time() + (int) ($token['expires_in'] ?? 3600);

        return $this->cachedAccessToken;
    }

    private function documentsUrl(string $path): string
    {
        $segments = array_filter(explode('/', trim($path, '/')), fn (string $segment) => $segment !== '');

        if ($segments === [] || in_array('..', $segments, true)) {
            throw new InvalidArgumentException('Path collection/document Firestore tidak valid.');
        }

        $encodedPath = implode('/', array_map('rawurlencode', $segments));
        $database = rawurlencode((string) config('firestore.database_id', '(default)'));

        return sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/%s/documents/%s',
            rawurlencode($this->projectId()),
            $database,
            $encodedPath
        );
    }

    private function runQueryUrl(): string
    {
        return sprintf(
            'https://firestore.googleapis.com/v1/projects/%s/databases/%s/documents:runQuery',
            rawurlencode($this->projectId()),
            rawurlencode((string) config('firestore.database_id', '(default)')),
        );
    }

    private function projectId(): string
    {
        $projectId = config('firestore.project_id');

        if (! is_string($projectId) || trim($projectId) === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID belum diisi.');
        }

        return trim($projectId);
    }

    private function credentialsPath(): string
    {
        $path = config('firestore.credentials');

        if (! is_string($path) || trim($path) === '') {
            throw new RuntimeException('FIREBASE_CREDENTIALS belum diisi.');
        }

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));
        $isAbsolute = preg_match('/^[A-Za-z]:'.preg_quote(DIRECTORY_SEPARATOR, '/').'/', $path)
            || str_starts_with($path, DIRECTORY_SEPARATOR);
        $resolved = $isAbsolute ? $path : base_path($path);

        if (! is_file($resolved) || ! is_readable($resolved)) {
            throw new RuntimeException("File service account tidak ditemukan atau tidak dapat dibaca: {$resolved}");
        }

        return $resolved;
    }

    private function encodeFields(array $data): array
    {
        $fields = [];

        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }

        return $fields;
    }

    private function encodeValue(mixed $value): array
    {
        return match (true) {
            $value === null => ['nullValue' => null],
            is_bool($value) => ['booleanValue' => $value],
            is_int($value) => ['integerValue' => (string) $value],
            is_float($value) => ['doubleValue' => $value],
            is_string($value) => ['stringValue' => $value],
            $value instanceof DateTimeInterface => ['timestampValue' => $value->format(DATE_RFC3339_EXTENDED)],
            is_array($value) && array_is_list($value) => [
                'arrayValue' => ['values' => array_map(fn (mixed $item) => $this->encodeValue($item), $value)],
            ],
            is_array($value) => ['mapValue' => ['fields' => $this->encodeFields($value)]],
            default => throw new InvalidArgumentException('Tipe data tidak didukung oleh Firestore encoder: '.get_debug_type($value)),
        };
    }

    private function decodeDocument(array $document): array
    {
        return [
            'id' => basename($document['name'] ?? ''),
            'data' => $this->decodeFields($document['fields'] ?? []),
            'created_at' => $document['createTime'] ?? null,
            'updated_at' => $document['updateTime'] ?? null,
        ];
    }

    private function decodeFields(array $fields): array
    {
        $decoded = [];

        foreach ($fields as $key => $value) {
            $decoded[$key] = $this->decodeValue($value);
        }

        return $decoded;
    }

    private function decodeValue(array $value): mixed
    {
        return match (true) {
            array_key_exists('nullValue', $value) => null,
            array_key_exists('booleanValue', $value) => $value['booleanValue'],
            array_key_exists('integerValue', $value) => (int) $value['integerValue'],
            array_key_exists('doubleValue', $value) => (float) $value['doubleValue'],
            array_key_exists('timestampValue', $value) => $value['timestampValue'],
            array_key_exists('stringValue', $value) => $value['stringValue'],
            array_key_exists('bytesValue', $value) => $value['bytesValue'],
            array_key_exists('referenceValue', $value) => $value['referenceValue'],
            array_key_exists('geoPointValue', $value) => $value['geoPointValue'],
            array_key_exists('arrayValue', $value) => array_map(
                fn (array $item) => $this->decodeValue($item),
                $value['arrayValue']['values'] ?? []
            ),
            array_key_exists('mapValue', $value) => $this->decodeFields($value['mapValue']['fields'] ?? []),
            default => null,
        };
    }
}
