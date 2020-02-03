<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Organization\Organization;
use Hipper\TokenizedLogin\TokenizedLogin;
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
        $sessionName = $session->getName();
        $currentUser = $request->attributes->get('current_user');

        $organizationModel = $this->organization->get($currentUser->getOrganizationId());
        if (null === $organizationModel->getSubdomain()) {
            return new RedirectResponse('/sign-up/choose-team-url');
        }

        $token = $this->tokenizedLogin->create($currentUser);
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
