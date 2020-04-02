<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Invite\BulkInvitationCreator;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InviteController
{
    private const ORGANIZATION_KEYS = ['approved_email_domain_signup_allowed', 'approved_email_domains'];
    private const INVITE_KEYS = ['email_invites'];
    private const FINALIZE_ROUTE = 'front_end.sign_up_flow.finalize';

    use \Hipper\Api\ApiControllerTrait;

    private BulkInvitationCreator $bulkInvitationCreator;
    private OrganizationUpdater $organizationUpdater;
    private UrlGeneratorInterface $router;

    public function __construct(
        BulkInvitationCreator $bulkInvitationCreator,
        OrganizationUpdater $organizationUpdater,
        UrlGeneratorInterface $router
    ) {
        $this->bulkInvitationCreator = $bulkInvitationCreator;
        $this->organizationUpdater = $organizationUpdater;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $requestBody = $request->request->all();

        $organizationParameters = array_intersect_key($requestBody, array_flip(self::ORGANIZATION_KEYS));
        $inviteParameters = array_intersect_key($requestBody, array_flip(self::INVITE_KEYS));

        try {
            $this->organizationUpdater->update($currentUser->getOrganizationId(), $organizationParameters);
            $this->bulkInvitationCreator->create($currentUser, $request->getHost(), $inviteParameters);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->router->generate(self::FINALIZE_ROUTE);
        return new JsonResponse(['url' => $url], 200);
    }
}
