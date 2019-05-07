<?php
declare(strict_types=1);

namespace Lithos\App\Team;

use Lithos\Team\TeamModelMapper;
use Lithos\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig_Environment;

class TeamController
{
    private $teamModelMapper;
    private $teamRepository;
    private $twig;

    public function __construct(
        TeamModelMapper $teamModelMapper,
        TeamRepository $teamRepository,
        Twig_Environment $twig
    ) {
        $this->teamModelMapper = $teamModelMapper;
        $this->teamRepository = $teamRepository;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $urlId = $request->attributes->get('url_id');

        $result = $this->teamRepository->findByUrlId($organization->getId(), $urlId);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $team = $this->teamModelMapper->createFromArray($result);
        $person = $request->attributes->get('person');

        $context = [
            'team' => $team,
            'personIsInTeam' => $this->teamRepository->existsWithMappingForPerson($team->getId(), $person->getId()),
        ];

        return new Response(
            $this->twig->render('team/team_index.twig', $context)
        );
    }
}
