<?php
declare(strict_types=1);

namespace Lithos\Onboarding;

use Lithos\Person\PersonCreator;
use Lithos\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class SignUpController
{
    private $personCreator;
    private $twig;

    public function __construct(
        PersonCreator $personCreator,
        Twig_Environment $twig
    ) {
        $this->personCreator = $personCreator;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        return new Response(
            $this->twig->render('onboarding/signup.twig')
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        try {
            list($person, $encodedPassword) = $this->personCreator->create(json_decode($request->getContent(), true));
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
