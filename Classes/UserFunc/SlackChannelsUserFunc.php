<?php

namespace Caretaker\Caretaker\UserFunc;

use Caretaker\Caretaker\Services\Slack\Client;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlackChannelsUserFunc
{
    public function getChannels(&$incomingParameters)
    {
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('caretaker');
        $token = $extConfig['notifications.']['slack.']['token'];

        if (isset($token)) {
            $client = new Client($token);

            $response = json_decode($client->getChannels(), true);
            if (isset($response['channels']) && is_array($response['channels'])) {
                foreach ($response['channels'] as $channel) {
                    $incomingParameters['items'][] = [
                        'label' => $channel['name'],
                        'value' => $channel['id']
                    ];
                }
            }
        }
    }
}
