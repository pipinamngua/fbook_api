<?php

return [
    'book' => [
        'status' => [
            'unavailable' => 0,
            'available' => 1,
        ],
        'fields' => [
            'title',
            'description',
            'author',
            'publish_date',
            'total_page',
            'avg_star',
            'code',
            'count_view',
            'status',
            'created_at'
        ],
        'interested_books' => [
            'books_per_page' => 6,
        ],
    ],
    'book_user' => [
        'status' => [
            'waiting' => 1,
            'reading' => 2,
            'done' => 3,
        ]
    ],
    'filter_books' => [
        'view' => [
            'key' => 'view',
            'field' => 'count_view',
            'title' => translate('title_key.view')
        ],
        'waiting' => [
            'key' => 'waiting',
            'field' => '',
            'title' => translate('title_key.waiting')
        ],
        'rating' => [
            'key' => 'rating',
            'field' => 'avg_star',
            'title' => translate('title_key.rating')
        ],
        'latest' => [
            'key' => 'latest',
            'field' => 'created_at',
            'title' => translate('title_key.latest')
        ],
        'read' => [
            'key' => 'read',
            'field' => '',
            'title' => translate('title_key.read')
        ]
    ],
    'condition_sort_book' => [
        ['text' => 'Title', 'field' => 'title'],
        ['text' => 'View', 'field' => 'count_view'],
        ['text' => 'Star', 'field' => 'avg_star'],
        ['text' => 'Publish date', 'field' => 'publish_date'],
        ['text' => 'Author', 'field' => 'author'],
        ['text' => 'Created at', 'field' => 'created_at'],
    ],
    'filter_type' => [
        'category', 'office'
    ],
    'sort_type' => [
        'desc', 'asc'
    ],
    'media_type' => [
        'image' => 0,
        'video' => 1,
    ],
    'user_sharing_book' => 'sharing',
    'media' => [
        'type' => [
            'image_book' => 1
        ],
    ],
    'book_user_status_cancel' => 4
];
