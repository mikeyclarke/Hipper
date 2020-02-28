<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Invite\BulkInvitationCreator;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InviteController
{
    use \Hipper\Api\ApiControllerTrait;

    private BulkInvitationCreator $bulkInvitationCreator;
    private OrganizationUpdater $organizationUpdater;

    public function __construct(
        BulkInvitationCreator $bulkInvitationCreator,
        OrganizationUpdater $organizationUpdater
    ) {
        $this->bulkInvitationCreator = $bulkInvitationCreator;
        $this->organizationUpdater = $organizationUpdater;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->organizationUpdater->update($currentUser->getOrganizationId(), $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }

    public function postEmailInvitesAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');

        try {
            $this->bulkInvitationCreator->create($currentUser, $request->getHost(), $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }
}
