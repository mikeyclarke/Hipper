<?php
declare(strict_types=1);

namespace Hipper\Onboarding;

use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class SignUpController
{
    private $personCreation;
    private $twig;

    public function __construct(
        CreateFoundingMember $personCreation,
        Twig $twig
    ) {
        $this->personCreation = $personCreation;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $context = [
            'html_title' => 'Sign-up',
        ];

        return new Response(
            $this->twig->render('onboarding/signup.twig', $context)
        );
    }

    public function postAction(Request $request): Response
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
        $session->set('_password', $encodedPassword);

        return new JsonResponse(null, 201);
    }
}
