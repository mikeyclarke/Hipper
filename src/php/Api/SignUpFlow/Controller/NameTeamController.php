<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Organization\Organization;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NameTeamController
{
    use \Hipper\Api\ApiControllerTrait;

    private $organization;

    public function __construct(
        Organization $organization
    ) {
        $this->organization = $organization;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $this->organization->update($person->getOrganizationId(), ['name' => $request->request->get('name', '')]);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }
}
