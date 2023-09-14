<?php

declare(strict_types=1);

namespace Caretaker\Caretaker\Command;

use TYPO3\CMS\Core\Locking\LockFactory;
use Caretaker\Caretaker\Repository\NodeRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RunTestCommand extends Command
{
    protected function configure(): void
    {
        $this->setHelp('This command runs tests for a given node id.')
            ->addArgument(
                'nodeId',
                InputArgument::REQUIRED,
                'The entry node id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nodeRepository = NodeRepository::getInstance();
        $node = $nodeRepository->id2node($input->getArgument('nodeId'));

        if (!$node) {
            return Command::FAILURE;
        }

        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['lockingMode'] != 'disable') {
            $lockObj = GeneralUtility::makeInstance(LockFactory::class)->createLocker('tx_caretaker_update_' . $node->getCaretakerNodeId());

            // TODO: Refactor this when NotificationServices have been implemented
            // no output during scheduler runs
            // tx_caretaker_ServiceHelper::unregisterCaretakerNotificationService('CliNotificationService');

            if ($lockObj->acquire()) {
                $node->updateTestResult();
                $lockObj->release();
            } else {
                return Command::SUCCESS;
            }
        } else {
            $node->updateTestResult();
        }

        // TODO: Refactor this when NotificationServices have been implemented
        // send aggregated notifications
        // $notificationServices = tx_caretaker_ServiceHelper::getAllCaretakerNotificationServices();
        // /** @var tx_caretaker_AbstractNotificationService $notificationService */
        // foreach ($notificationServices as $notificationService) {
        //    $notificationService->sendNotifications();
        // }

        return Command::SUCCESS;
    }
}
