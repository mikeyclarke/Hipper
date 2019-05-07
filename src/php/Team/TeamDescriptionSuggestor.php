<?php
declare(strict_types=1);

namespace Lithos\Team;

class TeamDescriptionSuggestor
{
    const SUGGESTION_ALIASES = [
        'design' => ['design', 'research & design', 'research and design', 'product design', 'product'],
        'engineering' => ['engineering', 'software engineering', 'dev', 'development'],
        'techops' => ['tech ops', 'techops', 'dev ops', 'devops', 'infrastructure'],
        'it' => ['information technology', 'it'],
        'strategy' => [
            'business strategy', 'strategy', 'business strategy & ops', 'business strategy & operations',
            'business strategy and ops', 'business strategy and operations',
        ],
        'comms' => ['communications', 'comms', 'press relations', 'pr'],
        'finance' => ['finance'],
        'legal' => ['legal', 'policy', 'legal & policy', 'legal and policy', 'legal affairs'],
        'marketing' => ['marketing'],
        'hr' => [
            'peopleops', 'people ops', 'people operations', 'people', 'human resources', 'hr', 'recruiting & people',
            'recruiting and people',
        ],
        'qa' => ['quality assurance', 'qa'],
        'bizdev' => ['bizdev', 'biz dev', 'business development'],
        'sales' => ['sales'],
        'support' => ['customer support', 'support', 'customer service', 'customer experience'],
    ];

    // phpcs:disable Generic.Files.LineLength
    const SUGGESTIONS = [
        'design' => 'Our {TEAM_NAME} team conceives thoughtful and intuitive products to make our customers’ lives better',
        'engineering' => 'Our {TEAM_NAME} team executes our vision and brings {ORG_NAME}’s products to life',
        'techops' => 'Our {TEAM_NAME} team works to make our systems ever more reliable, robust, and scalable',
        'it' => 'Our {TEAM_NAME} team takes care of our internal systems and empowers everyone at {ORG_NAME} to do their best work',
        'strategy' => 'Our {TEAM_NAME} team plans key initiatives to drive {ORG_NAME} and its product offerings into the future',
        'comms' => 'Our {TEAM_NAME} team is the voice of {ORG_NAME} and shares our story with the world',
        'finance' => 'Our {TEAM_NAME} team ensures that {ORG_NAME} has the resources to grow and to invest in our product and people',
        'legal' => 'Our {TEAM_NAME} team looks out for our customers’ interests and makes sure that {ORG_NAME} is doing everything it needs to stay protected',
        'marketing' => 'Our {TEAM_NAME} team finds the most effective ways to identify and engage the right audiences for {ORG_NAME}’s products',
        'hr' => 'Our {TEAM_NAME} team looks after the happiness and well-being of the {ORG_NAME} family, and finds its newest members',
        'qa' => 'Our {TEAM_NAME} team works meticulously to ensure that our products are of the highest calibre and worthy of the {ORG_NAME} name',
        'bizdev' => 'Our {TEAM_NAME} team builds and nurtures relationships with partners and finds new opportunities to help {ORG_NAME} grow',
        'sales' => 'Our {TEAM_NAME} team works to put our products into customers’ hands and demonstrate the role that they can play in people’s lives',
        'support' => 'Our {TEAM_NAME} team works to understand our customers’ needs and ensure that their voice is heard in everything that we do',
    ];
    // phpcs:enable

    public function suggest(string $organizationName, string $teamName): ?string
    {
        $teamNameLower = mb_strtolower($teamName, 'UTF-8');
        foreach (self::SUGGESTION_ALIASES as $key => $aliases) {
            if (in_array($teamNameLower, $aliases)) {
                if (!isset(self::SUGGESTIONS[$key])) {
                    return null;
                }
                return str_replace(
                    ['{TEAM_NAME}', '{ORG_NAME}'],
                    [$teamName, $organizationName],
                    self::SUGGESTIONS[$key]
                );
            }
        }
        return null;
    }
}
