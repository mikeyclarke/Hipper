<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\InviteController;
use Hipper\Invite\BulkInvitationCreator;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Person\PersonModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class InviteControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $bulkInvitationCreator;
    private $organizationUpdater;
    private $router;
    private $controller;

    public function setUp(): void
    {
        $this->bulkInvitationCreator = m::mock(BulkInvitationCreator::class);
        $this->organizationUpdater = m::mock(OrganizationUpdater::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->controller = new InviteController(
            $this->bulkInvitationCreator,
            $this->organizationUpdater,
            $this->router
        );
    }

    /**
     * @test
     */
    public function postAction()
    {
        $request = Request::create('https://usehipper.test/_/sign-up/invite-people');

        $requestBody = [
            'email_invites' => ['foo@bar.com', 'foo@baz.com', 'foo@qux.com'],
            'approved_email_domain_signup_allowed' => true,
            'approved_email_domains' => ['acme.com'],
        ];
        $request->request->add($requestBody);
        $organizationId = 'org-uuid';
        $currentUser = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $request->attributes->set('current_user', $currentUser);

        $organizationUpdateParameters = [
            'approved_email_domain_signup_allowed' => true,
            'approved_email_domains' => ['acme.com'],
        ];
        $invitationCreatorParameters = [
            'email_invites' => ['foo@bar.com', 'foo@baz.com', 'foo@qux.com'],
        ];
        $routeName = 'front_end.sign_up_flow.finalize';
        $url = '/sign-up/finalize';

        $this->createOrganizationUpdaterExpectation([$organizationId, $organizationUpdateParameters]);
        $this->createBulkInvitationCreatorExpectation([$currentUser, 'usehipper.test', $invitationCreatorParameters]);
        $this->createRouterExpectation([$routeName], $url);

        $result = $this->controller->postAction($request);
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

    private function createBulkInvitationCreatorExpectation($args)
    {
        $this->bulkInvitationCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args);
    }

    private function createOrganizationUpdaterExpectation($args)
    {
        $this->organizationUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }
}
