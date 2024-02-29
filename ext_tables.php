<?php

defined('TYPO3') or die();

use Caretaker\Caretaker\Constants;

(function ($extKey='caretaker') {
    /**
     * Add new page types
     */
    $GLOBALS['PAGES_TYPES'] = [
        Constants::doktype_instance_group => [
            'type' => 'web',
            'allowedTables' => '*',
        ],
        Constants::doktype_instance => [
            'type' => 'web',
            'allowedTables' => '*',
        ],
    ];
})();
