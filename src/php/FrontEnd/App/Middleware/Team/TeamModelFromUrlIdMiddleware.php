<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Team;

use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class TeamModelFromUrlIdMiddleware
{
    const SEARCH_ROUTE = 'front_end.app.team.search';

    private $teamRepository;
    private $twig;
    private $urlGeneratorInterface;

    public function __construct(
        TeamRepository $teamRepository,
        Twig $twig,
        UrlGeneratorInterface $urlGeneratorInterface
    ) {
        $this->teamRepository = $teamRepository;
        $this->twig = $twig;
        $this->urlGeneratorInterface = $urlGeneratorInterface;
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
        $this->twig->addGlobal('team', $team);

        $personIsInTeam = $this->teamRepository->existsWithMappingForPerson($team->getId(), $person->getId());
        $request->attributes->set('personIsInTeam', $personIsInTeam);

        $this->twig->addGlobal(
            'search_action',
            $this->urlGeneratorInterface->generate(
                self::SEARCH_ROUTE,
                [
                    'subdomain' => $organization->getSubdomain(),
                    'team_url_id' => $urlId,
                ]
            )
        );
    }
}
