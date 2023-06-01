<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
// add API-Key to fe_user record
ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    array(
        'tx_caretaker_api_key' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:fe_users.tx_caretaker_api_key',
            'config' => array(
                'type' => 'input',
            ),
        ),
    )
);
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'tx_caretaker_api_key');
