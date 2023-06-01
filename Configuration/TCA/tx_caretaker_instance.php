<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use Caretaker\Caretaker\UserFunc\SlackChannelsUserFunc;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

$extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
$advancedNotificationsEnabled = $extConfig['notifications.']['advanced.']['enabled'] == '1';
$enableNewConfigurationOverrides = $extConfig['features.']['newConfigurationOverrides.']['enabled'] == '1';
if (VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getCurrentTypo3Version()) >= VersionNumberUtility::convertVersionNumberToInteger('7.5.0')) {
    // enable new configurations overrides automatically with 7.5 and later
    $enableNewConfigurationOverrides = true;
}

$GLOBALS['TCA']['tx_caretaker_instance'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'rootLevel' => -1,
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'fe_group' => 'fe_group',
        ],
        'iconfile' => 'EXT:caretaker/Resources/Public/Icons/instance.png',
        'searchFields' => 'title, description, url, host',
    ],
    'interface' => [
        'showRecordFieldList' => 'hidden,instancegroup,testgroups,tests,request_mode,encryption_mode,encryption_key,project_namename,project_manager,domain,additional_domains,contacts,server_type,server_provider,server_customer_id,server_other,cms_url,cms_admin,cms_pwd,cms_install_pwd,accesses,other,request_url,contacts',
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
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'description' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.description',
            'config' => [
                'type' => 'text',
                'cols' => '50',
                'rows' => '5',
                'enableRichtext' => true,
            ],
        ],
        'url' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.url',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'host' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.host',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'groups' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.groups',
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
                'foreign_table' => 'tx_caretaker_testgroup',
                'foreign_table_where' => 'ORDER BY tx_caretaker_testgroup.sorting ASC',
                'minitems' => 0,
                'maxitems' => 50,
                'MM' => 'tx_caretaker_instance_testgroup_mm',
            ],
        ],
        'tests' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tests',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_caretaker_test',
                'foreign_table_where' => ' AND deleted = 0 ORDER BY tx_caretaker_test.title ASC',
                'MM' => 'tx_caretaker_instance_test_mm',
                'MM_opposite_field' => 'instances',
                'size' => 5,
                'autoSizeMax' => 10,
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
        'public_key' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.public_key',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'max' => '1024',
            ],
        ],
        'instancegroup' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.instancegroup',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_caretaker_instancegroup',
                'foreign_table_where' => 'ORDER BY tx_caretaker_instancegroup.title ASC',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'items' => [
                    ['', '0'],
                ],
            ],
        ],
        'testconfigurations' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_conf',
            'config' => [
                'type' => 'flex',
                'ds' => [
                    'default' => 'FILE:EXT:caretaker/res/flexform/ds.tx_caretaker_instance_testconfiguration.xml',
                ],
            ],
        ],
        'configuration_overrides' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_test.test_conf',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_caretaker_instance_override',
                'foreign_field' => 'instance',
                'appearance' => [
                    'newRecordLinkAddTitle' => true,
                    'levelLinksPosition' => 'both',
                    'useSortable' => true,
                    'enabledControls' => [
                        'info' => false,
                        'new' => true,
                        'dragdrop' => true,
                        'sort' => false,
                        'hide' => true,
                        'delete' => true,
                        'localize' => false,
                    ],
                ],
                'behavior' => [
                    'enableCascadingDelete' => true,
                ],
            ],
        ],
        'contacts' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.contacts',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_caretaker_node_address_mm',
                'foreign_field' => 'uid_node',
                'foreign_table_field' => 'node_table',
                // 'foreign_selector' => 'uid_address',
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
        'slack_notification' => [
            'label' => 'Enable slack notification',
            'onChange' => 'reload',
            'config' => [
                'type' => 'check',
            ],
        ],
        'slack_notification_channel' => [
            'label' => 'Channel',
            'displayCond' => 'FIELD:slack_notification:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'maxitems' => 1,
                'itemsProcFunc' => SlackChannelsUserFunc::class . '->getChannels'
            ],
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                title, 
                instancegroup, 
                url, 
                host, 
                public_key, 
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.description, description, 
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.relations, groups, tests, 
                --div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.contacts, contacts, ' .
                ($advancedNotificationsEnabled ? '--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.notifications, notification_strategies, slack_notification, slack_notification_channel, ' : '') .
                '--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.testconfigurations, ' .
                ($enableNewConfigurationOverrides ? 'configuration_overrides, ' : 'testconfigurations,') .
                '--div--;LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance.tab.access, hidden, starttime, endtime, fe_group',
        ],
    ],
    'palettes' => [],
];
