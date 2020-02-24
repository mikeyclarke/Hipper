<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\DocumentOutlineGenerator;
use PHPUnit\Framework\TestCase;

class DocumentOutlineGeneratorTest extends TestCase
{
    private $outlineGenerator;

    public function setUp(): void
    {
        $this->outlineGenerator = new DocumentOutlineGenerator;
    }

    /**
     * @test
     */
    public function generate()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'An opening paragraph…',
                        ]
                    ]
                ],
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'First heading',
                        ]
                    ],
                    'html_id' => '_first-heading',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Something about the first topic…',
                        ]
                    ]
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Something to sum up…',
                        ]
                    ]
                ],
            ],
        ];

        $expected = [
            [
                'id' => '_first-heading',
                'level' => 1,
                'text' => 'First heading',
            ],
        ];

        $result = $this->outlineGenerator->generate($doc);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function levelIsTakenFromAttrs()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'An h1',
                        ]
                    ],
                    'html_id' => '_an-h1',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'An h2',
                        ]
                    ],
                    'html_id' => '_an-h2',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 3,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'An h3',
                        ]
                    ],
                    'html_id' => '_an-h3',
                ],
            ],
        ];

        $expected = [
            [
                'id' => '_an-h1',
                'level' => 1,
                'text' => 'An h1',
            ],
            [
                'id' => '_an-h2',
                'level' => 2,
                'text' => 'An h2',
            ],
            [
                'id' => '_an-h3',
                'level' => 3,
                'text' => 'An h3',
            ],
        ];

        $result = $this->outlineGenerator->generate($doc);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textFromMultipleTextNodesIsConcatinated()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'A ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'code mark',
                            'marks' => [
                                [
                                    'type' => 'code',
                                ]
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => ' in a heading',
                        ],
                    ],
                    'html_id' => '_a-code-mark-in-a-heading',
                ],
            ],
        ];

        $expected = [
            [
                'id' => '_a-code-mark-in-a-heading',
                'level' => 1,
                'text' => 'A code mark in a heading',
            ],
        ];

        $result = $this->outlineGenerator->generate($doc);
        $this->assertEquals($expected, $result);
    }
}
