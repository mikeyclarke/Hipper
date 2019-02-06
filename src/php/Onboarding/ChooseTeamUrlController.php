<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Lithos\Organization\Organization;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class ChooseTeamUrlController
{
    private $organization;
    private $twig;

    public function __construct(
        Organization $organization,
        Twig_Environment $twig
    ) {
        $this->organization = $organization;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $context = [
            'domain' => $request->getHttpHost(),
        ];

        return new Response(
            $this->twig->render('onboarding/choose_team_url.twig', $context)
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $person = $request->attributes->get('person');

        $this->organization->update($person->getOrganizationId(), ['subdomain' => $content['subdomain']]);
    }
}
