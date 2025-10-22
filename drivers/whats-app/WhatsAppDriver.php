<?php

namespace App\Modules\BotMan;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\User;

use Illuminate\Support\Collection;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages
// https://developers.facebook.com/docs/whatsapp/messaging-limits
// DRIVER_NAME needs to match filename w/out "Driver"
class WhatsAppDriver extends  HttpDriver
{
    protected $endpoint = 'https://graph.facebook.com/v17.0/';

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
        $this->config = Collection::make($this->config->get('whatsapp', []));
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
            $userMessage = $this->getMessageText();
            $senderId = $this->getSenderId(); // app
            $receiverId = $this->getReceiverId(); // user

            $message = new IncomingMessage($userMessage, $receiverId, $senderId, $this->payload);
            $this->messages = [$message];
        }

        return $this->messages;
    }

    /**
     * Retrieve User information.
     * @param IncomingMessage $matchingMessage
     * @return UserInterface
     */
    public function getUser(IncomingMessage $matchingMessage)
    {
        return new User($matchingMessage->getSender());
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
            ...$message->toArray()
        ];
    }

    /**
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        // 'https://graph.facebook.com/v17.0/FROM_PHONE_NUMBER_ID/messages'
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
        $message = $this->event->get("changes")[0]['value']['messages'][0];

        if ($message['type'] ===  'interactive') {
            return $message['interactive']['list_reply']["title"] ?? "";
        }

        return $message['text']['body'] ?? "";
    }

    // app
    public function getSenderId(): string
    {
        return (string) $this->event->get("changes")[0]['value']['metadata']['phone_number_id'] ?? "";
    }

    // user
    public function getReceiverId(): string
    {
        return (string) $this->event->get("changes")[0]['value']['messages'][0]['from'] ?? "";
    }
}
