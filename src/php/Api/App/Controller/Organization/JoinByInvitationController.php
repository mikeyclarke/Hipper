<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Login\Login;
use Hipper\Person\CreationStrategy\CreateFromInvite;
use Hipper\Person\Exception\InviteNotFoundException;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JoinByInvitationController
{
    use \Hipper\Api\ApiControllerTrait;

    private $personCreation;
    private $login;

    public function __construct(
        CreateFromInvite $personCreation,
        Login $login
    ) {
        $this->personCreation = $personCreation;
        $this->login = $login;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');
        $input = $request->request->all();

        try {
            list($person, $encodedPassword) = $this->personCreation->create($organization, $input);
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        } catch (InviteNotFoundException $e) {
            return new JsonResponse(
                [
                    'message' => 'Invite not found',
                ],
                400
            );
        }

        $session = $request->getSession();
        $this->login->populateSession($session, $person);

        return new JsonResponse(null, 201);
    }
}
