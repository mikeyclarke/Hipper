<?php
declare(strict_types=1);

namespace Hipper\Onboarding;

use Hipper\Organization\Organization;
use Hipper\Organization\OrganizationSubdomainGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class ChooseTeamUrlController
{
    private $organization;
    private $subdomainGenerator;
    private $twig;

    public function __construct(
        Organization $organization,
        OrganizationSubdomainGenerator $subdomainGenerator,
        Twig_Environment $twig
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

    public function postAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        try {
            $this->organization->update(
                $person->getOrganizationId(),
                [
                    'subdomain' => $request->request->get('subdomain', '')
                ]
            );
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        return new JsonResponse(null, 200);
    }
}
