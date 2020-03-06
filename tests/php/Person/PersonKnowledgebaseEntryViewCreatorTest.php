<?php
declare(strict_types=1);

namespace Hipper\Tests\Person;

use Hipper\Document\DocumentModel;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Person\PersonKnowledgebaseEntryViewCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\Storage\PersonKnowledgebaseEntryViewInserter;
use Hipper\Topic\TopicModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonKnowledgebaseEntryViewCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $inserter;
    private $creator;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->inserter = m::mock(PersonKnowledgebaseEntryViewInserter::class);

        $this->creator = new PersonKnowledgebaseEntryViewCreator(
            $this->idGenerator,
            $this->inserter
        );
    }

    /**
     * @test
     */
    public function createForDocument()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $documentId = 'doc-uuid';
        $knowledgebaseId = 'kb-uuid';

        $person = PersonModel::createFromArray([
            'id' => $personId,
            'organization_id' => $organizationId,
        ]);
        $document = DocumentModel::createFromArray([
            'id' => $documentId,
            'knowledgebase_id' => $knowledgebaseId,
        ]);

        $id = 'view-uuid';

        $this->createIdGeneratorExpectation($id);
        $this->createInserterExpectation([$id, $personId, $knowledgebaseId, $organizationId, $documentId, null]);

        $this->creator->create($person, $document);
    }

    /**
     * @test
     */
    public function createForTopic()
    {
        $personId = 'person-uuid';
        $organizationId = 'org-uuid';
        $topicId = 'topic-uuid';
        $knowledgebaseId = 'kb-uuid';

        $person = PersonModel::createFromArray([
            'id' => $personId,
            'organization_id' => $organizationId,
        ]);
        $topic = TopicModel::createFromArray([
            'id' => $topicId,
            'knowledgebase_id' => $knowledgebaseId,
        ]);

        $id = 'view-uuid';

        $this->createIdGeneratorExpectation($id);
        $this->createInserterExpectation([$id, $personId, $knowledgebaseId, $organizationId, null, $topicId]);

        $this->creator->create($person, $topic);
    }

    private function createInserterExpectation($args)
    {
        $this->inserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }
}
