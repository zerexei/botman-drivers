<?php

namespace Drivers\Viber;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;

use Illuminate\Support\Collection;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// src: https://developers.viber.com/docs/api/rest-bot-api
// https://creators.viber.com/
class ViberDriver extends  HttpDriver
{
    protected $endpoint = "https://chatapi.viber.com/pa";

    /** @var string */
    const DRIVER_NAME = 'Viber';

    public $messages = [];

    /**
     * This method is used to carry out the initial setup
     */
    public function buildPayload(Request $request)
    {
        $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
        $this->event = Collection::make($this->payload);
        $this->config = Collection::make($this->config->get('viber'));
    }

    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     */
    public function matchesRequest()
    {
        return in_array(false, [
            $this->isConfigured(),
            $this->getSenderId(),
            $this->getReceiverId(),
            $this->getMessageText()
        ]);
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages()
    {
        if (empty($this->messages)) {
            $message = new IncomingMessage(
                $this->getMessageText(),
                $this->getReceiverId(),
                $this->getSenderId(),
                $this->payload
            );
            $this->messages = [$message];
        }

        return $this->messages;
    }

    /**
     * @param string|Question|OutgoingMessage $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return Response
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        return [
            'receiver' => $this->getReceiverId(),
            'min_api_version' => 7,
            'sender' => [
                'name' => 'app-name', // The sender's name
                'avatar' => 'sender-avatar-path', // The sender's avatar URL
            ],
            ...$message->toArray()
        ];
    }

    /**
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        $response = $this->http->post("$this->endpoint/send_message", [], $payload, $this->getHeaders(), true);

        if (!$response->isSuccessful()) {
            // log error
        }

        return $response;
    }


    /**
     * Low-level method to perform driver specific API requests.
     *
     * @param  string  $endpoint
     * @param  array  $parameters
     * @param  IncomingMessage  $matchingMessage
     * @return Response
     */
    public function sendRequest($endpoint, array $parameters, IncomingMessage $matchingMessage)
    {
        $response = $this->http->post("{$this->endpoint}/{$endpoint}", [], $parameters, $this->getHeaders());

        if (!$response->isSuccessful()) {
            // log error
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->config->get('token'));
    }

    public function getHeaders(): array
    {
        return [
            'Accept:application/json',
            'Content-Type:application/json',
            'X-Viber-Auth-Token: ' . $this->config->get('token'),
        ];
    }

    public function getMessageText(): string
    {
        return (string) request('message.text');
    }

    // bot
    public function getSenderId(): string
    {
        // laravel request class
        $parts = explode('/', request()->getPathInfo());
        return (string) end($parts);
    }

    // user
    public function getReceiverId(): string
    {
        return (string) request('sender.id');
    }
}
