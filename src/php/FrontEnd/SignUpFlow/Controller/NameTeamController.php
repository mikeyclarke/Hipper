<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\SignUpFlow\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class NameTeamController
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
            'html_title' => 'Name your organization',
        ];

        return new Response(
            $this->twig->render('onboarding/name_team.twig', $context)
        );
    }
}
