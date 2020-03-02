<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Organization;

use Hipper\Person\Storage\PersonUpdater as PersonStorageUpdater;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class OnboardingCompletedMiddleware
{
    private const COMPLETE_URL_SEARCH_PARAM = 'explorer';

    private PersonStorageUpdater $personStorageUpdater;

    public function __construct(
        PersonStorageUpdater $personStorageUpdater
    ) {
        $this->personStorageUpdater = $personStorageUpdater;
    }

    public function before(Request $request)
    {
        $currentUser = $request->attributes->get('current_user');
        $completeOnboarding = $request->query->getBoolean(self::COMPLETE_URL_SEARCH_PARAM);

        if ($currentUser->isOnboardingCompleted()) {
            return;
        }

        if ($completeOnboarding) {
            $this->personStorageUpdater->update($currentUser->getId(), ['onboarding_completed' => true]);
            return new RedirectResponse('/');
        }

        return new RedirectResponse('/welcome');
    }
}
