<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
$extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
$advancedNotificationsEnabled = $extConfig['notifications.']['advanced.']['enabled'] == '1';

if ($advancedNotificationsEnabled) {
    $GLOBALS['TCA']['tx_caretaker_node_strategy_mm'] = [
        'ctrl' => [
            'hideTable' => 1,
            'label' => 'uid_strategy',
            'iconfile' => 'EXT:caretaker/Resources/Public/Icons/nodeaddressrelation.png',
        ],
        'interface' => [
            'showRecordFieldList' => '',
        ],
        'columns' => [
            'uid_strategy' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_strategies',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'foreign_table' => 'tx_caretaker_strategies',
                ],
            ],
        ],
        'types' => [
            '0' => ['showitem' => 'uid_strategy, --palette--;;1'],
        ],
    ];
}
