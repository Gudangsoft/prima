<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;

class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hero_slides.viewAny');
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('hero_slides.view');
    }

    public function create(User $user): bool
    {
        return $user->can('hero_slides.create');
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('hero_slides.update');
    }

    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('hero_slides.delete');
    }
}
