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
        'path' => '/module/caretaker/',
        'labels' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Caretaker',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'controllerActions' => [
            AdminModuleController::class => [
                'index', 'debug',
            ],
        ],
    ]
];
