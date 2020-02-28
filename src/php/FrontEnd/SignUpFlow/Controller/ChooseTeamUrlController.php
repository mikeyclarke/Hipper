<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Hipper\Organization\Exception\OrganizationNotFoundException;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Hipper\Organization\OrganizationSubdomainGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ChooseTeamUrlController
{
    private OrganizationRepository $organizationRepository;
    private OrganizationSubdomainGenerator $subdomainGenerator;
    private Twig $twig;

    public function __construct(
        OrganizationRepository $organizationRepository,
        OrganizationSubdomainGenerator $subdomainGenerator,
        Twig $twig
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->subdomainGenerator = $subdomainGenerator;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $currentUser = $request->attributes->get('current_user');

        $result = $this->organizationRepository->findById($currentUser->getOrganizationId());
        if (null === $result) {
            throw new OrganizationNotFoundException;
        }
        $organization = OrganizationModel::createFromArray($result);

        if (OrganizationModel::DEFAULT_NAME === $organization->getName()) {
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
