<?php
declare(strict_types=1);

namespace Hipper\App\Team;

use Hipper\Team\Team;
use Hipper\Team\TeamDescriptionSuggestor;
use Hipper\Team\TeamValidator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

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

    private $team;
    private $teamDescriptionSuggestor;
    private $teamValidator;
    private $twig;
    private $router;

    public function __construct(
        Team $team,
        TeamDescriptionSuggestor $teamDescriptionSuggestor,
        TeamValidator $teamValidator,
        Twig_Environment $twig,
        UrlGeneratorInterface $router
    ) {
        $this->team = $team;
        $this->teamDescriptionSuggestor = $teamDescriptionSuggestor;
        $this->teamValidator = $teamValidator;
        $this->twig = $twig;
        $this->router = $router;
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

    public function suggestAction(Request $request): JsonResponse
    {
        $organization = $request->attributes->get('organization');

        try {
            $this->teamValidator->validate($request->request->all(), $organization->getId(), true);
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        $result = $this->teamDescriptionSuggestor->suggest($organization->getName(), $request->request->get('name'));
        return new JsonResponse(
            [
                'suggested_description' => $result,
            ]
        );
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            $teamModel = $this->team->create($person, $request->request->all());
        } catch (ValidationException $e) {
            return new JsonResponse(
                [
                    'name' => $e->getName(),
                    'message' => $e->getMessage(),
                    'violations' => $e->getViolations(),
                ],
                400
            );
        }

        return new JsonResponse([
            'team_url' => $this->router->generate('team.get', ['url_id' => $teamModel->getUrlId()]),
        ], 201);
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
