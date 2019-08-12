<?php
declare(strict_types=1);

namespace Hipper\Onboarding;

use Hipper\Organization\Organization;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class NameTeamController
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
        return new Response(
            $this->twig->render('onboarding/name_team.twig')
        );
    }

    public function postAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        try {
            $this->organization->update($person->getOrganizationId(), ['name' => $request->request->get('name', '')]);
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
