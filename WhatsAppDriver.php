<?php

namespace App\Conversations\Drivers;

use BotMan\BotMan\Drivers\HttpDriver;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Facebook\Extensions\User;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WhatsAppDriver extends  HttpDriver
{
    // https://developers.facebook.com/docs/whatsapp/cloud-api/reference/messages#text-messages
    // https://www.youtube.com/watch?v=eiAiasR1LGE
    protected $facebookProfileEndpoint = 'https://graph.facebook.com/v17.0/';

    const MESSAGING_TYPE = "RESPONSE";

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
        $this->content = $request->getContent();
        $this->config = Collection::make($this->config->get('whatsapp_business_account', []));
    }

    /**
     * Determine if the request is for this driver.
     *
     * @return bool
     */
    public function matchesRequest(): bool
    {
        if ($this->payload->get('object') !== "whatsapp_business_account") {
            return false;
        }

        return (bool) $this->getRequestMessage();
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages(): array
    {
        if (empty($this->messages)) {
            $userMessage = $this->getRequestMessage();
            $userId = $this->getSenderId();
            $senderId = $this->getReceiverId();
            $message = new IncomingMessage($userMessage, $userId, $senderId, $this->payload);
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
     * @param IncomingMessage $message
     * @return \BotMan\BotMan\Messages\Incoming\Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        return Answer::create($message->getText())->setMessage($message);
    }

    /**
     * @param string|Question|OutgoingMessage $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return Response
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = []): array
    {
        $parameters = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $this->getReceiverId(),
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $message->getText()
            ]
        ];

        return $parameters;
    }

    /**
     * @param mixed $payload
     * @return Response
     */
    public function sendPayload($payload)
    {
        // 'https://graph.facebook.com/v17.0/FROM_PHONE_NUMBER_ID/messages'
        $response = $this->http->post(
            $this->facebookProfileEndpoint . $this->getSenderId() . '/messages',
            [],
            $payload,
            headers: [
                'Authorization: Bearer ' . $this->config->get('token'),
            ]
        );

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
        return $this->http->post($this->facebookProfileEndpoint . $endpoint, [], $parameters, headers: [
            'Authorization: Bearer ' . $this->config->get('token'),
        ]);
    }

    /**
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->config->get('token'));
    }


    protected function getRequestMessage(): string
    {
        return $this->event->get("changes")[0]['value']['messages'][0]['text']['body'] ?? "";
    }


    protected function getSenderId(): string
    {
        return $this->event->get("changes")[0]['value']['metadata']['phone_number_id'] ?? "";
    }

    protected function getReceiverId(): string
    {
        return $this->event->get("changes")[0]['value']['messages'][0]['from'] ?? "";
    }
}
