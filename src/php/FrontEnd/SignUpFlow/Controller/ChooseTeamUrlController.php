<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Organization\Organization;
use Hipper\Organization\OrganizationSubdomainGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ChooseTeamUrlController
{
    private Organization $organization;
    private OrganizationSubdomainGenerator $subdomainGenerator;
    private Twig $twig;

    public function __construct(
        Organization $organization,
        OrganizationSubdomainGenerator $subdomainGenerator,
        Twig $twig
    ) {
        $this->organization = $organization;
        $this->subdomainGenerator = $subdomainGenerator;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $this->organization->get($currentUser->getOrganizationId());

        if (Organization::DEFAULT_NAME === $organization->getName()) {
            return new RedirectResponse('/sign-up/name-team');
        }

        $context = [
            'domain' => $request->getHttpHost(),
            'html_title' => 'Organization URL',
            'subdomainSuggestion' => $this->subdomainGenerator->generate($organization->getName()),
        ];

        return new Response(
            $this->twig->render('onboarding/choose_team_url.twig', $context)
        );
    }
}
