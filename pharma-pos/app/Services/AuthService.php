<?php

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class AuthService
{
    /**
     * Login user
     */
    public function login(string $email, string $password): ?User
    {
        $user = User::where('email', $email)
            ->where('is_active', true)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        // Log aktivitas login
        $this->logActivity($user, AuditLog::ACTION_LOGIN, 'auth', $user->id);

        return $user;
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $this->logActivity($user, AuditLog::ACTION_LOGOUT, 'auth', $user->id);
    }

    /**
     * Cek apakah user memiliki role tertentu
     */
    public function hasRole(User $user, string $role): bool
    {
        return $user->role && $user->role->nama_role === $role;
    }

    /**
     * Cek apakah user memiliki akses ke modul
     */
    public function hasAccess(User $user, string $modul): bool
    {
        $roleAccess = [
            'admin' => ['*'],
            'manager' => ['master_data', 'laporan', 'pembelian', 'stok'],
            'kasir' => ['pos', 'penjualan'],
        ];

        if (!$user->role) {
            return false;
        }

        $userRole = $user->role->nama_role;
        $access = $roleAccess[$userRole] ?? [];

        // Admin punya akses ke semua
        if (in_array('*', $access)) {
            return true;
        }

        return in_array($modul, $access);
    }

    /**
     * Log aktivitas user
     */
    private function logActivity(User $user, string $aksi, string $modul, ?int $referensiId = null): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => $aksi,
            'modul' => $modul,
            'referensi_id' => $referensiId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
