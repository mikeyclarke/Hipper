<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Person\PersonRepository;
use Hipper\TokenizedLogin\TokenizedLoginRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenizedLoginController
{
    private $personRepository;
    private $tokenizedLoginRepository;

    public function __construct(
        PersonRepository $personRepository,
        TokenizedLoginRepository $tokenizedLoginRepository
    ) {
        $this->personRepository = $personRepository;
        $this->tokenizedLoginRepository = $tokenizedLoginRepository;
    }

    public function getAction(Request $request): Response
    {
        $token = $request->query->get('t');

        if (null == $token) {
            return new Response(null, 401);
        }

        $tokenizedLogin = $this->tokenizedLoginRepository->get($token);
        if (null === $tokenizedLogin) {
            return new Response(null, 401);
        }

        $person = $this->personRepository->findById($tokenizedLogin['person_id']);
        $this->tokenizedLoginRepository->deleteAllForPerson($person['id']);

        $session = $request->getSession();
        $session->set('_personId', $person['id']);
        $session->set('_password', $person['password']);

        return new RedirectResponse('/');
    }
}
