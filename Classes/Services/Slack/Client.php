<?php

namespace Caretaker\Caretaker\services\Slack;

class Client
{
    const BASE_URL = 'https://slack.com/api/';

    /**
     * @var string
     */
    protected $token;

    /**
     * ApiClient constructor.
     * @param string $token
     */
    public function __construct($token)
    {
        $this->client = new \GuzzleHttp\Client(
            [
                'base_uri' => self::BASE_URL,
                'headers' => ['content-type' => 'application/json; charset=utf-8'],
            ]
        );

        $this->token = $token;
    }

    /**
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postMessage($options)
    {
        return $this->sendRequest('chat.postMessage', $options);
    }

    /**
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getChannels()
    {
        return $this->sendRequest('conversations.list', ['exclude_archived' => true, 'limit' => 500])->getBody();
    }

    /**
     * @param string $method
     * @param array $options
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function sendRequest($method, $options = null)
    {
        $options['token'] = $this->token;
        return $this->client->get($method . '?' . http_build_query($options));
    }
}
