<?php

namespace Caretaker\Caretaker\UserFunc;

class SlackChannelsUserFunc
{
    public function getChannels(&$incomingParameters)
    {
        $extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['caretaker']);
        $token = $extConfig['notifications.']['slack.']['token'];
        $client = new \Caretaker\Caretaker\services\Slack\Client($token);

        $response = json_decode($client->getChannels(), true);
        if (isset($response['channels']) && is_array($response['channels'])) {
            foreach ($response['channels'] as $channel) {
                $incomingParameters['items'][] = [0 => $channel['name'], 1 => $channel['id']];
            }
        }
    }
}
