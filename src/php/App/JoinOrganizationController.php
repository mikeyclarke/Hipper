<?php
declare(strict_types=1);

namespace Lithos\App;

use Lithos\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Lithos\Validation\Exception\ValidationException;
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
        $parameters = json_decode($request->getContent(), true);
        $organization = $request->attributes->get('organization');

        try {
            list($person, $encodedPassword) = $this->personCreation->create($organization, $parameters);
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

        return new Response(null, 201);
    }
}
