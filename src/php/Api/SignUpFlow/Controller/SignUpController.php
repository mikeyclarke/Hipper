<?php
declare(strict_types=1);

namespace Hipper\Api\SignUpFlow\Controller;

use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SignUpController
{
    private $personCreation;

    public function __construct(
        CreateFoundingMember $personCreation
    ) {
        $this->personCreation = $personCreation;
    }

    public function postAction(Request $request): JsonResponse
    {
        try {
            list($person, $encodedPassword) = $this->personCreation->create($request->request->all());
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

        $session = $request->getSession();
        $session->set('_personId', $person->getId());

        return new JsonResponse(null, 201);
    }
}
