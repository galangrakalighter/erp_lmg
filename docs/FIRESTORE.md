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
   FIREBASE_ACTIVITY_LOG_COLLECTION=logs
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

## Automatic activity logs

Authenticated activity is written to the `logs` collection by default. It
includes successful page visits, login/logout, and Eloquent create, update, and
delete events. Each document includes the user identity, division, IP address,
user agent, module, target, old/new values, and a Firestore timestamp.

Passwords, tokens, API keys, secrets, and private keys are redacted. Firestore
errors are written to the Laravel log and never interrupt the user's request.

Admins can inspect the records from the read-only **Activity Log** sidebar menu.
The route is protected by both authentication and the user's `isAdmin` flag;
there are no create, update, or delete endpoints for logs.
