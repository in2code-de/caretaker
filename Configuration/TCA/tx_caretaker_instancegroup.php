<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
$extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
$advancedNotificationsEnabled = $extConfig['notifications.']['advanced.']['enabled'] == '1';

$GLOBALS['TCA']['tx_caretaker_instancegroup'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'type' => '',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'rootLevel' => -1,
        'treeParentField' => 'parent_group',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:caretaker/Resources/Public/Icons/instancegroup.png',
        'searchFields' => 'title, description',
    ],
    'interface' => [
        'showRecordFieldList' => 'name,tests,description,parent_group,contacts,notification_strategies,starttime,endtime,hidden,fe_group',
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
                'renderType' => 'selectMultipleSideBySide',
                'items' => [
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hide_at_login', -1],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.any_login', -2],
                    ['LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.usergroups', '--div--'],
                ],
                'exclusiveKeys' => '-1,-2',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'maxitems' => 20,
                'size' => 7,
            ],
        ],
        'title' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
                'required' => true,
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.description',
            'config' => [
                'type' => 'text',
                'cols' => '50',
                'rows' => '5',
                'enableRichtext' => true,
            ],
        ],
        'parent_group' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.parent_group',
            'config' => [
                'type' => 'select',
                'renderMode' => 'tree',
                'renderType' => 'selectTree',
                'treeConfig' => [
                    'parentField' => 'parent_group',
                    'appearance' => [
                        'showHeader' => true,
                    ],
                ],
                'foreign_table' => 'tx_caretaker_instancegroup',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'contacts' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.contacts',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_caretaker_node_address_mm',
                'foreign_field' => 'uid_node',
                'foreign_table_field' => 'node_table',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                ],
            ],
        ],
        'notification_strategies' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_general.notification_strategies',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_caretaker_node_strategy_mm',
                'foreign_field' => 'uid_node',
                'foreign_table_field' => 'node_table',
                'foreign_selector' => 'uid_strategy',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                ],
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                title, 
                --palette--;;1,
                parent_group, 
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.tab.description, description, 
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.tab.contacts, contacts, ' .
                ($advancedNotificationsEnabled ? '--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.tab.notifications, notification_strategies, ' : '') .
                '--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup.tab.access, hidden, starttime, endtime, fe_group',
        ],
    ],
    'palettes' => [],
];
