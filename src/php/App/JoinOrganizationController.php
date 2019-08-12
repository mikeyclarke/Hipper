<?php
declare(strict_types=1);

namespace Hipper\App;

use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JoinOrganizationController
{
    private $personCreation;

    public function __construct(
        CreateFromApprovedEmailDomain $personCreation
    ) {
        $this->personCreation = $personCreation;
    }

    public function postAction(Request $request): Response
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
