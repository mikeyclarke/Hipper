<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Security\UntrustedInternalUriRedirector;
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
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;

    public function __construct(
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector
    ) {
        $this->twig = $twig;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
    }

    public function getAction(Request $request): Response
    {
        $returnTo = $request->query->get('return_to');

        $context = [
            'backLink' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
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
}
