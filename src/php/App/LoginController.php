<?php
declare(strict_types=1);

namespace Lithos\App;

use Lithos\Person\PersonPasswordEncoderFactory;
use Lithos\Person\PersonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    private $personPasswordEncoderFactory;
    private $personRepository;

    public function __construct(
        PersonPasswordEncoderFactory $personPasswordEncoderFactory,
        PersonRepository $personRepository
    ) {
        $this->personPasswordEncoderFactory = $personPasswordEncoderFactory;
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

        $passwordEncoder = $this->personPasswordEncoderFactory->create();
        if (!$passwordEncoder->isPasswordValid($person['password'], $password, null)) {
            return new Response(null, 400);
        }

        $session = $request->getSession();
        $session->set('_personId', $person['id']);
        $session->set('_password', $person['password']);

        return new RedirectResponse('/');
    }
}
