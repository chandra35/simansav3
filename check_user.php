<?php

use App\Models\User;

$user = User::where('username', '1871026709900003')->first();

if ($user) {
    echo "=== USER DATA ===" . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Username: " . $user->username . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo PHP_EOL;
    
    echo "=== ROLES (Spatie) ===" . PHP_EOL;
    foreach ($user->roles as $role) {
        echo "- " . $role->name . PHP_EOL;
    }
    echo PHP_EOL;
    
    echo "=== RELATIONS ===" . PHP_EOL;
    echo "Has Siswa: " . ($user->siswa ? 'YES (ID: ' . $user->siswa->id . ')' : 'NO') . PHP_EOL;
    echo "Has GTK: " . ($user->gtk ? 'YES (ID: ' . $user->gtk->id . ')' : 'NO') . PHP_EOL;
    
} else {
    echo "User tidak ditemukan!" . PHP_EOL;
}
