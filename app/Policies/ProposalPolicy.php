<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\User;

/**
 * Otorisasi usulan.
 *
 * `viewAny` sengaja longgar (semua peran boleh membuka daftar); pembatasan
 * baris dilakukan di ProposalResource::getEloquentQuery() per peran. Method
 * selain CRUD dasar dipakai oleh aksi status pada fase 4-8.
 */
class ProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proposals.viewAny');
    }

    public function view(User $user, Proposal $proposal): bool
    {
        if ($user->hasAnyRole(['admin_lppm', 'pimpinan'])) {
            return true;
        }

        if ($user->isReviewer() && $proposal->isAssignedReviewer($user)) {
            return true;
        }

        if ($proposal->user_id === $user->getKey()) {
            return true;
        }

        // Anggota tim (diundang) boleh melihat usulan yang mengikutsertakannya.
        return $proposal->members()->where('user_id', $user->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->can('proposals.create');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        // Hanya pemilik, dan hanya selama masih draf.
        return $proposal->user_id === $user->getKey()
            && $proposal->status === ProposalStatus::Draft
            && $user->can('proposals.update');
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $proposal->user_id === $user->getKey()
            && $proposal->status === ProposalStatus::Draft
            && $user->can('proposals.delete');
    }

    /* -------------------------------------------------------------------------
     |  Aksi alur kerja
     | ------------------------------------------------------------------------- */

    /** Dosen mengirim usulan final (draft -> submitted). */
    public function submit(User $user, Proposal $proposal): bool
    {
        return $proposal->user_id === $user->getKey()
            && $proposal->status === ProposalStatus::Draft
            && filled($proposal->file_proposal)
            && $proposal->allDosenMembersApproved();
    }

    /** Persetujuan institusi / penolakan (gerbang tunggal). */
    public function decideApproval(User $user, Proposal $proposal): bool
    {
        return $user->canApproveInstitution()
            && $proposal->status === ProposalStatus::Submitted;
    }

    /** Admin LPPM menugaskan reviewer (approved_lppm atau under_review). */
    public function assignReviewer(User $user, Proposal $proposal): bool
    {
        return $user->isAdminLppm()
            && in_array($proposal->status, [ProposalStatus::ApprovedLppm, ProposalStatus::UnderReview], true);
    }

    /** Reviewer mengisi penilaian (harus ditugaskan & usulan sedang dinilai). */
    public function review(User $user, Proposal $proposal): bool
    {
        return $user->isReviewer()
            && $proposal->status === ProposalStatus::UnderReview
            && $proposal->isAssignedReviewer($user);
    }

    /** Admin LPPM menetapkan pendanaan (under_review -> funded/rejected). */
    public function decideFunding(User $user, Proposal $proposal): bool
    {
        return $user->isAdminLppm()
            && $proposal->status === ProposalStatus::UnderReview;
    }

    /** Dosen mengunggah laporan kemajuan/akhir. */
    public function submitReport(User $user, Proposal $proposal): bool
    {
        return $proposal->user_id === $user->getKey()
            && in_array($proposal->status, [ProposalStatus::Funded, ProposalStatus::InProgress], true);
    }

    /** Dosen menambah/mengelola bukti luaran. */
    public function manageOutputs(User $user, Proposal $proposal): bool
    {
        return $proposal->user_id === $user->getKey()
            && in_array($proposal->status, [
                ProposalStatus::InProgress,
                ProposalStatus::Reported,
                ProposalStatus::OutputValidated,
            ], true);
    }

    /** Admin LPPM memvalidasi bukti luaran. */
    public function validateOutput(User $user, Proposal $proposal): bool
    {
        return $user->isAdminLppm()
            && in_array($proposal->status, [ProposalStatus::Reported, ProposalStatus::OutputValidated], true);
    }

    private const BERJALAN = [
        ProposalStatus::Funded,
        ProposalStatus::InProgress,
        ProposalStatus::Reported,
        ProposalStatus::OutputValidated,
    ];

    /** Dosen mengisi catatan harian (logbook) untuk usulan yang berjalan. */
    public function logbook(User $user, Proposal $proposal): bool
    {
        return $proposal->user_id === $user->getKey()
            && in_array($proposal->status, self::BERJALAN, true);
    }

    /** Admin LPPM melakukan monev internal PT atas usulan yang berjalan. */
    public function monevInternal(User $user, Proposal $proposal): bool
    {
        return $user->isAdminLppm()
            && in_array($proposal->status, self::BERJALAN, true);
    }
}
