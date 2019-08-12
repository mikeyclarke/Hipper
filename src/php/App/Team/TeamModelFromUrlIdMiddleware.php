<?php
declare(strict_types=1);

namespace Hipper\App\Team;

use Hipper\Team\TeamModelMapper;
use Hipper\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamModelFromUrlIdMiddleware
{
    private $teamModelMapper;
    private $teamRepository;

    public function __construct(
        TeamModelMapper $teamModelMapper,
        TeamRepository $teamRepository
    ) {
        $this->teamModelMapper = $teamModelMapper;
        $this->teamRepository = $teamRepository;
    }

    public function before(Request $request)
    {
        $organization = $request->attributes->get('organization');
        $person = $request->attributes->get('person');

        $urlId = $request->attributes->get('url_id');
        $result = $this->teamRepository->findByUrlId($organization->getId(), $urlId);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $team = $this->teamModelMapper->createFromArray($result);
        $request->attributes->set('team', $team);

        $personIsInTeam = $this->teamRepository->existsWithMappingForPerson($team->getId(), $person->getId());
        $request->attributes->set('personIsInTeam', $personIsInTeam);
    }
}