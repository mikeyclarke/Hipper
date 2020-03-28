<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class SignUpController
{
    private const TERMS_URL = '/terms-of-use';

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
            'terms_url' => self::TERMS_URL,
        ];

        return new Response(
            $this->twig->render('sign_up_flow/sign_up.twig', $context)
        );
    }
}
