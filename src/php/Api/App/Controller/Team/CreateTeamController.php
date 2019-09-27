<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Team;

use Hipper\Team\Team;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CreateTeamController
{
    private $team;
    private $router;

    public function __construct(
        Team $team,
        UrlGeneratorInterface $router
    ) {
        $this->team = $team;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $teamModel = $this->team->create($person, $request->request->all());
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

        return new JsonResponse([
            'team_url' => $this->router->generate('team.get', ['team_url_id' => $teamModel->getUrlId()]),
        ], 201);
    }
}
