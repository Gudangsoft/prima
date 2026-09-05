<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Enums\Role;
use App\Models\Announcement;
use App\Models\FundingDecision;
use App\Models\Proposal;
use App\Models\ProposalScheme;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $fundedStatuses = [
            ProposalStatus::Funded->value,
            ProposalStatus::InProgress->value,
            ProposalStatus::Reported->value,
            ProposalStatus::OutputValidated->value,
        ];

        return view('landing', [
            'branding' => Settings::branding(),
            'footer' => Settings::footer(),
            'stats' => [
                'usulan' => Proposal::query()->count(),
                'didanai' => Proposal::query()->whereIn('status', $fundedStatuses)->count(),
                'dosen' => User::query()->role(Role::Dosen->value)->count(),
                'reviewer' => User::query()->role(Role::Reviewer->value)->count(),
                'skema_aktif' => ProposalScheme::query()->where('aktif', true)->count(),
                'dana' => (float) FundingDecision::query()->sum('jumlah_dana'),
            ],
            'announcements' => Announcement::query()->published()->limit(3)->get(),
            'tahunAktif' => (int) now()->year,
        ]);
    }
}
