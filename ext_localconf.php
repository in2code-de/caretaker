<?php

defined('TYPO3') or die();

use Caretaker\Caretaker\Constants;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(function () {
    $icons = [
        'doktype-instance-group-default' => 'EXT:caretaker/Resources/Public/Icons/instancegroup.png',
        'doktype-instance-default' => 'EXT:caretaker/Resources/Public/Icons/instance.png',
    ];
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon(
            $identifier,
            BitmapIconProvider::class,
            ['source' => $path]
        );
    }

    /**
     * Allow backend users to drag and drop the new page type:
     */
    ExtensionManagementUtility::addUserTSConfig('
        options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . Constants::doktype_instance_group . ', ' . Constants::doktype_instance . ')
        options.pageTree.doktypesToShowInNewPageDragArea := removeFromList(' . PageRepository::DOKTYPE_MOUNTPOINT . ', ' . PageRepository::DOKTYPE_SPACER . ', ' . PageRepository::DOKTYPE_RECYCLER . ')
    ');
})();
