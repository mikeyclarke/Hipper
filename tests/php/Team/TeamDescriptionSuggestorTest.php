<?php
declare(strict_types=1);

namespace Lithos\Tests\Team;

use Lithos\Team\TeamDescriptionSuggestor;
use PHPUnit\Framework\TestCase;

class TeamDescriptionSuggestorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        $this->teamDescriptionSuggestor = new TeamDescriptionSuggestor;
    }

    /**
     * @dataProvider suggestionProvider
     */
    public function testSuggest($teamName, $expected)
    {
        $orgName = 'Dunder Mifflin';
        $result = $this->teamDescriptionSuggestor->suggest($orgName, $teamName);
        $this->assertEquals($expected, $result);
    }

    public function suggestionProvider()
    {
        // phpcs:disable Generic.Files.LineLength
        return [
            'no match' => [
                'Foo',
                null,
            ],
            'design' => [
                'Research & Design',
                'Our Research & Design team conceives thoughtful and intuitive products to make our customers’ lives better',
            ],
            'engineering' => [
                'Software Engineering',
                'Our Software Engineering team executes our vision and brings Dunder Mifflin’s products to life',
            ],
            'techops' => [
                'TechOps',
                'Our TechOps team works to make our systems ever more reliable, robust, and scalable',
            ],
            'it' => [
                'IT',
                'Our IT team takes care of our internal systems and empowers everyone at Dunder Mifflin to do their best work',
            ],
            'strategy' => [
                'Business Strategy & Ops',
                'Our Business Strategy & Ops team plans key initiatives to drive Dunder Mifflin and its product offerings into the future',
            ],
            'comms' => [
                'Communications',
                'Our Communications team is the voice of Dunder Mifflin and shares our story with the world',
            ],
            'finance' => [
                'Finance',
                'Our Finance team ensures that Dunder Mifflin has the resources to grow and to invest in our product and people',
            ],
            'legal' => [
                'Legal Affairs',
                'Our Legal Affairs team looks out for our customers’ interests and makes sure that Dunder Mifflin is doing everything it needs to stay protected',
            ],
            'marketing' => [
                'Marketing',
                'Our Marketing team finds the most effective ways to identify and engage the right audiences for Dunder Mifflin’s products',
            ],
            'hr' => [
                'PeopleOps',
                'Our PeopleOps team looks after the happiness and well-being of the Dunder Mifflin family, and finds its newest members',
            ],
            'qa' => [
                'QA',
                'Our QA team works meticulously to ensure that our products are of the highest calibre and worthy of the Dunder Mifflin name'
            ],
            'bizdev' => [
                'Business Development',
                'Our Business Development team builds and nurtures relationships with partners and finds new opportunities to help Dunder Mifflin grow',
            ],
            'sales' => [
                'Sales',
                'Our Sales team works to put our products into customers’ hands and demonstrate the role that they can play in people’s lives',
            ],
            'support' => [
                'Customer Experience',
                'Our Customer Experience team works to understand our customers’ needs and ensure that their voice is heard in everything that we do'
            ],
        ];
        // phpcs:enable
    }
}
