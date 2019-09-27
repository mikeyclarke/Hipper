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
    private $organization;
    private $subdomainGenerator;
    private $twig;

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
        $person = $request->attributes->get('person');
        $organization = $this->organization->get($person->getOrganizationId());

        if (Organization::DEFAULT_NAME === $organization->getName()) {
            return new RedirectResponse('/name-team');
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
