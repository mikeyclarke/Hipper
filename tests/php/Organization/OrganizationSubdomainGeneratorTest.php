<?php
declare(strict_types=1);

namespace Lithos\Tests\Organization;

use Lithos\Organization\OrganizationSubdomainGenerator;
use PHPUnit\Framework\TestCase;

class OrganizationSubdomainGeneratorTest extends TestCase
{
    private $subdomainGenerator;

    public function setUp(): void
    {
        $this->subdomainGenerator = new OrganizationSubdomainGenerator;
    }

    /**
     * @test
     */
    public function invalidCharactersAreStripped()
    {
        $input = '🤔 achme= ā_ #';
        $expected = 'achme';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function outerWhitespaceAndDashesAreStripped()
    {
        $input = '   achme----';
        $expected = 'achme';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function duplicateWhitespaceIsStripped()
    {
        $input = 'acme   llp';
        $expected = 'acme-llp';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function whitespaceIsReplaced()
    {
        $input = 'acme limited liability partnership';
        $expected = 'acme-limited-liability-partnership';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function isLowercased()
    {
        $input = 'acme LLP';
        $expected = 'acme-llp';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function isCheckedAgainstBlacklist()
    {
        $input = 'apple';
        $expected = '';

        $result = $this->subdomainGenerator->generate($input);
        $this->assertEquals($expected, $result);
    }
}
