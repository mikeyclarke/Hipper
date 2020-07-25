<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Team;

use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class TeamModelFromUrlSlugMiddleware
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
        $currentUser = $request->attributes->get('current_user');

        $urlSlug = $request->attributes->get('team_url_slug');
        $result = $this->teamRepository->findByUrlSlug($organization->getId(), $urlSlug);
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $team = TeamModel::createFromArray($result);
        $request->attributes->set('team', $team);
        $this->twig->addGlobal('team', $team);

        $currentUserIsInTeam = $this->teamRepository->existsWithMappingForPerson($team->getId(), $currentUser->getId());
        $request->attributes->set('current_user_is_in_team', $currentUserIsInTeam);

        $this->twig->addGlobal(
            'search_action',
            $this->urlGeneratorInterface->generate(
                self::SEARCH_ROUTE,
                [
                    'subdomain' => $organization->getSubdomain(),
                    'team_url_slug' => $urlSlug,
                ]
            )
        );
    }
}
