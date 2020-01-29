<?php
declare(strict_types=1);

namespace Hipper\Tests\Section;

use Hipper\Section\SectionAncestory;
use Hipper\Section\SectionRepository;
use Hipper\Organization\OrganizationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SectionAncestoryTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $sectionRepository;
    private $sectionAncestory;

    public function setUp(): void
    {
        $this->sectionRepository = m::mock(SectionRepository::class);
        $this->sectionAncestory = new SectionAncestory(
            $this->sectionRepository
        );
    }

    /**
     * @test
     */
    public function getAncestorNamesForSectionIds()
    {
        $sectionIds = [
            'sect5-uuid',
            'sect3-uuid',
            'sectD-uuid',
        ];
        $organizationId = 'org-uuid';
        $organization = new OrganizationModel;
        $organization->setId($organizationId);

        $sections = [
            [
                'id' => 'sect3-uuid',
                'name' => 'Section 3',
                'parent_section_id' => 'sect2-uuid',
            ],
            [
                'id' => 'sect1-uuid',
                'name' => 'Section 1',
                'parent_section_id' => null,
            ],
            [
                'id' => 'sect5-uuid',
                'name' => 'Section 5',
                'parent_section_id' => 'sect4-uuid',
            ],
            [
                'id' => 'sect2-uuid',
                'name' => 'Section 2',
                'parent_section_id' => 'sect1-uuid',
            ],
            [
                'id' => 'sect4-uuid',
                'name' => 'Section 4',
                'parent_section_id' => 'sect3-uuid',
            ],
            [
                'id' => 'sectC-uuid',
                'name' => 'Section C',
                'parent_section_id' => 'sectB-uuid',
            ],
            [
                'id' => 'sectA-uuid',
                'name' => 'Section A',
                'parent_section_id' => null,
            ],
            [
                'id' => 'sectB-uuid',
                'name' => 'Section B',
                'parent_section_id' => 'sectA-uuid',
            ],
            [
                'id' => 'sectD-uuid',
                'name' => 'Section D',
                'parent_section_id' => 'sectC-uuid',
            ],
        ];

        $this->createSectionRepositoryExpectation([$sectionIds, $organizationId], $sections);

        $expected = [
            'sect5-uuid' => ['Section 1', 'Section 2', 'Section 3', 'Section 4', 'Section 5'],
            'sect3-uuid' => ['Section 1', 'Section 2', 'Section 3'],
            'sectD-uuid' => ['Section A', 'Section B', 'Section C', 'Section D'],
        ];

        $result = $this->sectionAncestory->getAncestorNamesForSectionIds($sectionIds, $organization);
        $this->assertEquals($expected, $result);
    }

    private function createSectionRepositoryExpectation($args, $result)
    {
        $this->sectionRepository
            ->shouldReceive('getNameAndAncestorNamesWithIds')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
