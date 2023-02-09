<?php

return [
    'login' => [
        'type' => 2,
    ],
    'logout' => [
        'type' => 2,
    ],
    'error' => [
        'type' => 2,
    ],
    'sign-up' => [
        'type' => 2,
    ],
    'index' => [
        'type' => 2,
    ],
    'view' => [
        'type' => 2,
    ],
    'create' => [
        'type' => 2,
    ],
    'update' => [
        'type' => 2,
    ],
    'delete' => [
        'type' => 2,
    ],
    'profile' => [
        'type' => 2,
    ],
    'test' => [
        'type' => 2,
    ],
    'syncing' => [
        'type' => 2,
    ],
    'sync-buyer' => [
        'type' => 2,
    ],
    'sync-price-category' => [
        'type' => 2,
    ],
    'sync-all' => [
        'type' => 2,
    ],
    'sync-nomenclature' => [
        'type' => 2,
    ],
    'sync-nomenclature-group' => [
        'type' => 2,
    ],
    'get-price-for-price-category' => [
        'type' => 2,
    ],
    'sync-price-for-price-category' => [
        'type' => 2,
    ],
    'sync-buyer-balances' => [
        'type' => 2,
    ],
    'get-orders-by-date' => [
        'type' => 2,
    ],
    'get-nomenclature' => [
        'type' => 2,
    ],
    'system-info' => [
        'type' => 2,
    ],
    'show-errors' => [
        'type' => 2,
    ],
    'show-order-error-settings' => [
        'type' => 2,
    ],
    'check-activity' => [
        'type' => 2,
    ],
    'order-create' => [
        'type' => 2,
    ],
    'order-update' => [
        'type' => 2,
    ],
    'cancel' => [
        'type' => 2,
    ],
    'change-status' => [
        'type' => 2,
    ],
    'copy-order' => [
        'type' => 2,
    ],
    're-make-documents' => [
        'type' => 2,
    ],
    'sync' => [
        'type' => 2,
    ],
    'sync-p-f-p-c' => [
        'type' => 2,
    ],
    'get-content' => [
        'type' => 2,
    ],
    'get-product-for-tab' => [
        'type' => 2,
    ],
    'add-product' => [
        'type' => 2,
    ],
    'offline' => [
        'type' => 2,
    ],
    'delete-draft' => [
        'type' => 2,
    ],
    'reset-draft' => [
        'type' => 2,
    ],
    'to-queue' => [
        'type' => 2,
    ],
    'send-drafts' => [
        'type' => 2,
    ],
    'help' => [
        'type' => 2,
    ],
    'to-draft' => [
        'type' => 2,
    ],
    'update-draft' => [
        'type' => 2,
    ],
    'php-info' => [
        'type' => 2,
    ],
    'guest' => [
        'type' => 1,
        'description' => 'Гость',
        'ruleName' => 'userGroup',
        'children' => [
            'login',
            'error',
            'sign-up',
            'index',
            'view',
            'get-nomenclature',
            'sync-nomenclature',
            'sync-price-for-price-category',
            'check-activity',
            'sync',
            'sync-p-f-p-c',
            'offline',
            'send-drafts',
        ],
    ],
    'buyer' => [
        'type' => 1,
        'description' => 'Покупатель',
        'ruleName' => 'userGroup',
        'children' => [
            'update',
            'create',
            'logout',
            'profile',
            'guest',
            'get-orders-by-date',
            'order-create',
            'order-update',
            'cancel',
            'change-status',
            'copy-order',
            're-make-documents',
            'get-content',
            'get-product-for-tab',
            'add-product',
            'offline',
            'delete-draft',
            'reset-draft',
            'to-queue',
            'send-drafts',
            'help',
            'to-draft',
            'update-draft',
            'updateOwnProfile',
        ],
    ],
    'supplier' => [
        'type' => 1,
        'description' => 'Поставщик',
        'ruleName' => 'userGroup',
        'children' => [
            'updateOwnProfile',
        ],
    ],
    'admin' => [
        'type' => 1,
        'description' => 'Администратор',
        'ruleName' => 'userGroup',
        'children' => [
            'delete',
            'test',
            'syncing',
            'sync-buyer',
            'sync-price-category',
            'sync-all',
            'sync-nomenclature-group',
            'get-price-for-price-category',
            'sync-price-for-price-category',
            'sync-buyer-balances',
            'system-info',
            'show-errors',
            'php-info',
            'guest',
            'buyer',
        ],
    ],
    'updateOwnProfile' => [
        'type' => 2,
        'ruleName' => 'isProfileOwner',
    ],
];
