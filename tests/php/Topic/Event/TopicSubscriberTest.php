<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\Event\TopicCreatedEvent;
use Hipper\Topic\Event\TopicSubscriber;
use Hipper\Topic\TopicModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TopicSubscriberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $activityCreator;
    private $subscriber;

    public function setUp(): void
    {
        $this->activityCreator = m::mock(ActivityCreator::class);

        $this->subscriber = new TopicSubscriber(
            $this->activityCreator
        );
    }

    /**
     * @test
     */
    public function onTopicCreatedInOrganization()
    {
        $topicId = 'topic-uuid';
        $topicName = 'Coding standards';
        $topicDescription = null;
        $topicUrlId = 'abcd1234';
        $topicUrlSlug = 'coding-standards';
        $organizationName = 'Acme';

        $topic = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => $topicName,
            'description' => $topicDescription,
            'url_id' => $topicUrlId,
            'url_slug' => $topicUrlSlug,
        ]);
        $organization = OrganizationModel::createFromArray([
            'name' => $organizationName,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $topicUrlSlug,
            'url_id' => $topicUrlId,
        ]);
        $actor = new PersonModel;

        $event = new TopicCreatedEvent(
            $topic,
            $organization,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'organization',
            'knowledgebase_owner_name' => $organizationName,
            'knowledgebase_owner_url_slug' => null,
            'topic_name' => $topicName,
            'topic_description' => 'This topic doesn’t have a description yet',
            'topic_url_id' => $topicUrlId,
            'topic_route' => $topicUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'topic_created', $properties, null, $topicId, null, null]
        );

        $this->subscriber->onTopicCreated($event);
    }

    /**
     * @test
     */
    public function onTopicCreatedInTeam()
    {
        $topicId = 'topic-uuid';
        $topicName = 'Coding standards';
        $topicDescription = 'The style and conventions that we use in PHP, TypeScript, and other languages at Acme.';
        $topicUrlId = 'abcd1234';
        $topicUrlSlug = 'coding-standards';
        $teamId = 'team-uuid';
        $teamName = 'Engineering';
        $teamUrlSlug = 'engineering';

        $topic = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => $topicName,
            'description' => $topicDescription,
            'url_id' => $topicUrlId,
            'url_slug' => $topicUrlSlug,
        ]);
        $team = TeamModel::createFromArray([
            'id' => $teamId,
            'name' => $teamName,
            'url_slug' => $teamUrlSlug,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $topicUrlSlug,
            'url_id' => $topicUrlId,
        ]);
        $actor = new PersonModel;

        $event = new TopicCreatedEvent(
            $topic,
            $team,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'team',
            'knowledgebase_owner_name' => $teamName,
            'knowledgebase_owner_url_slug' => $teamUrlSlug,
            'topic_name' => $topicName,
            'topic_description' => $topicDescription,
            'topic_url_id' => $topicUrlId,
            'topic_route' => $topicUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'topic_created', $properties, null, $topicId, $teamId, null]
        );

        $this->subscriber->onTopicCreated($event);
    }

    /**
     * @test
     */
    public function onTopicCreatedInProject()
    {
        $topicId = 'topic-uuid';
        $topicName = 'Branding guidelines';
        $topicDescription = 'How to express Acme’s brand consistently';
        $topicUrlId = 'abcd1234';
        $topicUrlSlug = 'branding-guidelines';
        $projectId = 'project-uuid';
        $projectName = 'Marketing website';
        $projectUrlSlug = 'marketing-website';

        $topic = TopicModel::createFromArray([
            'id' => $topicId,
            'name' => $topicName,
            'description' => $topicDescription,
            'url_id' => $topicUrlId,
            'url_slug' => $topicUrlSlug,
        ]);
        $project = ProjectModel::createFromArray([
            'id' => $projectId,
            'name' => $projectName,
            'url_slug' => $projectUrlSlug,
        ]);
        $route = KnowledgebaseRouteModel::createFromArray([
            'route' => $topicUrlSlug,
            'url_id' => $topicUrlId,
        ]);
        $actor = new PersonModel;

        $event = new TopicCreatedEvent(
            $topic,
            $project,
            $route,
            $actor
        );

        $properties = [
            'knowledgebase_owner_type' => 'project',
            'knowledgebase_owner_name' => $projectName,
            'knowledgebase_owner_url_slug' => $projectUrlSlug,
            'topic_name' => $topicName,
            'topic_description' => $topicDescription,
            'topic_url_id' => $topicUrlId,
            'topic_route' => $topicUrlSlug,
        ];

        $this->createActivityCreatorExpectation(
            [$actor, 'topic_created', $properties, null, $topicId, null, $projectId]
        );

        $this->subscriber->onTopicCreated($event);
    }

    private function createActivityCreatorExpectation($args)
    {
        $this->activityCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args);
    }
}
