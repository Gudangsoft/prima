<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\MemberApprovalStatus;
use App\Enums\MemberType;
use App\Filament\Widgets\Concerns\ForDosen;
use App\Models\ProposalMember;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

/**
 * Kartu "Undangan Anggota Tim" di dasbor dosen: usulan orang lain yang mengundang
 * user ini sebagai anggota dan masih menunggu persetujuan.
 */
class UndanganTimCard extends Widget
{
    use ForDosen;

    protected static string $view = 'filament.widgets.undangan-tim-card';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'lg' => 1,
    ];

    public static function canView(): bool
    {
        return static::isDosenUser() && static::pending()->isNotEmpty();
    }

    private static function isDosenUser(): bool
    {
        return auth()->user()?->isActingAs('dosen') ?? false;
    }

    /** @return Collection<int, ProposalMember> */
    private static function pending(): Collection
    {
        return ProposalMember::query()
            ->where('user_id', auth()->id())
            ->where('jenis', MemberType::Dosen->value)
            ->where('status', MemberApprovalStatus::Menunggu->value)
            ->with('proposal:id,judul,user_id', 'proposal.submitter:id,name')
            ->get();
    }

    public function respond(int $memberId, string $keputusan): void
    {
        $member = ProposalMember::query()
            ->where('id', $memberId)
            ->where('user_id', auth()->id())
            ->where('status', MemberApprovalStatus::Menunggu->value)
            ->first();

        if ($member === null) {
            return;
        }

        $member->update([
            'status' => $keputusan === 'setuju'
                ? MemberApprovalStatus::Menyetujui->value
                : MemberApprovalStatus::Menolak->value,
        ]);

        Notification::make()
            ->title($keputusan === 'setuju' ? 'Keikutsertaan disetujui' : 'Keikutsertaan ditolak')
            ->success()
            ->send();
    }

    public function getViewData(): array
    {
        return [
            'invitations' => self::pending()->map(fn (ProposalMember $m): array => [
                'id' => $m->getKey(),
                'judul' => $m->proposal?->judul ?? '—',
                'ketua' => $m->proposal?->submitter?->name ?? '—',
                'tugas' => $m->tugas,
            ]),
        ];
    }
}
