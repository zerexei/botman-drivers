<?php

namespace Drivers\WhatsApp;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;

use Illuminate\Support\Collection;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// src: https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages
// https://developers.facebook.com/docs/whatsapp/messaging-limits
class WhatsAppDriver extends  HttpDriver
{
    protected $endpoint = 'G';

    /** @var string */
    const DRIVER_NAME = 'WhatsApp';

    public $messages = [];

    /**
     * This method is used to carry out the initial setup
     * @param Request $request
     */
    public function buildPayload(Request $request)
    {
        $this->payload = new ParameterBag((array) json_decode($request->getContent(), true));
        $this->event = Collection::make((array) $this->payload->get('entry', [null])[0]);
        // $this->signature = $request->headers->get('X_HUB_SIGNATURE', '');
        $this->config = Collection::make($this->config->get('whatsApp', []));
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
            $this->payload->get('object') !== "whatsapp_business_account",
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
                $this->getSenderId(),
                $this->getReceiverId(),
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
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $this->getReceiverId(),
            "senderId" => $this->getSenderId(),
            ...$message->toArray()
        ];
    }

    /**
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        // 'https://graph.facebook.com/v24.0/FROM_PHONE_NUMBER_ID/messages'
        $url = $this->endpoint . $this->getSenderId() . '/messages';
        $headers = $this->getHeaders();
        $response = $this->http->post($url, [], $payload, $headers);

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
        $url = $this->endpoint . "/" . $endpoint;
        $headers = $this->getHeaders();
        $response = $this->http->post($url, [], $parameters, $headers);

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
            'Authorization: Bearer ' . $this->config->get('token')
        ];
    }

    public function getMessageText(): string
    {
        $message = request('entry.0.changes.0.value.messages.0', ['type' => 'text']);

        if ($message['type'] ===  'interactive') {
            return $message['interactive']['list_reply']["title"] ?? "";
        }

        return $message['text']['body'] ?? "";
    }

    // bot
    public function getSenderId(): string
    {
        return (string) request('entry.0.changes.0.value.metadata.phone_number_id');
    }

    // user
    public function getReceiverId(): string
    {
        return (string) request('entry.0.changes.0.value.messages.0.from');
    }
}
