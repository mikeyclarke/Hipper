<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Login\Login;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\TokenizedLogin\TokenizedLoginRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenizedLoginController
{
    private $login;
    private $personRepository;
    private $tokenizedLoginRepository;

    public function __construct(
        Login $login,
        PersonRepository $personRepository,
        TokenizedLoginRepository $tokenizedLoginRepository
    ) {
        $this->login = $login;
        $this->personRepository = $personRepository;
        $this->tokenizedLoginRepository = $tokenizedLoginRepository;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $token = $request->query->get('t');

        if (null == $token) {
            return new Response(null, 401);
        }

        $tokenizedLogin = $this->tokenizedLoginRepository->get($token);
        if (null === $tokenizedLogin) {
            return new Response(null, 401);
        }

        $result = $this->personRepository->findById($tokenizedLogin['person_id']);
        if (null === $result || $organization->getId() !== $result['organization_id']) {
            return new Response(null, 401);
        }

        $person = PersonModel::createFromArray($result);

        $this->tokenizedLoginRepository->deleteAllForPerson($person->getId());

        $session = $request->getSession();
        $this->login->populateSession($session, $person);

        return new RedirectResponse('/');
    }
}
