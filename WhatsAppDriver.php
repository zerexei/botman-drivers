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
class WhatsAppDriver extends  HttpDriver
{
    protected $endpoint = 'https://graph.facebook.com/v17.0/';

    /** @var string */
    // DRIVER_NAME needs to match filename w/out "Driver"
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
    public function matchesRequest()
    {
        if ($this->payload->get('object') !== "whatsapp_business_account") {
            return false;
        }

        return (bool) $this->getMessageText();
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

            // FIXME: update to sender/receiver id
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
     * @param IncomingMessage $message
     * @return \BotMan\BotMan\Messages\Incoming\Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        $answer = Answer::create($message->getText())->setMessage($message);

        return $answer;
    }

    /**
     * @param string|Question|OutgoingMessage $message
     * @param IncomingMessage $matchingMessage
     * @param array $additionalParameters
     * @return Response
     */
    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $additionalParameters['type'] ??= "text";

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

        if ($additionalParameters['type'] === 'image') {
            $parameters['type'] = "image";
            $parameters['image'] = [
                'link' => $message->getText()
            ];
        }

        if (in_array($additionalParameters['type'], ['button', 'quick-reply'])) {
            $parameters['type'] = "interactive";

            // parse buttons
            $buttons = $additionalParameters['buttons'];
            $buttons = array_slice($buttons, 0, 10);
            $buttons = array_map(function ($button) {
                return [
                    "id" =>  $button['id'],
                    "title" => $button['title'],
                ];
            }, $buttons);

            // 
            $parameters['interactive'] = [
                'type' => 'list',
                "body" => [
                    "text" => $message->getText()
                ],
                'action' => [
                    "sections" => [
                        [
                            "title" => "Options",
                            "rows" => $buttons
                        ]
                    ],
                    "button" => "Options",
                ]
            ];
        }

        return $parameters;
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
        $type = request()->entry[0]["changes"][0]['value']['messages'][0]['type'] ?? "";

        switch ($type) {
            case 'text':
                return request()->entry[0]["changes"][0]['value']['messages'][0]['text']['body'] ?? "";

            case 'interactive':
                $reply = request()->entry[0]["changes"][0]['value']['messages'][0]['interactive']['list_reply'] ?? null;
                return  $reply['title'] ?? "";

            default:
                return "";
        }
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
