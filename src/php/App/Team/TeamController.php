<?php
declare(strict_types=1);

namespace Hipper\App\Team;

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
        $personIsInTeam = $request->attributes->get('personIsInTeam');

        $context = [
            'html_title' => $team->getName(),
            'team' => $team,
            'personIsInTeam' => $personIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_index.twig', $context)
        );
    }
}
