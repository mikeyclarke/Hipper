<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Lithos\Organization\Organization;
use Lithos\TokenizedLogin\TokenizedLogin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FinalizeController
{
    private $organization;
    private $tokenizedLogin;

    public function __construct(
        Organization $organization,
        TokenizedLogin $tokenizedLogin
    ) {
        $this->organization = $organization;
        $this->tokenizedLogin = $tokenizedLogin;
    }

    public function getAction(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $person = $request->attributes->get('person');

        $organizationModel = $this->organization->get($person->getOrganizationId());
        if (null === $organizationModel->getSubdomain()) {
            return new RedirectResponse('/choose-team-url');
        }

        $token = $this->tokenizedLogin->create($person);
        $session->invalidate();

        return new RedirectResponse(
            sprintf('https://%s.%s?t=%s', $organizationModel->getSubdomain(), $request->getHttpHost(), $token)
        );
    }
}
