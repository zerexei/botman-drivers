<?php

namespace Drivers\Template;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;

use Illuminate\Support\Collection;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TemplateDriver extends  HttpDriver
{
    protected $endpoint = "";

    /** @var string */
    const DRIVER_NAME = 'Template'; // should match file name

    public $messages = [];

    /**
     * This method is used to carry out the initial setup
     */
    public function buildPayload(Request $request)
    {
        $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
        $this->event = Collection::make($this->payload);
        $this->config = Collection::make($this->config->get('config-key', []));
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
            // ...
            ...$message->toArray()
        ];
    }

    /**
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        $response = $this->http->post("$this->endpoint", [], $payload, $this->getHeaders(), true);

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
        return (bool) false;
    }

    public function getHeaders(): array
    {
        return [
            // ...
        ];
    }

    public function getMessageText(): string
    {
        return (string) "";
    }

    // bot
    public function getSenderId(): string
    {
        return (string) "";
    }

    // user
    public function getReceiverId(): string
    {
        return (string) "";
    }
}
