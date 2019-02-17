<?php
declare(strict_types=1);

namespace Lithos\App;

use Lithos\Person\PersonPasswordEncoder;
use Lithos\Person\PersonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    private $passwordEncoder;
    private $personRepository;

    public function __construct(
        PersonPasswordEncoder $passwordEncoder,
        PersonRepository $personRepository
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->personRepository = $personRepository;
    }

    public function postAction(Request $request): Response
    {
        $content = json_decode($request->getContent(), true);

        $email = $content['email_address'];
        $password = $content['password'];

        $person = $this->personRepository->findOneByEmailAddress($email);
        if (null === $person) {
            return new Response(null, 400);
        }

        if (!$this->passwordEncoder->isPasswordValid($person['password'], $password)) {
            return new Response(null, 400);
        }

        $session = $request->getSession();
        $session->set('_personId', $person['id']);
        $session->set('_password', $person['password']);

        return new RedirectResponse('/');
    }
}
