<?php

$GLOBALS['TCA']['tx_caretaker_instance_override'] = [
    'ctrl' => [
        'title' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override',
        'label' => 'type',
        'label_alt' => 'test,curl_option',
        'label_userFunc' => 'Caretaker\\Caretaker\\UserFunc\\LabelUserFunc->getLabel',
        'type' => 'type',
        'hideTable' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'rootLevel' => -1,
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'disabled',
        ],
    ],
    'interface' => [
        'showitem' => '',
    ],
    'columns' => [
        'type' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.type.test_configuration',
                        'value' => 'test_configuration',
                    ],
                    [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.type.curl_option',
                        'value' => 'curl_option',
                    ],
                ],
                'default' => 'test_configuration',
            ],
        ],
        'test' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.test',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_caretaker_test',
            ],
            'onChange' => 'reload',
        ],
        'test_hidden' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.test_hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'test_configuration' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.test_configuration',
            'config' => [
                'type' => 'flex',
                'ds_pointerField' => 'test',
                // TODO: Implement this helper
                // 'ds' => \tx_caretaker_ServiceHelper::getTcaTestConfigDsWithIds(),
            ],
            'displayCond' => 'FIELD:test:REQ:true',
        ],
        'curl_option' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_option',
            'config' => [
                'type' => 'select',
                'size' => '1',
                'max' => '1',
                'items' => [
                    ['label' => '', 'value' => ''],
                    ['label' => 'CURLOPT_SSL_VERIFYPEER', 'value' => 'CURLOPT_SSL_VERIFYPEER'],
                    ['label' => 'CURLOPT_SSL_VERIFYHOST', 'value' => 'CURLOPT_SSL_VERIFYHOST'],
                    ['label' => 'CURLOPT_TIMEOUT_MS', 'value' => 'CURLOPT_TIMEOUT_MS'],
                    ['label' => 'CURLOPT_INTERFACE', 'value' => 'CURLOPT_INTERFACE'],
                    ['label' => 'CURLOPT_USERPWD (user:password)', 'value' => 'CURLOPT_USERPWD'],
                    ['label' => 'CURLOPT_HTTPAUTH', 'value' => 'CURLOPT_HTTPAUTH'],
                ],
                'renderType' => 'selectSingle',
            ],
            'onChange' => 'reload',
        ],
        'curl_value_int' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value',
            'config' => [
                'type' => 'number',
            ],
            'displayCond' => 'FIELD:curl_option:=:CURLOPT_TIMEOUT_MS',
        ],
        'curl_value_string' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
            'displayCond' => 'FIELD:curl_option:IN:CURLOPT_INTERFACE,CURLOPT_USERPWD',
        ],
        'curl_value_bool' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value.true',
                        'value' => 'true'
                    ],
                    [
                        'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value.false',
                        'value' => 'false'
                    ],
                ],
            ],
            'displayCond' => [
                'OR' => [
                    'FIELD:curl_option:=:CURLOPT_SSL_VERIFYPEER',
                    'FIELD:curl_option:=:CURLOPT_SSL_VERIFYHOST',
                ],
            ]
        ],
        'curl_value_httpauth' => [
            'label' => 'LLL:EXT:caretaker/Resources/Private/Language/locallang_db.xlf:tx_caretaker_instance_override.curl_value',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => 'CURLAUTH_ANY', 'value' => CURLAUTH_ANY],
                    ['label' => 'CURLAUTH_ANYSAFE', 'value' => CURLAUTH_ANYSAFE],
                    ['label' => 'CURLAUTH_BASIC', 'value' => CURLAUTH_BASIC],
                    ['label' => 'CURLAUTH_DIGEST', 'value' => CURLAUTH_DIGEST],
                    ['label' => 'CURLAUTH_GSSNEGOTIATE', 'value' => CURLAUTH_GSSNEGOTIATE],
                    ['label' => 'CURLAUTH_NTLM', 'value' => CURLAUTH_NTLM],
                ],
            ],
            'displayCond' => 'FIELD:curl_option:=:CURLOPT_HTTPAUTH',
        ],
        'instance' => [
            'label' => 'Instance',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_caretaker_instance',
            ],
        ],
    ],
    'types' => [
        'test_configuration' => [
            'showitem' => 'type,test,test_hidden,test_configuration,--palette--;;instance',
        ],
        'curl_option' => [
            'showitem' => 'type,curl_option,curl_value_int,curl_value_string,curl_value_bool,curl_value_httpauth,--palette--;;instance',
        ],
    ],
    'palettes' => [
        'instance' => [
            'showitem' => 'instance',
            'isHiddenPalette' => true,
        ],
    ],
];
