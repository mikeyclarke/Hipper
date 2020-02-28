<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Organization\OrganizationUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ChooseTeamUrlController
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
