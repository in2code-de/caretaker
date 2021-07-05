<?php

use Caretaker\Caretaker\services\Slack\Client;

class tx_caretaker_SlackNotificationService extends tx_caretaker_AbstractNotificationService
{
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
     * Constructor
     * reads the service configuration
     */
    public function __construct()
    {
        parent::__construct('slack');

    }

    /**
     * This is called whenever the notification service is called. We have to store all interesting
     * results in an internal structure to use it later.
     */
    public function addNotification($event, $node, $result = null, $lastResult = null)
    {
        if ($node instanceof tx_caretaker_InstanceNode && $result instanceof tx_caretaker_AggregatorResult) {
            if ($result->getNumERROR() === 0 && $result->getNumWARNING() === 0 && $result->getNumUNDEFINED() === 0) {
//              Stop if everything is ok
                return;
            }

            $slackNotificationEnabled = (bool)$node->getProperty('slack_notification');
            $slackChannel = $node->getProperty('slack_notification_channel');

            if ($slackNotificationEnabled && !empty($slackChannel)) {
                $this->notifications[] = [
                    'channel' => $slackChannel,
                    'message' => '*' . $node->getTitle() . '*' . PHP_EOL . $result->getLocallizedInfotext(),
                ];
            }
        }
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
