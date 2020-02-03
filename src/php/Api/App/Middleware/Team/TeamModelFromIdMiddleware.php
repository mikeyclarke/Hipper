<?php
declare(strict_types=1);

namespace Hipper\Api\App\Middleware\Team;

use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TeamModelFromIdMiddleware
{
    private TeamRepository $teamRepository;

    public function __construct(
        TeamRepository $teamRepository
    ) {
        $this->teamRepository = $teamRepository;
    }

    public function before(Request $request)
    {
        $organization = $request->attributes->get('organization');

        $id = $request->attributes->get('team_id');
        $result = $this->teamRepository->findById($organization->getId(), $id);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $team = TeamModel::createFromArray($result);
        $request->attributes->set('team', $team);
    }
}
