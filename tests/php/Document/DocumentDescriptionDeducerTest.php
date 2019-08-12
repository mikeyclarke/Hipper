<?php
declare(strict_types=1);

namespace Hipper\Tests\Document;

use Hipper\Document\DocumentDescriptionDeducer;
use PHPUnit\Framework\TestCase;

class DocumentDescriptionDeducerTest extends TestCase
{
    private $documentDescriptionDeducer;

    public function setUp(): void
    {
        $this->documentDescriptionDeducer = new DocumentDescriptionDeducer;
    }

    /**
     * @test
     * @dataProvider deducerProvider
     */
    public function deduce($content, $expected)
    {
        $result = $this->documentDescriptionDeducer->deduce($content);
        $this->assertEquals($expected, $result);
    }

    public function deducerProvider()
    {
        return [
            'No type' => [
                [],
                null,
            ],
            'Type isn’t doc' => [
                ['type' => 'foo'],
                null,
            ],
            'No content' => [
                ['type' => 'doc', 'content' => []],
                null,
            ],
            'Paragraphs with no text' => [
                [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph'], ['type' => 'paragraph'], ['type' => 'paragraph'],
                    ],
                ],
                null,
            ],
            'Only the first three children are searched' => [
                [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph'],
                        ['type' => 'paragraph'],
                        ['type' => 'paragraph'],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Fourth p text too late']]],
                    ]
                ],
                null,
            ],
            'Minimum of 15 characters required for description' => [
                [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Too short']]]
                    ]
                ],
                null,
            ],
            'Description from first paragraph' => [
                [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'A suitable description']]],
                    ],
                ],
                'A suitable description',
            ],
            'Non-paragrah children and empty paragraphs are passed over' => [
                [
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'heading',
                            'attrs' => ['level' => 1],
                            'content' => [['type' => 'text', 'text' => 'A heading']],
                        ],
                        ['type' => 'paragraph'],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'A suitable description']]],
                    ],
                ],
                'A suitable description',
            ],
            'Short text is passed over' => [
                [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Some shit here']]],
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'Much better content here though, fine for a description']
                            ]
                        ],
                    ],
                ],
                'Much better content here though, fine for a description',
            ],
            'Marks are discarded and text output without them' => [
                [
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                ['type' => 'text', 'text' => 'Another '],
                                ['type' => 'text', 'marks' => [['type' => 'strong']], 'text' => 'suitable'],
                                ['type' => 'text', 'text' => ' '],
                                ['type' => 'text', 'marks' => [['type' => 'em']], 'text' => 'description'],
                                ['type' => 'text', 'text' => ' with a bit of '],
                                ['type' => 'text', 'marks' => [['type' => 'code']], 'text' => 'mark-up'],
                                ['type' => 'text', 'text' => ' and a '],
                                [
                                    'type' => 'text',
                                    'marks' => [
                                        [
                                            'type' => 'link',
                                            'attrs' => ['href' => 'https://duckduckgo.com', 'title' => null],
                                        ]
                                    ],
                                    'text' => 'link',
                                ],
                                ['type' => 'text', 'text' => '.'],
                            ],
                        ],
                    ],
                ],
                'Another suitable description with a bit of mark-up and a link.',
            ],
            'Text is truncated at maximum character length' => [
                [
                    'type' => 'doc',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'content' => [
                                [
                                    'type' => 'text',
                                    // phpcs:disable Generic.Files.LineLength
                                    'text' => 'The unanimous Declaration of the thirteen united States of America, “When in the Course of human events, it becomes necessary for one people to dissolve the political bands which have connected them with another, and to assume among the powers of the earth, the separate and equal station to which the Laws of Nature and of Nature’s God entitle them, a decent respect to the opinions of mankind requires that they should declare the causes which impel them to the separation.',
                                    // phpcs:enable
                                ]
                            ]
                        ]
                    ]
                ],
                // phpcs:disable Generic.Files.LineLength
                'The unanimous Declaration of the thirteen united States of America, “When in the Course of human events, it becomes necessary for one people to disso…',
                // phpcs:enable
            ],
        ];
    }
}
