<?php

$GLOBALS['TCA']['tx_caretaker_testgroup'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'rootLevel' => -1,
        'treeParentField' => 'parent_group',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:caretaker/Resources/Public/Icons/group.png',
        'searchFields' => 'title, description',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,tests,name',
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
                'type' => 'input',
                'size' => '8',
                'eval' => 'date',
                'default' => '0',
                'checkbox' => '0',
                'renderType' => 'inputDateTime',
            ],
        ],
        'endtime' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => '8',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0',
                'range' => [
                    'upper' => mktime(0, 0, 0, 12, 31, 2020),
                    'lower' => mktime(0, 0, 0, date('m') - 1, date('d'), date('Y')),
                ],
                'renderType' => 'inputDateTime',
            ],
        ],
        'fe_group' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fe_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login', -2],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups', '--div--'],
                ],
                'foreign_table' => 'fe_groups',
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.description',
            'config' => [
                'type' => 'text',
                'cols' => '50',
                'rows' => '5',
                'enableRichtext' => true,
            ],
        ],
        'parent_group' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.parent_group',
            'config' => [
                'type' => 'select',
                'renderMode' => 'tree', // for old versions
                'renderType' => 'selectTree', // for 7.4 and higher
                'treeConfig' => [
                    'parentField' => 'parent_group',
                    'appearance' => [
                        'showHeader' => true,
                    ],
                ],
                'foreign_table' => 'tx_caretaker_testgroup',
                'foreign_table_where' => 'ORDER BY tx_caretaker_testgroup.sorting ASC',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'instances' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.instances',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_instance',
                'MM' => 'tx_caretaker_instance_testgroup_mm',
                'MM_opposite_field' => 'group',
                'size' => 5,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 10000,
            ],
        ],
        'tests' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.tests',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_test',
                'foreign_table_where' => 'ORDER BY tx_caretaker_test.title ASC',
                'MM' => 'tx_caretaker_testgroup_test_mm',
                'MM_opposite_field' => 'groups',
                'size' => 5,
                'autoSizeMax' => 25,
                'minitems' => 0,
                'maxitems' => 10000,
                'fieldControl' => [
                    'addRecord' => [
                        'pid' => '###CURRENT_PID###',
                        'table' => 'tx_caretaker_test',
                        'title' => 'Create new Test',
                        'setValue' => 'prepend',
                    ],
                    'editPopup' => [
                        'title' => 'Edit Test',
                        'windowOpenParameters' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
                    ],
                ],
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                hidden, 
                --palette--;;1, 
                title, 
                parent_group,
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.tab.description,description,
		        --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_testgroup.tab.relations,tests
		    ',
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => 'starttime,endtime,fe_group'],
        'instances' => ['showitem' => 'instances', 'isHiddenPalette' => true],
    ],
];
