<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Organization\OrganizationUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class NameTeamController
{
    use \Hipper\Api\ApiControllerTrait;

    private OrganizationUpdater $organizationUpdater;

    public function __construct(
        OrganizationUpdater $organizationUpdater
    ) {
        $this->organizationUpdater = $organizationUpdater;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->organizationUpdater->update(
                $currentUser->getOrganizationId(),
                ['name' => $request->request->get('name', '')]
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }
}
