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
        $person = $request->attributes->get('person');

        $context = [
            'html_title' => 'Verify your email address',
            'person' => $person,
        ];

        return new Response(
            $this->twig->render('onboarding/verify_identity.twig', $context)
        );
    }
}
