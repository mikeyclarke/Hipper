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
    use \Hipper\Api\ApiControllerTrait;

    const TEAM_ROUTE_NAME = 'front_end.app.team.show';

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
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            $teamModel = $this->team->create($currentUser, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse([
            'team_url' => $this->router->generate(
                self::TEAM_ROUTE_NAME,
                [
                    'subdomain' => $organization->getSubdomain(),
                    'team_url_id' => $teamModel->getUrlId(),
                ]
            ),
        ], 201);
    }
}
