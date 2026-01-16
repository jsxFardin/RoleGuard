<?php

return [
    /**
     * Permission definitions used for:
     * - seeding permissions
     * - building the Role permission UI (grouped tree)
     *
     * Slug convention: "{module}.{action}"
     * Actions: list, view, create, update, delete
     */
    'groups' => [
        [
            'key' => 'admin',
            'label' => 'Admin',
            'modules' => [
                [
                    'key' => 'admin',
                    'label' => 'Admin Panel',
                    'permissions' => [
                        ['slug' => 'admin.access', 'label' => 'Access'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'cms',
            'label' => 'CMS',
            'modules' => [
                [
                    'key' => 'services',
                    'label' => 'Services',
                    'permissions' => [
                        ['slug' => 'services.list', 'label' => 'List'],
                        ['slug' => 'services.view', 'label' => 'View'],
                        ['slug' => 'services.create', 'label' => 'Create'],
                        ['slug' => 'services.update', 'label' => 'Update'],
                        ['slug' => 'services.delete', 'label' => 'Delete'],
                    ],
                ],
                [
                    'key' => 'faqs',
                    'label' => 'FAQs',
                    'permissions' => [
                        ['slug' => 'faqs.list', 'label' => 'List'],
                        ['slug' => 'faqs.view', 'label' => 'View'],
                        ['slug' => 'faqs.create', 'label' => 'Create'],
                        ['slug' => 'faqs.update', 'label' => 'Update'],
                        ['slug' => 'faqs.delete', 'label' => 'Delete'],
                    ],
                ],
            ],
        ],
        [
            'key' => 'users_roles',
            'label' => 'Users & Roles',
            'modules' => [
                [
                    'key' => 'users',
                    'label' => 'Users',
                    'permissions' => [
                        ['slug' => 'users.list', 'label' => 'List'],
                        ['slug' => 'users.view', 'label' => 'View'],
                        ['slug' => 'users.create', 'label' => 'Create'],
                        ['slug' => 'users.update', 'label' => 'Update'],
                        ['slug' => 'users.delete', 'label' => 'Delete'],
                    ],
                ],
                [
                    'key' => 'roles',
                    'label' => 'Roles',
                    'permissions' => [
                        ['slug' => 'roles.list', 'label' => 'List'],
                        ['slug' => 'roles.view', 'label' => 'View'],
                        ['slug' => 'roles.create', 'label' => 'Create'],
                        ['slug' => 'roles.update', 'label' => 'Update'],
                        ['slug' => 'roles.delete', 'label' => 'Delete'],
                    ],
                ],
                [
                    'key' => 'audit',
                    'label' => 'Audit',
                    'permissions' => [
                        ['slug' => 'audit.list', 'label' => 'List'],
                        ['slug' => 'audit.view', 'label' => 'View'],
                    ],
                ],
            ],
        ],
    ],
];

