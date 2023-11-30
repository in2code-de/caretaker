<?php
use Caretaker\Caretaker\Controller\AdminModuleController;


/**
 * Definitions for modules provided by EXT:caretakermak
 */
return [
    'web_caretaker' => [
        'position' => ['top'],
        'standalone' => true,
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/caretaker',
        'labels' => 'Caretaker',
        'extensionName' => 'Caretaker',
        'controllerActions' => [
            AdminModuleController::class => [
                'index', 'debug',
            ],
        ],
    ]
];
