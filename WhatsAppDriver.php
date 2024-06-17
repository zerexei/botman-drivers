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
    protected $facebookProfileEndpoint = 'https://graph.facebook.com/v17.0/';

    const MESSAGING_TYPE = "RESPONSE";

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

        return (bool) $this->getRequestMessage();
    }

    /**
     * Retrieve the chat message.
     *
     * @return array
     */
    public function getMessages()
    {
        if (empty($this->messages)) {
            $userMessage = $this->getRequestMessage();
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
     * @param IncomingMessage $message
     * @return \BotMan\BotMan\Messages\Incoming\Answer
     */
    public function getConversationAnswer(IncomingMessage $message)
    {
        $answer = Answer::create($message->getText())->setMessage($message);

        if (str_starts_with($message->getText(), 'go-to-block:')) {
            $answer->setInteractiveReply(true);
        }

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
        if (!isset($additionalParameters['type'])) {
            $additionalParameters['type'] = "text";
        }

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
            // unset($parameters['text']);

            $parameters['type'] = "image";
            $parameters['image'] = [
                'link' => $message->getText()
            ];
        }

        // https://www.youtube.com/watch?v=BLfNT9rSZ_M
        if (in_array($additionalParameters['type'], ['button', 'quick-reply'])) {
            $parameters['type'] = "interactive";

            $buttons = collect($additionalParameters['buttons'])
                ->slice(0, 10)
                ->map(function ($button) {
                    $payload = [
                        "id" =>  $button['id'],
                        "title" => $button['title'],
                    ];

                    if ($button['type'] === 'go-to-block') {
                        $payload['id'] = "go-to-block:" . $button['value'];
                    }

                    return $payload;
                })->toArray();

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
        $url = $this->facebookProfileEndpoint . $this->getSenderId() . '/messages';
        $headers = ['Authorization: Bearer ' . $this->config->get('token')];
        $response = $this->http->post($url, [], $payload, $headers);
        Log::error("sendPayload", [$payload]);

        if (!$response->isSuccessful()) {
            Log::error("sendPayloadddd", [$payload, $this->config->get('token'), $response->getContent()]);
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
        $url = $this->facebookProfileEndpoint . $this->getSenderId() . '/messages';
        $headers = ['Authorization: Bearer ' . $this->config->get('token')];
        $response = $this->http->post($url, [], $parameters, $headers);

        if (!$response->isSuccessful()) {
            Log::error("sendRequest", [$parameters, $this->config->get('token'), $response->getContent()]);
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

    public function getRequestMessage(): string
    {
        $type = request()->entry[0]["changes"][0]['value']['messages'][0]['type'] ?? "";
        switch ($type) {
            case 'text':
                return request()->entry[0]["changes"][0]['value']['messages'][0]['text']['body'] ?? "";

            case 'interactive':
                $reply = request()->entry[0]["changes"][0]['value']['messages'][0]['interactive']['list_reply'];
                $input = str_starts_with($reply['id'], 'go-to-block:') ? $reply['id'] : "";
                return $input ? $input : $reply['title'] ?? "";

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
