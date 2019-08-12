<?php
declare(strict_types=1);

namespace Hipper\Onboarding;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class VerifiedPersonMiddleware
{
    public function before(Request $request)
    {
        $person = $request->attributes->get('person');
        if (!$person->getEmailAddressVerified()) {
            return new RedirectResponse('/verify-identity');
        }
    }
}
