<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account JSON file.
    | Download from: Firebase Console → Project Settings → Service Accounts
    |   → Generate New Private Key
    |
    | Place the file at: storage/app/firebase/service-account.json
    | Or set the FIREBASE_CREDENTIALS env variable to a custom path.
    |
    */
    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase/service-account.json')),
];
