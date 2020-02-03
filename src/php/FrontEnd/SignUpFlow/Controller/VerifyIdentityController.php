<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class VerifyIdentityController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $currentUser = $request->attributes->get('current_user');

        $context = [
            'html_title' => 'Verify your email address',
            'current_user' => $currentUser,
        ];

        return new Response(
            $this->twig->render('onboarding/verify_identity.twig', $context)
        );
    }
}
