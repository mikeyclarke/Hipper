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

    private Organization $organization;

    public function __construct(
        Organization $organization
    ) {
        $this->organization = $organization;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->organization->update(
                $currentUser->getOrganizationId(),
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
