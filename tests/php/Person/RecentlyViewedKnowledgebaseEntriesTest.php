<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonKnowledgebaseEntryViewRepository;
use Hipper\Person\PersonModel;
use Hipper\Person\RecentlyViewedKnowledgebaseEntries;
use Hipper\Person\RecentlyViewedKnowledgebaseEntriesFormatter;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class RecentlyViewedKnowledgebaseEntriesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $knowledgebaseRepository;
    private $entryViewRepository;
    private $formatter;
    private $recentlyViewedKnowledgebaseEntries;

    public function setUp(): void
    {
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->entryViewRepository = m::mock(PersonKnowledgebaseEntryViewRepository::class);
        $this->formatter = m::mock(RecentlyViewedKnowledgebaseEntriesFormatter::class);

        $this->recentlyViewedKnowledgebaseEntries = new RecentlyViewedKnowledgebaseEntries(
            $this->knowledgebaseRepository,
            $this->entryViewRepository,
            $this->formatter
        );
    }

    /**
     * @test
     */
    public function get()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $organizationKnowledgebaseId = 'org-kb-uuid';

        $person = PersonModel::createFromArray([
            'id' => $personId,
        ]);
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
            'knowledgebase_id' => $organizationKnowledgebaseId,
        ]);
        $displayTimeZone = 'Europe/London';

        $entryViewRepositoryResult = [
            [
                'id' => 'doc1-uuid',
                'name' => 'Doc #1',
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => null,
            ],
            [
                'id' => 'doc2-uuid',
                'name' => 'Doc #2',
                'knowledgebase_id' => 'kb2-uuid',
                'parent_topic_id' => 'topic1-uuid',
            ],
            [
                'id' => 'topic1-uuid',
                'name' => 'Topic #1',
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic3-uuid',
            ],
            [
                'id' => 'doc3-uuid',
                'name' => 'Doc #3',
                'knowledgebase_id' => 'kb1-uuid',
                'parent_topic_id' => 'topic1-uuid',
            ],
            [
                'id' => 'topic2-uuid',
                'name' => 'Aren’t these imaginatively named!?',
                'knowledgebase_id' => 'kb3-uuid',
                'parent_topic_id' => null,
            ],
        ];
        $knowledgebaseIds = ['kb1-uuid', 'kb2-uuid', 'kb3-uuid'];
        $knowledgebaseRepositoryResult = [
            [
                'name' => 'Foo',
                'url_id' => 'foo',
                'knowledgebase_id' => 'kb1-uuid',
                'entity' => 'team',
            ],
            [
                'name' => 'Bar',
                'url_id' => 'bar',
                'knowledgebase_id' => 'kb2-uuid',
                'entity' => 'team',
            ],
            [
                'name' => 'Baz',
                'url_id' => 'baz',
                'knowledgebase_id' => 'kb3-uuid',
                'entity' => 'project',
            ],
        ];
        $knowledgebaseOwners = [
            $organizationKnowledgebaseId => $organization,
            'kb1-uuid' => TeamModel::createFromArray($knowledgebaseRepositoryResult[0]),
            'kb2-uuid' => TeamModel::createFromArray($knowledgebaseRepositoryResult[1]),
            'kb3-uuid' => ProjectModel::createFromArray($knowledgebaseRepositoryResult[2]),
        ];
        $formatterResult = ['formatted-entries'];

        $this->createEntryViewRepositoryExpectation([$personId, 5], $entryViewRepositoryResult);
        $this->createKnowledgebaseRepositoryExpectation(
            [$knowledgebaseIds, $organizationId],
            $knowledgebaseRepositoryResult
        );
        $this->createFormatterExpectation(
            [$organization, $knowledgebaseOwners, $displayTimeZone, $entryViewRepositoryResult],
            $formatterResult
        );

        $result = $this->recentlyViewedKnowledgebaseEntries->get($person, $organization, $displayTimeZone);
        $this->assertEquals($formatterResult, $result);
    }

    private function createFormatterExpectation($args, $result)
    {
        $this->formatter
            ->shouldReceive('format')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRepository
            ->shouldReceive('getKnowledgebaseOwnersForIds')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createEntryViewRepositoryExpectation($args, $result)
    {
        $this->entryViewRepository
            ->shouldReceive('getMostRecentlyViewedForPerson')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
