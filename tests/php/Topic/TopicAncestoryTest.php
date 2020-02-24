<?php
declare(strict_types=1);

namespace Hipper\Tests\Topic;

use Hipper\Topic\TopicAncestory;
use Hipper\Topic\TopicRepository;
use Hipper\Organization\OrganizationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TopicAncestoryTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $topicRepository;
    private $topicAncestory;

    public function setUp(): void
    {
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->topicAncestory = new TopicAncestory(
            $this->topicRepository
        );
    }

    /**
     * @test
     */
    public function getAncestorNamesForTopicIds()
    {
        $topicIds = [
            'sect5-uuid',
            'sect3-uuid',
            'sectD-uuid',
        ];
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $topics = [
            [
                'id' => 'sect3-uuid',
                'name' => 'Topic 3',
                'parent_topic_id' => 'sect2-uuid',
            ],
            [
                'id' => 'sect1-uuid',
                'name' => 'Topic 1',
                'parent_topic_id' => null,
            ],
            [
                'id' => 'sect5-uuid',
                'name' => 'Topic 5',
                'parent_topic_id' => 'sect4-uuid',
            ],
            [
                'id' => 'sect2-uuid',
                'name' => 'Topic 2',
                'parent_topic_id' => 'sect1-uuid',
            ],
            [
                'id' => 'sect4-uuid',
                'name' => 'Topic 4',
                'parent_topic_id' => 'sect3-uuid',
            ],
            [
                'id' => 'sectC-uuid',
                'name' => 'Topic C',
                'parent_topic_id' => 'sectB-uuid',
            ],
            [
                'id' => 'sectA-uuid',
                'name' => 'Topic A',
                'parent_topic_id' => null,
            ],
            [
                'id' => 'sectB-uuid',
                'name' => 'Topic B',
                'parent_topic_id' => 'sectA-uuid',
            ],
            [
                'id' => 'sectD-uuid',
                'name' => 'Topic D',
                'parent_topic_id' => 'sectC-uuid',
            ],
        ];

        $this->createTopicRepositoryExpectation([$topicIds, $organizationId], $topics);

        $expected = [
            'sect5-uuid' => ['Topic 1', 'Topic 2', 'Topic 3', 'Topic 4', 'Topic 5'],
            'sect3-uuid' => ['Topic 1', 'Topic 2', 'Topic 3'],
            'sectD-uuid' => ['Topic A', 'Topic B', 'Topic C', 'Topic D'],
        ];

        $result = $this->topicAncestory->getAncestorNamesForTopicIds($topicIds, $organization);
        $this->assertEquals($expected, $result);
    }

    private function createTopicRepositoryExpectation($args, $result)
    {
        $this->topicRepository
            ->shouldReceive('getNameAndAncestorNamesWithIds')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
