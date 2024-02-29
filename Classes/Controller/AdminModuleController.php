<?php

namespace Caretaker\Caretaker\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
final class AdminModuleController extends ActionController
{
    protected ?ModuleData $moduleData = null;
    protected ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        protected readonly BackendUriBuilder $backendUriBuilder,
        protected readonly PageRenderer $pageRenderer
    ) {
    }

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    public function initializeAction(): void
    {
        $this->moduleData = $this->request->getAttribute('moduleData');
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod.xlf:mlang_tabs_tab'));
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

    /**
     * Assign default variables to ModuleTemplate view
     */
    protected function initializeView(): void
    {
        $this->moduleTemplate->assignMultiple([
            'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
            'timeFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
        ]);
        // Load JavaScript modules
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/context-menu.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/modal.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/beuser/backend-user-listing.js');
    }


    // use Psr\Http\Message\ResponseInterface
    public function debugAction(): ResponseInterface
    {
        $this->view->assign('someVar', 'someContent');
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    // use Psr\Http\Message\ResponseInterface
    public function indexAction(): ResponseInterface
    {
        $this->view->assign('someVar', 'someContent');
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $dropDownButton = $buttonBar->makeDropDownButton()
            ->setLabel('Dropdown')
            ->setTitle('Save')
            ->setIcon($this->iconFactory->getIcon('actions-heart'))
            ->addItem(
                GeneralUtility::makeInstance(DropDownItem::class)
                    ->setLabel('Item')
                    ->setHref('#'),
            );
        $buttonBar->addButton(
            $dropDownButton,
            ButtonBar::BUTTON_POSITION_RIGHT,
            2,
        );

        $this->addMainMenu('index');
        $addUserButton = $buttonBar->makeLinkButton()
            ->setIcon($this->iconFactory->getIcon('actions-plus', Icon::SIZE_SMALL))
            ->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:backendUser.create', 'beuser'))
            ->setShowLabelText(true)
            ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('record_edit', [
                'edit' => ['be_users' => [0 => 'new']],
                'returnUrl' => $this->request->getAttribute('normalizedParams')->getRequestUri(),
            ]));
        $buttonBar->addButton($addUserButton);
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setRouteIdentifier('backend_user_management')
            ->setArguments(['action' => 'index'])
            ->setDisplayName(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:backendUsers', 'beuser'));
        $buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }
    private function setDocHeader(): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $dropDownButton = $buttonBar->makeDropDownButton()
            ->setLabel('Dropdown')
            ->setTitle('Save')
            ->setIcon($this->iconFactory->getIcon('actions-heart'))
            ->addItem(
                GeneralUtility::makeInstance(DropDownItem::class)
                    ->setLabel('Item')
                    ->setHref('#'),
            );
        $buttonBar->addButton(
            $dropDownButton,
            ButtonBar::BUTTON_POSITION_RIGHT,
            2,
        );
    }

    /**
     * Doc header main drop down
     */
    protected function addMainMenu(string $currentAction): void
    {
        $this->uriBuilder->setRequest($this->request);
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('CaretakerModuleMenu');
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:backendUsers', 'beuser'))
                ->setHref($this->uriBuilder->uriFor('index'))
                ->setActive($currentAction === 'index')
        );
        if ($currentAction === 'debug') {
            $menu->addMenuItem(
                $menu->makeMenuItem()
                    ->setTitle(LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang.xlf:backendUserDetails', 'beuser'))
                    ->setHref($this->uriBuilder->uriFor('debug'))
                    ->setActive(true)
            );
        }
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

}
