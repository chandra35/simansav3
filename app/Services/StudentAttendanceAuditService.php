<?php

namespace App\Services;

use App\Models\AbsensiSiswaAudit;
use App\Models\AbsensiSiswaRecord;
use App\Models\AbsensiSiswaSession;
use App\Models\User;
use Illuminate\Http\Request;

class StudentAttendanceAuditService
{
    public function session(
        AbsensiSiswaSession $session,
        User $actor,
        string $action,
        array $before,
        array $after,
        ?string $reason,
        Request $request
    ): AbsensiSiswaAudit {
        return AbsensiSiswaAudit::create([
            'session_id' => $session->id,
            'actor_user_id' => $actor->id,
            'action' => $action,
            'before_values' => $before ?: null,
            'after_values' => $after ?: null,
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function record(
        AbsensiSiswaSession $session,
        AbsensiSiswaRecord $record,
        User $actor,
        string $action,
        array $before,
        array $after,
        ?string $reason,
        Request $request
    ): AbsensiSiswaAudit {
        return AbsensiSiswaAudit::create([
            'session_id' => $session->id,
            'record_id' => $record->id,
            'siswa_id' => $record->siswa_id,
            'actor_user_id' => $actor->id,
            'action' => $action,
            'before_values' => $before ?: null,
            'after_values' => $after ?: null,
            'reason' => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
