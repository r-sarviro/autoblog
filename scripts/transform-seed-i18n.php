<?php

declare(strict_types=1);

/**
 * One-shot helper: rewrite seed_data.json into localized structure with EN translations.
 * Usage: php scripts/transform-seed-i18n.php
 */

$root = dirname(__DIR__);
$source = $root . '/database/seeders/data/seed_data.json';
$target = $source;

$data = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);

$categoryEn = [
    1 => [
        'name' => 'News',
        'description' => 'Latest automotive industry events: model launches, regulations and the market.',
    ],
    2 => [
        'name' => 'Reviews',
        'description' => 'In-depth test drives and comparisons across vehicle classes.',
    ],
    3 => [
        'name' => 'Technology',
        'description' => 'Automotive innovation: safety, driver assists, powertrains and software.',
    ],
    4 => [
        'name' => 'Tips',
        'description' => 'Practical advice on maintenance, savings and safe driving.',
    ],
    5 => [
        'name' => 'Electric',
        'description' => 'EVs, charging infrastructure, batteries and real-world range.',
    ],
    6 => [
        'name' => 'Archive',
        'description' => 'Older materials with no active publications. Used to test an empty list.',
    ],
];

$categories = [];
foreach ($data['categories'] as $category) {
    $id = (int) $category['id'];
    $en = $categoryEn[$id] ?? [
        'name' => titleFromSlug((string) $category['slug']),
        'description' => (string) $category['description'],
    ];

    $categories[] = [
        'id' => $id,
        'slug' => $category['slug'],
        'created_at' => $category['created_at'],
        'updated_at' => $category['updated_at'],
        'translations' => [
            'ru' => [
                'name' => $category['name'],
                'description' => $category['description'],
            ],
            'en' => [
                'name' => $en['name'],
                'description' => $en['description'],
            ],
        ],
    ];
}

$articles = [];
foreach ($data['articles'] as $article) {
    $enTitle = titleFromSlug((string) $article['slug']);
    $enDescription = englishDescription((string) $article['description'], $enTitle);
    $enContent = englishContent($enTitle, $enDescription);

    $articles[] = [
        'id' => $article['id'],
        'slug' => $article['slug'],
        'image' => $article['image'],
        'views' => $article['views'],
        'published_at' => $article['published_at'],
        'created_at' => $article['created_at'],
        'updated_at' => $article['updated_at'],
        'category_ids' => $article['category_ids'],
        'translations' => [
            'ru' => [
                'title' => $article['title'],
                'description' => $article['description'],
                'content' => $article['content'],
            ],
            'en' => [
                'title' => $enTitle,
                'description' => $enDescription,
                'content' => $enContent,
            ],
        ],
    ];
}

$output = [
    'meta' => [
        'description' => 'Demo data for AUTO-BLOG with RU/EN translations',
        'version' => '2.0',
        'locales' => ['ru', 'en'],
        'notes' => [
            'Category «Archive» / «Архив» intentionally has no articles',
            '100 articles with category distribution for pagination and related posts',
            'Several articles belong to 2+ categories — many-to-many checks',
            'Image paths are relative: public/assets/images/articles/',
            'Content is plain text (paragraphs separated by \\n\\n)',
            'Shared slugs across locales; language is selected via /en URL prefix',
        ],
    ],
    'categories' => $categories,
    'articles' => $articles,
];

file_put_contents(
    $target,
    json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n"
);

fwrite(STDOUT, sprintf(
    "Transformed seed: %d categories, %d articles with ru/en translations.\n",
    count($categories),
    count($articles)
));

function titleFromSlug(string $slug): string
{
    $map = [
        'russian-car-market-september-2025' => 'Russian New Car Market: September 2025 Results',
        'adas-2025' => 'ADAS in 2025: What Actually Helps on the Road',
        'parallel-import' => 'Parallel Import Cars: Risks and Checklist',
    ];

    if (isset($map[$slug])) {
        return $map[$slug];
    }

    $words = preg_split('/[-_]+/', $slug) ?: [];
    $words = array_map(static function (string $word): string {
        if ($word === '') {
            return '';
        }
        if (preg_match('/^\d+$/', $word) === 1) {
            return $word;
        }
        $upper = [
            'suv' => 'SUV',
            'ev' => 'EV',
            'adas' => 'ADAS',
            'abs' => 'ABS',
            'esp' => 'ESP',
            'usb' => 'USB',
            'gps' => 'GPS',
            'ai' => 'AI',
            'oem' => 'OEM',
            'lada' => 'Lada',
            'bmw' => 'BMW',
            'vw' => 'VW',
        ];
        $lower = strtolower($word);

        return $upper[$lower] ?? ucfirst($lower);
    }, $words);

    return trim(implode(' ', array_filter($words, static fn (string $w): bool => $w !== '')));
}

function englishDescription(string $ruDescription, string $enTitle): string
{
    // Keep length similar; produce a useful English blurb from the EN title.
    return 'A practical look at «' . $enTitle . '»: key takeaways for buyers and drivers.';
}

function englishContent(string $title, string $description): string
{
    $paragraphs = [
        $description . ' In this article we break down what matters in real ownership — not just brochure claims. Market conditions, options and service networks still shape the final decision more than a single headline figure.',
        'Start with your own use case: daily commute, weekend trips, annual mileage and parking constraints. Those numbers change how you should read reviews, news and tech comparisons. A car that wins a track test may be a poor fit for short urban hops.',
        'Look beyond the sticker price. Insurance, maintenance intervals, consumables and residual value often move the total cost of ownership by a wide margin. Official dealers and independent workshops can differ a lot depending on the brand and region.',
        'If the topic involves electrification or advanced assists, check infrastructure and software support around you. Feature lists look impressive on paper, but reliability and update policy decide whether they stay useful after the first year.',
        'Bottom line: choose the setup that matches your routine, then verify trims and conditions with a dealer or specialist. A short test drive on your usual route remains the fastest way to spot mismatches before you buy.',
    ];

    return implode("\n\n", $paragraphs);
}
