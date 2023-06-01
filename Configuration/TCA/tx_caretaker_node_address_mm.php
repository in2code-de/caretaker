<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['tx_caretaker_node_address_mm'] = [
    'ctrl' => [
        'hideTable' => 1,
        'label' => 'uid_address',
        'label_alt' => 'role',
        'label_alt_force' => 1,
        'iconfile' => 'EXT:caretaker/Resources/Public/Icons/nodeaddressrelation.png',
        'rootLevel' => -1,
    ],
    'columns' => [
        'uid_address' => [
            'label' => 'LLL:EXT:tt_address/locallang_tca.xml:tt_address',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => ExtensionManagementUtility::isLoaded('tt_address') ? 'tt_address' : 'tx_caretaker_contactaddress',
                'fieldControl' => [
                    'addRecord' => [
                        'pid' => '0',
                        'table' => ExtensionManagementUtility::isLoaded('tt_address') ? 'tt_address' : 'tx_caretaker_contactaddress',
                        'title' => 'Create new address',
                        'setValue' => 'prepend',
                    ],
                ],
            ],
        ],
        'role' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_roles',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_caretaker_roles',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'uid_address, --palette--;;1, role'],
    ],
];
