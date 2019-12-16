<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Login\Login;
use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JoinController
{
    use \Hipper\Api\ApiControllerTrait;

    private $personCreation;
    private $login;

    public function __construct(
        CreateFromApprovedEmailDomain $personCreation,
        Login $login
    ) {
        $this->personCreation = $personCreation;
        $this->login = $login;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            list($person, $encodedPassword) = $this->personCreation->create($organization, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $session = $request->getSession();
        $this->login->populateSession($session, $person);

        return new JsonResponse(null, 201);
    }
}
