<?php
declare(strict_types=1);

namespace Lithos\App;

use Lithos\Team\Team;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateTeamController
{
    private $team;

    public function __construct(
        Team $team
    ) {
        $this->team = $team;
    }

    public function postAction(Request $request): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $person = $request->attributes->get('person');

        try {
            $teamModel = $this->team->create($person, $parameters);
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

        return new Response(null, 201);
    }
}
