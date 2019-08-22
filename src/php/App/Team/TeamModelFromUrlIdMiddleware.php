<?php
declare(strict_types=1);

namespace Hipper\App\Team;

use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamModelFromUrlIdMiddleware
{
    private $teamRepository;

    public function __construct(
        TeamRepository $teamRepository
    ) {
        $this->teamRepository = $teamRepository;
    }

    public function before(Request $request)
    {
        $organization = $request->attributes->get('organization');
        $person = $request->attributes->get('person');

        $urlId = $request->attributes->get('team_url_id');
        $result = $this->teamRepository->findByUrlId($organization->getId(), $urlId);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $team = TeamModel::createFromArray($result);
        $request->attributes->set('team', $team);

        $personIsInTeam = $this->teamRepository->existsWithMappingForPerson($team->getId(), $person->getId());
        $request->attributes->set('personIsInTeam', $personIsInTeam);
    }
}
