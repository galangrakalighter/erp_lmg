# Firestore integration

The application accesses Cloud Firestore from the Laravel backend through the
official Firestore REST API and Google service-account authentication.

## Setup

1. In Firebase Console, open **Project settings > Service accounts** and create
   a private key.
2. Save the downloaded file as
   `storage/app/firebase/service-account.json`. Never commit this file.
3. Complete these values in `.env`:

   ```dotenv
   FIREBASE_PROJECT_ID=your-firebase-project-id
   FIREBASE_DATABASE_ID=(default)
   FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
   FIREBASE_DEFAULT_COLLECTION=your_collection
   ```

4. Clear cached configuration and test the connection:

   ```shell
   php artisan config:clear
   php artisan firestore:test
   ```

   A different collection can be tested without changing `.env`:

   ```shell
   php artisan firestore:test collection_name
   ```

## Usage from Laravel

Inject `App\Services\FirestoreService` into a controller or another service:

```php
public function index(FirestoreService $firestore)
{
    return response()->json($firestore->list('customers'));
}

public function store(Request $request, FirestoreService $firestore)
{
    return response()->json(
        $firestore->create('customers', $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
        ])),
        201
    );
}
```

Available operations:

- `list($collection, $pageSize, $pageToken)`
- `get($collection, $documentId)`
- `create($collection, $data, $documentId = null)`
- `set($collection, $documentId, $data)`
- `delete($collection, $documentId)`

`set` replaces the document fields with the supplied data. Add application
routes only after deciding which collections and user roles may access them.
