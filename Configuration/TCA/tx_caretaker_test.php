<?php

$GLOBALS['TCA']['tx_caretaker_test'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'rootLevel' => -1,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:caretaker/Resources/Public/Icons/test.png',
        'searchFields' => 'title, description',
    ],
    'columns' => [
        'hidden' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => '0',
            ],
        ],
        'starttime' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'size' => '8',
                'default' => '0',
            ],
        ],
        'endtime' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'size' => '8',
                'default' => '0',
                'range' => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')),
                ],
            ],
        ],
        'fe_group' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['label' => '', 'value' => 0],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login',
                        'value' => -1
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login',
                        'value' => -2
                    ],
                    [
                        'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups',
                        'value' => '--div--'
                    ],
                ],
                'foreign_table' => 'fe_groups',
                'renderType' => 'selectSingle',
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.description',
            'config' => [
                'type' => 'text',
                'cols' => '50',
                'rows' => '5',
                'enableRichtext' => true,
            ],
        ],
        'test_interval' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_interval',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'always', 'value' => 0],
                    ['label' => '1 Minute', 'value' => 60],
                    ['label' => '5 Minutes', 'value' => 300],
                    ['label' => '10 Minutes', 'value' => 600],
                    ['label' => '15 Minutes', 'value' => 900],
                    ['label' => '20 Minutes', 'value' => 1200],
                    ['label' => '30 Minutes', 'value' => 1800],
                    ['label' => '45 Minutes', 'value' => 2700],
                    ['label' => '1 Hour', 'value' => 3600],
                    ['label' => '2 Hours', 'value' => 7200],
                    ['label' => '4 Hours', 'value' => 14400],
                    ['label' => '8 Hours', 'value' => 28800],
                    ['label' => '10 Hours', 'value' => 36000],
                    ['label' => '12 Hours', 'value' => 43200],
                    ['label' => '1 Day', 'value' => 86400],
                    ['label' => '2 Days', 'value' => 172800],
                    ['label' => '3 Days', 'value' => 259200],
                    ['label' => '4 Days', 'value' => 345600],
                    ['label' => '5 Days', 'value' => 432000],
                    ['label' => '6 Days', 'value' => 518400],
                    ['label' => '1 Week', 'value' => 604800],
                    ['label' => '2 Weeks', 'value' => 1209600],
                    ['label' => '4 Weeks', 'value' => 2419200],
                ],
                'default' => 0,
            ],
        ],
        'test_interval_start_hour' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_interval_start_hour',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                    ['label' => 1, 'value' => 1],
                    ['label' => 2, 'value' => 2],
                    ['label' => 3, 'value' => 3],
                    ['label' => 4, 'value' => 4],
                    ['label' => 5, 'value' => 5],
                    ['label' => 6, 'value' => 6],
                    ['label' => 7, 'value' => 7],
                    ['label' => 8, 'value' => 8],
                    ['label' => 9, 'value' => 9],
                    ['label' => 10, 'value' => 10],
                    ['label' => 11, 'value' => 11],
                    ['label' => 12, 'value' => 12],
                    ['label' => 13, 'value' => 13],
                    ['label' => 14, 'value' => 14],
                    ['label' => 15, 'value' => 15],
                    ['label' => 16, 'value' => 16],
                    ['label' => 17, 'value' => 17],
                    ['label' => 18, 'value' => 18],
                    ['label' => 19, 'value' => 19],
                    ['label' => 20, 'value' => 20],
                    ['label' => 21, 'value' => 21],
                    ['label' => 22, 'value' => 22],
                    ['label' => 23, 'value' => 23],
                    ['label' => 24, 'value' => 24],
                ],
            ],
        ],
        'test_interval_stop_hour' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_interval_stop_hour',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
                    ['label' => 1, 'value' => 1],
                    ['label' => 2, 'value' => 2],
                    ['label' => 3, 'value' => 3],
                    ['label' => 4, 'value' => 4],
                    ['label' => 5, 'value' => 5],
                    ['label' => 6, 'value' => 6],
                    ['label' => 7, 'value' => 7],
                    ['label' => 8, 'value' => 8],
                    ['label' => 9, 'value' => 9],
                    ['label' => 10, 'value' => 10],
                    ['label' => 11, 'value' => 11],
                    ['label' => 12, 'value' => 12],
                    ['label' => 13, 'value' => 13],
                    ['label' => 14, 'value' => 14],
                    ['label' => 15, 'value' => 15],
                    ['label' => 16, 'value' => 16],
                    ['label' => 17, 'value' => 17],
                    ['label' => 18, 'value' => 18],
                    ['label' => 19, 'value' => 19],
                    ['label' => 20, 'value' => 20],
                    ['label' => 21, 'value' => 21],
                    ['label' => 22, 'value' => 22],
                    ['label' => 23, 'value' => 23],
                    ['label' => 24, 'value' => 24],
                ],
            ],
        ],
        'test_service' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_service',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array_merge(
                    [
                        0 => [
                            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_service.select_service',
                            'value' => ''
                        ],
                    ],
                // TODO: Implement this helper
                // \tx_caretaker_ServiceHelper::getTcaTestServiceItems()
                ),
                'size' => 1,
                'maxitems' => 1,
            ],
            'onChange' => 'reload',
        ],
        'test_conf' => [
            'displayCond' => 'FIELD:test_service:REQ:true',
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_conf',
            'config' => [
                'type' => 'flex',
                'ds_pointerField' => 'test_service',
                // TODO: Implement this helper
                // 'ds' => \tx_caretaker_ServiceHelper::getTcaTestConfigDs(),
            ],
        ],
        'test_retry' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    0 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_0',
                        'value' => 0
                    ],
                    2 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_1',
                        'value' => 1
                    ],
                    3 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_2',
                        'value' => 2
                    ],
                    4 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_3',
                        'value' => 3
                    ],
                    5 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_4',
                        'value' => 4
                    ],
                    6 => [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_retry_5',
                        'value' => 5
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
                'default' => 0,
            ],
        ],
        'test_due' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_due',
            'config' => [
                'type' => 'check',
            ],
        ],
        'roles' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.roles',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_roles',
                'size' => 5,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 100,
                'MM' => 'tx_caretaker_test_roles_mm',
            ],
        ],
        'groups' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.groups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_testgroup',
                'size' => 5,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 50,
                'MM' => 'tx_caretaker_testgroup_test_mm',
            ],
        ],
        'instances' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.instances',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_instance',
                'size' => 5,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 10000,
                'MM' => 'tx_caretaker_instance_test_mm',
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                test_service, 
                hidden,
                --palette--;;1,
                title,
                test_interval, 
                --palette--;;2,
                test_retry, 
                test_due,
                test_conf,
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.tab.description, 
                    description,
				--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.tab.notifications, 
				    roles,
                    --palette--;Groups and Instances;3
            ',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime,endtime,fe_group'],
        '2' => ['showitem' => 'test_interval_start_hour,test_interval_stop_hour'],
        '3' => ['showitem' => 'groups,instances', 'isHiddenPalette' => true],
    ],
];
