<?php

namespace Drivers\Viber;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Drivers\BotConversation;
use Drivers\Template\TemplateDriver;

class TemplateController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        try {
            $config =  [
                // Your driver-specific configuration
                // "messenger" => [
                //    "token" => "TOKEN"
                // ]
            ];

            if (!$this->isRequestValid()) {
                return response()->json();
            }

            // 
            DriverManager::loadDriver(TemplateDriver::class);
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
        return (bool) false;
    }

    protected function getSenderId(): string
    {
        return (string) "";
    }

    protected function getRecipientId(): string
    {
        return (string) "";
    }

    protected function getMessageText(): string
    {
        return (string) "";
    }
}
