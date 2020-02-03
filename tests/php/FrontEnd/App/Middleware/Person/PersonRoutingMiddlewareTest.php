<?php
declare(strict_types=1);

namespace Hipper\Tests\FrontEnd\App\Middleware;

use Hipper\FrontEnd\App\Middleware\Person\PersonRoutingMiddleware;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonRoutingMiddlewareTest extends TestCase
{
    private $personRepository;
    private $router;
    private $middleware;

    public function setUp(): void
    {
        $this->personRepository = m::mock(PersonRepository::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->middleware = new PersonRoutingMiddleware(
            $this->personRepository,
            $this->router
        );
    }

    /**
     * @test
     */
    public function noPersonFoundWithUrlId()
    {
        $username = '@christopher_pike';
        $urlId = 'url-id';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $request = new Request([], [], [
            'organization' => $organization,
            'url_id' => $urlId,
            'username' => $username,
        ]);

        $this->createPersonRepositoryExpectation([$urlId, $organizationId], null);

        $this->expectException(NotFoundHttpException::class);

        $this->middleware->before($request);
    }

    /**
     * @test
     */
    public function slugIsCurrent()
    {
        $username = '@christopher_pike';
        $urlId = 'url-id';
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $request = new Request([], [], [
            'organization' => $organization,
            'url_id' => $urlId,
            'username' => $username,
        ]);

        $personResult = [
            'id' => 'person-uuid',
            'previous_usernames' => null,
            'url_id' => $urlId,
            'username' => $username,
        ];

        $this->createPersonRepositoryExpectation([$urlId, $organizationId], $personResult);

        $this->middleware->before($request);
        $this->assertInstanceOf(PersonModel::class, $request->attributes->get('person'));
    }

    private function createPersonRepositoryExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('findOneByUrlId')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
