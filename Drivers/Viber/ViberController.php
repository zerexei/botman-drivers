<?php

namespace Drivers\Viber;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Drivers\BotConversation;
use Drivers\Viber\ViberDriver;

class ViberController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        try {
            $config =  [
                'viber' => [
                    'token' => 'your-viber-access-token',
                ]
            ];

            if (!$this->isRequestValid()) {
                return response()->json();
            }

            // 
            DriverManager::loadDriver(ViberDriver::class);
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
        return (bool) request('token');
    }

    protected function getSenderId(): string
    {
        return (string) request('sender.id');
    }

    protected function getRecipientId(): string
    {
        $parts = explode('/', request()->getPathInfo());
        return (string) end($parts);
    }

    protected function getMessageText(): string
    {
        return (string) request('message.text');
    }
}
