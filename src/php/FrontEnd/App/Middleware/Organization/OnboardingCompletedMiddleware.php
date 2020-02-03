<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Organization;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class OnboardingCompletedMiddleware
{
    public function before(Request $request)
    {
        $currentUser = $request->attributes->get('current_user');
        if (!$currentUser->isOnboardingCompleted()) {
            return new RedirectResponse('/welcome');
        }
    }
}
