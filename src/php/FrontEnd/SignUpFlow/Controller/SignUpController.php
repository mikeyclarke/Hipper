<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class SignUpController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
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
}
