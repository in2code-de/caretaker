<?php

namespace Caretaker\Caretaker\Service\Notification;

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Http\ApplicationType;
use Caretaker\Caretaker\Entity\Node\InstanceNode;
use Caretaker\Caretaker\Entity\Result\AggregatorResult;
use Caretaker\Caretaker\Entity\Result\ResultMessage;
use Caretaker\Caretaker\Service\Slack\Client;
use Caretaker\Caretaker\Service\SlackNotificationTimerService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SlackNotificationService extends AbstractNotificationService
{
    public const SLACK_NOTIFICATION_TYPE_EVERYTIME = 0;
    public const SLACK_NOTIFICATION_TYPE_BY_INTERVAL = 1;

    /**
     * Array of notifications to send to slack
     *
     *    $notifications = [
     *        [
     *            'channel' => 'channel id here',
     *            'message' => 'message here'
     *        ],
     *        ...
     *    ];
     *
     * @var array
     */
    protected $notifications = [];

    /**
     * @var null|Logger
     */
    protected $logger = null;

    /**
     * @var null|SlackNotificationTimerService
     */
    protected $slackNotificationTimerService = null;

    /**
     * Constructor
     * reads the service configuration
     */
    public function __construct()
    {
        parent::__construct('slack');
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->slackNotificationTimerService = GeneralUtility::makeInstance(SlackNotificationTimerService::class);
    }

    /**
     * This is called whenever the notification service is called. We have to store all interesting
     * results in an internal structure to use it later.
     */
    public function addNotification($event, $node, $result = null, $lastResult = null)
    {
        if ($node instanceof InstanceNode && $result instanceof AggregatorResult) {
            $slackNotificationEnabled = (bool)$node->getProperty('slack_notification');
            $slackChannel = $node->getProperty('slack_notification_channel');

            if ($result->getNumERROR() === 0 && $result->getNumWARNING() === 0) {
                // set notification_log for the current node as deleted
                if ($this->slackNotificationTimerService->hasActiveLog($node->getUid())) {
                    if ($node->getProperty('slack_notification_if_fixed')) {
                        $this->notifications[] = [
                            'channel' => $slackChannel,
                            'message' => $this->getErrorsFixedMessage($node),
                        ];
                    }

                    $currentActiveLog = $this->slackNotificationTimerService->getActiveLog($node->getUid());
                    $this->slackNotificationTimerService->deleteLog((int)$currentActiveLog['uid']);
                }
                return;
            }

            if ($slackNotificationEnabled && !empty($slackChannel)) {
                if ((int)$node->getProperty('slack_notification_interval_type') === self::SLACK_NOTIFICATION_TYPE_BY_INTERVAL) {
                    $sendNotification = $this->shouldSendIntervalNotification($node, $result);
                } else {
                    $sendNotification = true;
                }

                if ($sendNotification) {
                    $this->notifications[] = [
                        'channel' => $slackChannel,
                        'message' => $this->getSlackErrorMessage($node, $result),
                    ];
                }
            }
        }
    }

    private function getSlackErrorMessage($node, $result): string {
        $message = '*Fehler auf: ' . $node->getTitle() . '*' . PHP_EOL . ' ' . PHP_EOL;
        $isFirstError = true;
        $isFirstUndefined = true;

            /** @var ResultMessage $submessage */
        foreach ($result->getSubMessages() as $submessage) {
                switch ($submessage->getText()) {
                    case 'LLL:EXT:caretaker/Resources/Private/Language/locallang.xlf:aggregator_result_submessage_error':
                        if ($isFirstError) {
                            $message .= PHP_EOL . PHP_EOL . ':x: *Fehler:* :x:' . PHP_EOL;
                        }

                        $message .= 'Testgruppe: *' . implode(', ', $submessage->getValues()) . '*' . PHP_EOL;
                        $isFirstError = false;
                        break;
                    case 'LLL:EXT:caretaker/Resources/Private/Language/locallang.xlf:aggregator_result_submessage_undefined':
                        if ($isFirstUndefined) {
                            $message .= PHP_EOL . PHP_EOL . ':warning: *Undefinierter Zustand:* :warning:' . PHP_EOL;
                        }

                        $message .= 'Testgruppe: *' . implode(', ', $submessage->getValues()) . '*' . PHP_EOL;
                        $isFirstUndefined = false;
                        break;
                }
            }

        return  $message;
    }

    private function getErrorsFixedMessage($node): string {
        return ':white_check_mark: Fehler auf *' . $node->getTitle() . '* wurden behoben!';
    }

    private function shouldSendIntervalNotification($node, $result): bool {

        if ($this->slackNotificationTimerService->hasActiveLog($node->getProperty('uid'))) {
            $lastNotificationLog = $this->slackNotificationTimerService->getActiveLog($node->getProperty('uid'));
            $currentHash = $result->getResultHash();
            // problems have not changed
            if ($lastNotificationLog['result_hash'] === $result->getResultHash()) {
                $logExpireTime = (int)$lastNotificationLog['tstamp'] + ($node->getProperty('slack_notification_interval') * 60 * 60);
                if (time() > $logExpireTime) {
                    // notification is expired

                    // - set old tx_caretaker_notification_log as deleted
                    $this->slackNotificationTimerService->deleteLog((int)$lastNotificationLog['uid']);

                    // - create new tx_caretaker_notification_log with current values
                    $this->slackNotificationTimerService->createLog($node->getProperty('uid'), $result->getResultHash());

                    // - send a notification
                    return true;
                } else {
                    // nothing to do!
                    return false;
                }
            } else {
                // resend notification if the result hash ( has changed

                // - set the old tx_caretaker_notification_log as deleted
                $this->slackNotificationTimerService->deleteLog((int)$lastNotificationLog['uid']);

                // - create a new tx_caretaker_notification_log with current values
                $this->slackNotificationTimerService->createLog($node->getProperty('uid'), $result->getResultHash());

                // - send a new notification ? maybe add an option to change the behaviour
                return true;
            }

        } else {
            // create new notification log entry
            $this->slackNotificationTimerService->createLog($node->getProperty('uid'), $result->getResultHash());
            $this->logger->debug('New intervall notification was sent.', ['node' => $node]);
            return true;
        }
    }

    /**
     * this override sends slack notifications also if executed via "refresh" in the backend
     * (if the basic slack notification is enabled in the extension settings).
     *
     * @return bool
     */
    public function isEnabled()
    {
        $enabled = (bool)$this->getConfigValue('enabled');
        return $enabled === true && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend();
    }

    /**
     * Send all stored notifications
     */
    public function sendNotifications()
    {
        $token = $this->getConfigValue('token');
        $client = new Client($token);

        foreach ($this->notifications as $notification) {
            $options = [
                'as_user' => true,
                'channel' => $notification['channel'],
                'text' => $notification['message'],
            ];

            $client->postMessage($options);
        }
    }
}
