<?php
declare(strict_types=1);

namespace Hipper\App;

use Hipper\Person\CreationStrategy\CreateFromInvite;
use Hipper\Person\Exception\InviteNotFoundException;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JoinByInvitationController
{
    private $personCreation;

    public function __construct(
        CreateFromInvite $personCreation
    ) {
        $this->personCreation = $personCreation;
    }

    public function getAction(Request $request)
    {
    }

    public function postAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $input = $request->request->all();

        try {
            list($person, $encodedPassword) = $this->personCreation->create($organization, $input);
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        } catch (InviteNotFoundException $e) {
            return new JsonResponse(
                [
                    'message' => 'Invite not found',
                ],
                400
            );
        }

        $session = $request->getSession();
        $session->set('_personId', $person->getId());
        $session->set('_password', $encodedPassword);

        return new JsonResponse(null, 201);
    }
}
