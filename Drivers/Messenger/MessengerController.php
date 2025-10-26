<?php

namespace Drivers\Viber;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Drivers\BotConversation;
use Drivers\Messenger\MessengerDriver;

class MessengerController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        try {
            $config =  [
                "messenger" => [
                    'token' => 'your-meta-app-access-token',
                ]
            ];

            if (!$this->isRequestValid()) {
                return response()->json();
            }

            // 
            DriverManager::loadDriver(MessengerDriver::class);
            $botman = BotManFactory::create($config, new LaravelCache());

            //
            $botman->fallback(fn(BotMan $bot)  => $bot->startConversation(new BotConversation));

            //
            $botman->listen();
        } catch (\Throwable $th) {
            // laravel respose class
            return response()->json();
        }
    }

    protected function isRequestValid()
    {
        return  in_array(false, [
            $this->isConfigured(),
            $this->getSenderId(),
            $this->getRecipientId(),
            $this->getMessageText(),
        ]);
    }

    protected function isConfigured(): bool
    {
        return (bool) request('token', false);
    }

    protected function getSenderId(): string
    {
        return (string) request('entry.0.messaging.0.sender.id');
    }

    protected function getRecipientId(): string
    {
        return (string) request('entry.0.messaging.0.recipient.id');
    }

    public function isPostback(): bool
    {
        return (bool) request('entry.0.messaging.0.postback');
    }

    protected function getMessageText(): string
    {
        if ($this->isPostback()) {
            return (string) request('entry.0.messaging.0.postback.payload');
        }

        return (string) request('entry.0.messaging.0.message.text');
    }
}
