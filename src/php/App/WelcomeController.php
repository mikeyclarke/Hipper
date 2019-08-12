<?php
declare(strict_types=1);

namespace Hipper\App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class WelcomeController
{
    private $twig;

    public function __construct(
        Twig_Environment $twig
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
