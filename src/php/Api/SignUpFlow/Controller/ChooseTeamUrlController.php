<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Organization\Organization;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ChooseTeamUrlController
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
            $this->organization->update(
                $person->getOrganizationId(),
                [
                    'subdomain' => $request->request->get('subdomain', '')
                ]
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }
}
