<?php

$extConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['caretaker'] ?? [];
$advancedNotificationsEnabled = false;
if (isset($extConfig['notifications.'])) {
    $advancedNotificationsEnabled = $extConfig['notifications.']['advanced.']['enabled'] == '1' ?? false;
}

if ($advancedNotificationsEnabled) {
    $GLOBALS['TCA']['tx_caretaker_exitpoints'] = [
        'ctrl' => [
            'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints',
            'label' => 'name',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'default_sortby' => 'ORDER BY name',
            'delete' => 'deleted',
            'rootLevel' => -1,
            'enablecolumns' => [
                'disabled' => 'hidden',
            ],
            'iconfile' => 'EXT:caretaker/Resources/Public/Icons/exitpoint.png',
            'searchFields' => 'name, description',
        ],
        'interface' => [
            'showRecordFieldList' => 'hidden,id,name,description,service,config',
        ],
        'columns' => [
            'hidden' => [
                'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
                'config' => [
                    'type' => 'check',
                    'default' => '0',
                ],
            ],
            'id' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.id',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'eval' => 'nospace,unique',
                    'required' => true,
                ],
            ],
            'name' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.name',
                'config' => [
                    'type' => 'input',
                    'size' => '255',
                ],
            ],
            'description' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.description',
                'config' => [
                    'type' => 'text',
                    'cols' => '50',
                    'rows' => '5',
                ],
            ],
            'service' => [
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.service',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => array_merge(
                        [
                            0 => ['LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.service.select_exitpoint', ''],
                        ],
                        // TODO: Implement this helper
                        // \tx_caretaker_ServiceHelper::getTcaExitPointServiceItems()
                    ),
                    'size' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],
            'config' => [
                'displayCond' => 'FIELD:service:REQ:true',
                'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_exitpoints.config',
                'config' => [
                    'type' => 'flex',
                    'ds_pointerField' => 'service',
                    // TODO: Implement this helper
                    // 'ds' => \tx_caretaker_ServiceHelper::getTcaExitPointConfigDs(),
                ],
            ],
        ],
        'types' => [
            '0' => ['showitem' => 'id, name, description, service, config'],
        ],
        'palettes' => [
            '1' => ['showitem' => 'hidden'],
        ],
    ];
}
