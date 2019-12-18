<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class LoginController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $redirect = $request->query->get('r');

        $context = [
            'html_title' => sprintf('Sign in to %s', $organization->getName()),
            'redirect' => $redirect,
        ];

        return new Response(
            $this->twig->render('organization/login.twig', $context)
        );
    }
}
