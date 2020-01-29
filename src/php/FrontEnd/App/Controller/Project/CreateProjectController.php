<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Security\UntrustedInternalUriRedirector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class CreateProjectController
{
    const PLACEHOLDER_PROJECT_NAMES = [
        'Growth',
        'Retention',
        'Marketing website'
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
            'html_title' => 'New project',
            'placeholderProjectName' => $this->getPlaceholderProjectName(),
        ];

        return new Response(
            $this->twig->render(
                'project/create_project.twig',
                $context
            )
        );
    }

    private function getPlaceholderProjectName(): string
    {
        $names = self::PLACEHOLDER_PROJECT_NAMES;
        return $names[array_rand($names)];
    }
}
