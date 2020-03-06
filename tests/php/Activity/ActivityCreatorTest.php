<?php
declare(strict_types=1);

namespace Hipper\Tests\Activity;

use Hipper\Activity\ActivityCreator;
use Hipper\Activity\Storage\ActivityInserter;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Person\PersonModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ActivityCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $activityInserter;
    private $idGenerator;
    private $activityCreator;

    public function setUp(): void
    {
        $this->activityInserter = m::mock(ActivityInserter::class);
        $this->idGenerator = m::mock(IdGenerator::class);

        $this->activityCreator = new ActivityCreator(
            $this->activityInserter,
            $this->idGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $actorId = 'actor-uuid';
        $organizationId = 'org-uuid';

        $actor = PersonModel::createFromArray([
            'id' => $actorId,
            'organization_id' => $organizationId,
        ]);
        $type = 'foo.created';

        $id = 'activity-uuid';

        $this->createIdGeneratorExpectation($id);
        $this->createActivityInserterExpectation([
            $id,
            $type,
            $actorId,
            $organizationId,
            null,
            null,
            null,
            null,
            null
        ]);

        $this->activityCreator->create($actor, $type);
    }

    /**
     * @test
     */
    public function createWithProperties()
    {
        $actorId = 'actor-uuid';
        $organizationId = 'org-uuid';

        $actor = PersonModel::createFromArray([
            'id' => $actorId,
            'organization_id' => $organizationId,
        ]);
        $type = 'bar.updated';
        $properties = [
            'some' => 'thing',
            'another' => 'thing',
        ];

        $id = 'activity-uuid';
        $storage = json_encode($properties);

        $this->createIdGeneratorExpectation($id);
        $this->createActivityInserterExpectation([
            $id,
            $type,
            $actorId,
            $organizationId,
            $storage,
            null,
            null,
            null,
            null
        ]);

        $this->activityCreator->create($actor, $type, $properties);
    }

    private function createActivityInserterExpectation($args)
    {
        $this->activityInserter
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
