<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Invite\BulkInvitationCreator;
use Hipper\Organization\Organization;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class InviteController
{
    use \Hipper\Api\ApiControllerTrait;

    private $bulkInvitationCreator;
    private $organization;

    public function __construct(
        BulkInvitationCreator $bulkInvitationCreator,
        Organization $organization
    ) {
        $this->bulkInvitationCreator = $bulkInvitationCreator;
        $this->organization = $organization;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $this->organization->update($person->getOrganizationId(), $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }

    public function postEmailInvitesAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $this->bulkInvitationCreator->create($person, $request->getHost(), $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        return new JsonResponse(null, 200);
    }
}
