<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
$advancedNotificationsEnabled = $extConfig['notifications']['advanced']['enabled'] == '1';

if ($advancedNotificationsEnabled && ExtensionManagementUtility::isLoaded('tt_address')) {
    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_caretaker_xmpp' => [
                'exclude' => 0,
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tt_address.tx_caretaker_xmpp',
                'config' => [
                    'type' => 'input',
                    'size' => '30',
                ],
            ],
        ]
    );
    ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_caretaker_xmpp;;;;1-1-1');
}
