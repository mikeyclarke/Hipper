<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class WelcomeController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        return new Response(
            $this->twig->render(
                'welcome.twig'
            )
        );
    }
}