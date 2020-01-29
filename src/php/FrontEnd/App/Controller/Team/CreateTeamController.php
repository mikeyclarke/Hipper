<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateTeamController
{
    const PLACEHOLDER_TEAM_NAMES = [
        'People Ops',
        'Design',
        'Marketing',
        'Quality Assurance',
        'Dev Ops',
        'Engineering',
        'Tech Ops',
        'Product',
        'Finance',
        'Business Development',
        'Legal',
        'Sales',
    ];

    private Twig $twig;

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $context = [
            'backLink' => $this->getBackLink($request),
            'bodyClassList' => [
                'l-sheet',
            ],
            'html_title' => 'New team',
            'placeholderTeamName' => $this->getPlaceholderTeamName(),
        ];

        return new Response(
            $this->twig->render(
                'team/create_team.twig',
                $context
            )
        );
    }

    private function getPlaceholderTeamName(): string
    {
        $names = self::PLACEHOLDER_TEAM_NAMES;
        return $names[array_rand($names)];
    }

    private function getBackLink(Request $request): string
    {
        if (!$request->server->has('HTTP_REFERER')) {
            return '/';
        }

        $referrer = $request->server->get('HTTP_REFERER');
        $origin = $request->getSchemeAndHttpHost();
        if (substr($referrer, 0, strlen($origin)) !== $origin) {
            return '/';
        }

        $path = substr($referrer, strlen($origin));
        if ($path === 'teams/new') {
            return '/';
        }

        return $path;
    }
}
