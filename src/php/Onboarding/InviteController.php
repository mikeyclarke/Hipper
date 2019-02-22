<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Lithos\Invite\BulkInvitationCreator;
use Lithos\Organization\Organization;
use Lithos\Validation\Constraints\NotPersonalEmailDomain;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validation;
use Twig_Environment;

class InviteController
{
    private $bulkInvitationCreator;
    private $organization;
    private $twig;

    public function __construct(
        BulkInvitationCreator $bulkInvitationCreator,
        Organization $organization,
        Twig_Environment $twig
    ) {
        $this->bulkInvitationCreator = $bulkInvitationCreator;
        $this->organization = $organization;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $person = $request->attributes->get('person');
        $email = $person->getEmailAddress();
        $emailDomain = substr($email, strrpos($email, '@') + 1);

        $context = [
            'isApprovedEmailSignupSupported' => !$this->isEmailDomainPersonal($emailDomain),
            'emailDomain' => $emailDomain,
        ];

        return new Response(
            $this->twig->render('onboarding/invite.twig', $context)
        );
    }

    public function postAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        try {
            $this->organization->update($person->getOrganizationId(), $request->request->all());
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

        return new JsonResponse(null, 200);
    }

    public function postEmailInvitesAction(Request $request): Response
    {
        $person = $request->attributes->get('person');

        try {
            $this->bulkInvitationCreator->create($person, $request->getHost(), $request->request->all());
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

        return new JsonResponse(null, 200);
    }

    private function isEmailDomainPersonal(string $emailDomain): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($emailDomain, [new NotPersonalEmailDomain]);
        return count($violations) > 0;
    }
}
