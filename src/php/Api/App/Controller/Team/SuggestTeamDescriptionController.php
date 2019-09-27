<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Team;

use Hipper\Team\TeamDescriptionSuggestor;
use Hipper\Team\TeamValidator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SuggestTeamDescriptionController
{
    private $teamDescriptionSuggestor;
    private $teamValidator;

    public function __construct(
        TeamDescriptionSuggestor $teamDescriptionSuggestor,
        TeamValidator $teamValidator
    ) {
        $this->teamDescriptionSuggestor = $teamDescriptionSuggestor;
        $this->teamValidator = $teamValidator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            $this->teamValidator->validate($request->request->all(), $organization->getId(), true);
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

        $result = $this->teamDescriptionSuggestor->suggest($organization->getName(), $request->request->get('name'));
        return new JsonResponse(
            [
                'suggested_description' => $result,
            ]
        );
    }
}
