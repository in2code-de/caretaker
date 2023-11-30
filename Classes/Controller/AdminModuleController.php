<?php

namespace Caretaker\Caretaker\Controller;

use Caretaker\Caretaker\Entity\Node\AggregatorNode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[Controller]
final class AdminModuleController extends ActionController
{
    private int $pageUid;
    private array $exampleConfig;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        protected readonly ExtensionConfiguration $extensionConfiguration,
        protected readonly PasswordHashFactory $passwordHashFactory,
        protected readonly ResourceFactory $resourceFactory,
        protected readonly FileRepository $fileRepository,
        protected readonly ConnectionPool $connectionPool,
        protected readonly DataHandler $dataHandler,
    )
    {
    }


    private function setDocHeader(string $active)
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $list = $buttonBar->makeLinkButton()
            ->setHref('<uri-builder-path>')
            ->setTitle('Caretaker')
            ->setShowLabelText('Link')
            ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-extension-import', Icon::SIZE_SMALL));
        $buttonBar->addButton($list, ButtonBar::BUTTON_POSITION_LEFT, 1);
    }

    public function indexAction(): ResponseInterface
    {
        $view = $this->initializeModuleTemplate($this->request);

        $view->assignMultiple(
            [

            ]
        );
        return $view->renderResponse();
    }



    public function debugAction(): ResponseInterface
    {
        $view = $this->initializeModuleTemplate($this->request);

        $view->assignMultiple(
            [
            ]
        );
        return $view->renderResponse();
    }

    /**
     * Generates the action menu
     */
    protected function initializeModuleTemplate(
        ServerRequestInterface $request
    ): ModuleTemplate {
        $menuItems = [
            'debug' => [
                'controller' => 'AdminModule',
                'action' => 'debug',
                'label' => $this->getLanguageService()->sL('LLL:EXT:caretaker/Resources/Private/Language/locallang_mod.xlf:adminModuleController.debug'),
            ]
        ];
        $view = $this->moduleTemplateFactory->create($request);

        $menu = $view->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('AdminModuleMenu');

        $context = '';
        foreach ($menuItems as $menuItemConfig) {
            $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'];
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref($this->uriBuilder->reset()->uriFor(
                    $menuItemConfig['action'],
                    [],
                    $menuItemConfig['controller']
                ))
                ->setActive($isActive);
            $menu->addMenuItem($menuItem);
            if ($isActive) {
                $context = $menuItemConfig['label'];
            }
        }

        $view->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);

        $view->setTitle(
            $this->getLanguageService()->sL('LLL:EXT:caretaker/Resources/Private/Language/Module/locallang_mod.xlf:adminModuleController.moduleTitle'),
            $context
        );

        $permissionClause = $this->getBackendUserAuthentication()->getPagePermsClause(Permission::PAGE_SHOW);
        $this->pageUid = (int)($this->request->getQueryParams()['id'] ?? 0);
        $pageRecord = BackendUtility::readPageAccess(
            $this->pageUid,
            $permissionClause
        );
        if ($pageRecord) {
            $view->getDocHeaderComponent()->setMetaInformation($pageRecord);
        }
        $view->setFlashMessageQueue($this->getFlashMessageQueue());

        return $view;
    }
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    protected function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
