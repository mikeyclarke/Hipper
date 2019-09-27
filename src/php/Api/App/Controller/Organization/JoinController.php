<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JoinController
{
    private $personCreation;

    public function __construct(
        CreateFromApprovedEmailDomain $personCreation
    ) {
        $this->personCreation = $personCreation;
    }

    public function postAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            list($person, $encodedPassword) = $this->personCreation->create($organization, $request->request->all());
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
        $session->set('_password', $encodedPassword);

        return new JsonResponse(null, 201);
    }
}
