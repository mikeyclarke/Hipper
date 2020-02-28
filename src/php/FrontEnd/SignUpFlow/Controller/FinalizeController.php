<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Organization\Exception\OrganizationNotFoundException;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Hipper\TokenizedLogin\TokenizedLoginCreator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FinalizeController
{
    private OrganizationRepository $organizationRepository;
    private TokenizedLoginCreator $tokenizedLoginCreator;

    public function __construct(
        OrganizationRepository $organizationRepository,
        TokenizedLoginCreator $tokenizedLoginCreator
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->tokenizedLoginCreator = $tokenizedLoginCreator;
    }

    public function getAction(Request $request): RedirectResponse
    {
        $session = $request->getSession();
        $sessionName = $session->getName();
        $currentUser = $request->attributes->get('current_user');

        $result = $this->organizationRepository->findById($currentUser->getOrganizationId());
        if (null === $result) {
            throw new OrganizationNotFoundException;
        }
        $organization = OrganizationModel::createFromArray($result);

        if (null === $organization->getSubdomain()) {
            return new RedirectResponse('/sign-up/choose-team-url');
        }

        $token = $this->tokenizedLoginCreator->create($currentUser);
        $session->invalidate();

        $response = new RedirectResponse(
            sprintf(
                'https://%s.%s/tokenized-login?t=%s',
                $organization->getSubdomain(),
                $request->getHttpHost(),
                $token
            )
        );
        $response->headers->clearCookie($sessionName);
        return $response;
    }
}
