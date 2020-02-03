<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamController
{
    private $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');

        $context = [
            'html_title' => $team->getName(),
            'team' => $team,
            'current_user_is_in_team' => $currentUserIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_index.twig', $context)
        );
    }
}
