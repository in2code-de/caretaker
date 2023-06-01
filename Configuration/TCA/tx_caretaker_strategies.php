<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

$extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
$advancedNotificationsEnabled = $extConfig['notifications.']['advanced.']['enabled'] == '1';

if ($advancedNotificationsEnabled) {
    $GLOBALS['TCA']['tx_caretaker_strategies'] = [
        'ctrl' => [
            'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_strategies',
            'label' => 'name',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'default_sortby' => 'ORDER BY name',
            'delete' => 'deleted',
            'rootLevel' => -1,
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
            'iconfile' => 'EXT:caretaker/Resources/Public/Icons/strategy.png',
            'searchFields' => 'name, description',
        ],
        'columns' => [
            'hidden' => [
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.disable',
                'config' => [
                    'type' => 'check',
                    'default' => '0',
                ],
            ],
            'name' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_strategies.name',
                'config' => [
                    'type' => 'input',
                    'size' => '30',
                    'eval' => 'unique,trim',
                ],
            ],
            'description' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_strategies.description',
                'config' => [
                    'type' => 'text',
                    'cols' => '50',
                    'rows' => '5',
                ],
            ],
            'config' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_strategies.config',
                'config' => [
                    'type' => 'text',
                    'cols' => 50,
                    'rows' => 50,
                ],
            ],
        ],
        'types' => [
            '0' => ['showitem' => 'hidden, id, name, description, config'],
        ],
        'palettes' => [
            '1' => [],
        ],
    ];
}
