<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Document\DocumentModel;
use Hipper\Document\Event\DocumentCreatedEvent;
use Hipper\Document\Event\DocumentSubscriber;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentSubscriberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $activityCreator;
    private $subscriber;

    public function setUp(): void
    {
        $this->activityCreator = m::mock(ActivityCreator::class);

        $this->subscriber = new DocumentSubscriber(
            $this->activityCreator
        );
    }

    /**
     * @test
     */
    public function onDocumentCreatedInOrganization()
    {
        $documentId = 'document-uuid';
        $documentName = 'Welcome to Engineering';
        $documentDescription = null;
        $documentDeducedDescription = null;
        $documentUrlId = 'abcd1234';
        $documentUrlSlug = 'welcome-to-engineering';
        $organizationName = 'Acme';

        $document = DocumentModel::createFromArray([
            'id' => $documentId,
            'name' => $documentName,
            'description' => $documentDescription,
            'deduced_description' => $documentDeducedDescription,
            'url_id' => $documentUrlId,
            'url_slug' => $documentUrlSlug,
        ]);
        $organization = OrganizationModel::createFromArray([
            'name' => $organizationName,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $documentUrlSlug,
            'url_id' => $documentUrlId,
        ]);
        $actor = new PersonModel;

        $event = new DocumentCreatedEvent(
            $document,
            $organization,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'organization',
            'knowledgebase_owner_name' => $organizationName,
            'knowledgebase_owner_url_slug' => null,
            'document_name' => $documentName,
            'document_description' => 'This document doesn’t have a description yet',
            'document_url_id' => $documentUrlId,
            'document_route' => $documentUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'document_created', $properties, $documentId, null, null, null]
        );

        $this->subscriber->onDocumentCreated($event);
    }

    /**
     * @test
     */
    public function onDocumentCreatedInTeam()
    {
        $documentId = 'document-uuid';
        $documentName = 'Welcome to Engineering';
        $documentDescription = null;
        $documentDeducedDescription = 'Hello, and congrats on joining Acme’s Engineering team!';
        $documentUrlId = 'abcd1234';
        $documentUrlSlug = 'welcome-to-engineering';
        $teamId = 'team-uuid';
        $teamName = 'Engineering';
        $teamUrlSlug = 'engineering';

        $document = DocumentModel::createFromArray([
            'id' => $documentId,
            'name' => $documentName,
            'description' => $documentDescription,
            'deduced_description' => $documentDeducedDescription,
            'url_id' => $documentUrlId,
            'url_slug' => $documentUrlSlug,
        ]);
        $team = TeamModel::createFromArray([
            'id' => $teamId,
            'name' => $teamName,
            'url_slug' => $teamUrlSlug,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $documentUrlSlug,
            'url_id' => $documentUrlId,
        ]);
        $actor = new PersonModel;

        $event = new DocumentCreatedEvent(
            $document,
            $team,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'team',
            'knowledgebase_owner_name' => $teamName,
            'knowledgebase_owner_url_slug' => $teamUrlSlug,
            'document_name' => $documentName,
            'document_description' => $documentDeducedDescription,
            'document_url_id' => $documentUrlId,
            'document_route' => $documentUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'document_created', $properties, $documentId, null, $teamId, null]
        );

        $this->subscriber->onDocumentCreated($event);
    }

    /**
     * @test
     */
    public function onDocumentCreatedInProject()
    {
        $documentId = 'document-uuid';
        $documentName = 'Acme Starter Guide';
        $documentDescription = 'The beginner guide for new members of the Acme team';
        $documentDeducedDescription = 'Hello, and welcome to Acme; we’re stoked to have you onboard!';
        $documentUrlId = 'abcd1234';
        $documentUrlSlug = 'acme-starter-guide';
        $projectId = 'project-uuid';
        $projectName = 'HR docs';
        $projectUrlSlug = 'hr-docs';

        $document = DocumentModel::createFromArray([
            'id' => $documentId,
            'name' => $documentName,
            'description' => $documentDescription,
            'deduced_description' => $documentDeducedDescription,
            'url_id' => $documentUrlId,
            'url_slug' => $documentUrlSlug,
        ]);
        $project = ProjectModel::createFromArray([
            'id' => $projectId,
            'name' => $projectName,
            'url_slug' => $projectUrlSlug,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $documentUrlSlug,
            'url_id' => $documentUrlId,
        ]);
        $actor = new PersonModel;

        $event = new DocumentCreatedEvent(
            $document,
            $project,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'project',
            'knowledgebase_owner_name' => $projectName,
            'knowledgebase_owner_url_slug' => $projectUrlSlug,
            'document_name' => $documentName,
            'document_description' => $documentDescription,
            'document_url_id' => $documentUrlId,
            'document_route' => $documentUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'document_created', $properties, $documentId, null, null, $projectId]
        );

        $this->subscriber->onDocumentCreated($event);
    }

    private function createActivityCreatorExpectation($args)
    {
        $this->activityCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args);
    }
}
