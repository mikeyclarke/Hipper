<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\ChooseOrganizationUrlController;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Person\PersonModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChooseOrganizationUrlControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $organizationUpdater;
    private $router;
    private $chooseOrganizationUrlController;
    private $request;

    public function setUp(): void
    {
        $this->organizationUpdater = m::mock(OrganizationUpdater::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->chooseOrganizationUrlController = new ChooseOrganizationUrlController(
            $this->organizationUpdater,
            $this->router
        );

        $this->request = new Request();
    }

    /**
     * @test
     */
    public function postAction()
    {
        $subdomain = 'Acme';
        $requestBody = [
            'subdomain' => $subdomain,
        ];
        $this->request->request->add($requestBody);
        $organizationId = 'org-uuid';
        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $this->request->attributes->set('current_user', $person);

        $routeName = 'front_end.sign_up_flow.finalize';
        $url = '/sign-up/begin';

        $this->createOrganizationUpdaterExpectation([$organizationId, ['subdomain' => $subdomain]]);
        $this->createRouterExpectation([$routeName], $url);

        $result = $this->chooseOrganizationUrlController->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertIsArray(json_decode($result->getContent(), true));
        $this->assertEquals($url, json_decode($result->getContent(), true)['url']);
    }

    private function createRouterExpectation($args, $result)
    {
        $this->router
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createOrganizationUpdaterExpectation($args)
    {
        $this->organizationUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }
}
