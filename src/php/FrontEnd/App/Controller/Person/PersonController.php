<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PersonController
{
    private Twig $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $currentUser = $request->attributes->get('current_user');
        $person = $request->attributes->get('person');

        $context = [
            'person' => $person,
            'person_is_current_user' => ($person->getId() === $currentUser->getId()),
        ];

        return new Response(
            $this->twig->render('person/person_index.twig', $context)
        );
    }
}
