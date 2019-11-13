<?php
declare(strict_types=1);

namespace Hipper\Login;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use UnexpectedValueException;

class Login
{
    private $loginValidator;
    private $passwordEncoder;
    private $personRepository;

    public function __construct(
        LoginValidator $loginValidator,
        PersonPasswordEncoder $passwordEncoder,
        PersonRepository $personRepository
    ) {
        $this->loginValidator = $loginValidator;
        $this->passwordEncoder = $passwordEncoder;
        $this->personRepository = $personRepository;
    }

    public function login(OrganizationModel $organization, array $parameters, SessionInterface $session): void
    {
        $person = null;
        if (isset($parameters['email_address'])) {
            $person = $this->personRepository->findOneByEmailAddress(
                $parameters['email_address'],
                $organization->getId()
            );
        }

        $this->loginValidator->validate($parameters);

        if (null === $person) {
            throw new InvalidCredentialsException;
        }

        if (!isset($person['password'])) {
            throw new UnexpectedValueException('Expected person array to contain a “password” property');
        }

        if (!$this->passwordEncoder->isPasswordValid($person['password'], $parameters['password'])) {
            throw new InvalidCredentialsException;
        }

        $this->populateSession($session, $person);
    }

    public function populateSession(SessionInterface $session, array $person): void
    {
        $session->set('_personId', $person['id']);
    }
}
