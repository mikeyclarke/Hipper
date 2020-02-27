<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Organization\Organization;
use Hipper\TokenizedLogin\TokenizedLoginCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FinalizeController
{
    private $organization;
    private $tokenizedLoginCreator;

    public function __construct(
        Organization $organization,
        TokenizedLoginCreator $tokenizedLoginCreator
    ) {
        $this->organization = $organization;
        $this->tokenizedLoginCreator = $tokenizedLoginCreator;
    }

    public function getAction(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $sessionName = $session->getName();
        $currentUser = $request->attributes->get('current_user');

        $organizationModel = $this->organization->get($currentUser->getOrganizationId());
        if (null === $organizationModel->getSubdomain()) {
            return new RedirectResponse('/sign-up/choose-team-url');
        }

        $token = $this->tokenizedLoginCreator->create($currentUser);
        $session->invalidate();

        $response = new RedirectResponse(
            sprintf(
                'https://%s.%s/tokenized-login?t=%s',
                $organizationModel->getSubdomain(),
                $request->getHttpHost(),
                $token
            )
        );
        $response->headers->clearCookie($sessionName);
        return $response;
    }
}
