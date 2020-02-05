<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Person;

use Hipper\Person\PersonRepository;
use Hipper\Person\PeopleListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class PeopleController
{
    private PersonRepository $personRepository;
    private PeopleListFormatter $peopleListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        PersonRepository $personRepository,
        PeopleListFormatter $peopleListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->personRepository = $personRepository;
        $this->peopleListFormatter = $peopleListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $displayTimeZone = $this->timeZoneFromRequest->get($request);

        $people = $this->personRepository->getAll($organization->getId(), 'name', 'ASC');
        $peopleList = $this->peopleListFormatter->format($organization, $people, $displayTimeZone);

        $context = [
            'html_title' => 'People',
            'people_list' => $peopleList,
        ];

        return new Response(
            $this->twig->render('person/people_list.twig', $context)
        );
    }
}
