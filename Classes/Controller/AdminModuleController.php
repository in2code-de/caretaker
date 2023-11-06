<?php

namespace Caretaker\Caretaker\Controller;

use TYPO3\CMS\Backend\Attribute\Controller;

// the module template will be initialized in handleRequest()
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

#[Controller]
final class AdminModuleController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        // ...
    )
    {
    }

    public function handleRequest(
        ServerRequestInterface $request
    ): ResponseInterface {
        $languageService = $GLOBALS['LANG'];

        $this->menuConfig($request);
        $moduleTemplate = $this->moduleTemplateFactory->create($request);
        // setUpDocHeader() is documented below
        $this->setUpDocHeader($moduleTemplate);

        $title = $languageService->sL(
            'LLL:EXT:examples/Resources/Private/Language/AdminModule/locallang_mod.xlf:mlang_tabs_tab'
        );
        switch ($this->MOD_SETTINGS['function']) {
            case 'debug':
                $moduleTemplate->setTitle(
                    $title,
                    $languageService->sL(
                        'EXT:examples/Resources/Private/Language/AdminModule/locallang.xlf:module.menu.debug'
                    )
                );
                return $this->debugAction($moduleTemplate);
            case 'password':
                $moduleTemplate->setTitle(
                    $title,
                    $languageService->sL(
                        'EXT:examples / Resources /private/Language / AdminModule / locallang . xlf:module . menu . password'
                    )
                );
                return $this->passwordAction($moduleTemplate);
            default:
                $moduleTemplate->setTitle(
                    $title,
                    $languageService->sL(
                        'EXT:examples/Resources/Private/Language/AdminModule/locallang.xlf:module.menu.log'
                    )
                );
                return $this->logAction($moduleTemplate);
        }
    }

    private function setDocHeader(string $active)
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $list = $buttonBar->makeLinkButton()
            ->setHref('<uri-builder-path>')
            ->setTitle('A Title')
            ->setShowLabelText('Link')
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-extension-import', Icon::SIZE_SMALL));
        $buttonBar->addButton($list, ButtonBar::BUTTON_POSITION_LEFT, 1);
    }
}
