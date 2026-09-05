<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Anggota tim usulan. Anggota dosen tertaut akun & wajib menyetujui sebelum
 * usulan dapat dikirim; anggota non-dosen/mahasiswa cukup data teks.
 */
class ProposalMember extends Model
{
    protected $fillable = [
        'proposal_id',
        'jenis',
        'user_id',
        'nama',
        'identitas_no',
        'institusi',
        'prodi',
        'jenjang',
        'tugas',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'jenis' => MemberType::class,
            'status' => MemberApprovalStatus::class,
        ];
    }

    /** @return BelongsTo<Proposal, self> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    /** @return BelongsTo<User, self> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
