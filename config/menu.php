<?php

return [
    'admin' => [
        [
            'title'      => 'Панель управления',
            'route'      => 'admin.dashboard',
            'icon'       => 'fas fa-tachometer-alt',
            'permission' => 'view_admin_panel',
        ],
        [
            'title'      => 'Новости',
            'route'      => 'admin.news.index',
            'icon'       => 'fas fa-newspaper',
            'permission' => 'view_news',
        ],
        [
            'title'      => 'Категории',
            'route'      => 'admin.categories.index',
            'icon'       => 'fas fa-list',
            'permission' => 'view_categories',
        ],
        [
            'title'      => 'Комментарии',
            'route'      => 'admin.comments.index',
            'icon'       => 'fas fa-comment',
            'permission' => 'view_comments',
        ],
        [
            'title'      => 'Пользователи',
            'route'      => 'admin.users.index',
            'icon'       => 'fas fa-users',
            'permission' => 'view_users',
        ],
    ],
    'public' => [
        [
            'title' => 'Главная',
            'route' => 'home',
            'icon' => null,
        ],
        [
            'title' => 'Категории',
            'route' => 'categories.index',
            'icon' => null,
        ],
        [
            'title' => 'Администрирование',
            'route' => 'admin.dashboard',
            'icon' => null,
            'permission' => 'view_admin_panel',
        ],
    ]
];
