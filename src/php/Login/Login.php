<?php
declare(strict_types=1);

namespace Hipper\Login;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
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
        $result = null;
        if (isset($parameters['email_address'])) {
            $result = $this->personRepository->findOneByEmailAddress(
                $parameters['email_address'],
                $organization->getId(),
                ['password']
            );
        }

        $this->loginValidator->validate($parameters);

        if (null === $result) {
            throw new InvalidCredentialsException;
        }

        if (!isset($result['password'])) {
            throw new UnexpectedValueException('Expected result array to contain a “password” property');
        }

        if (!$this->passwordEncoder->isPasswordValid($result['password'], $parameters['password'])) {
            throw new InvalidCredentialsException;
        }

        $person = PersonModel::createFromArray($result);
        $this->populateSession($session, $person);
    }

    public function populateSession(SessionInterface $session, PersonModel $person): void
    {
        $session->set('_personId', $person->getId());
        $session->migrate();
    }
}
