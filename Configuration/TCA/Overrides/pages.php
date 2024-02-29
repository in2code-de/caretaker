<?php

defined('TYPO3') or die();

use Caretaker\Caretaker\Constants;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(function ($extKey='caretaker', $table='pages') {
    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
            'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instancegroup',
            Constants::doktype_instance_group,
            'doktype-instance-group-default',
        ],
        '1',
        'after'
    );

    ExtensionManagementUtility::addTcaSelectItem(
        $table,
        'doktype',
        [
            'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance',
            Constants::doktype_instance,
            'doktype-instance-default',
        ],
        '1',
        'after'
    );

    ArrayUtility::mergeRecursiveWithOverrule(
        $GLOBALS['TCA'][$table],
        [
            /**
             * add icon for new page type
             */
            'ctrl' => [
                'typeicon_classes' => [
                    Constants::doktype_instance_group => 'doktype-instance-group-default',
                    Constants::doktype_instance => 'doktype-instance-default',
                ],
            ],
            /**
             * add all sysfolder standard fields and tabs to the new page type
             */
            'types' => [
                (string)Constants::doktype_instance_group => [
                    'showitem' => $GLOBALS['TCA'][$table]['types'][PageRepository::DOKTYPE_SYSFOLDER]['showitem'],
                ],
                (string)Constants::doktype_instance => [
                    'showitem' => $GLOBALS['TCA'][$table]['types'][PageRepository::DOKTYPE_SYSFOLDER]['showitem'],
                ],
            ],
        ]
    );
})();
