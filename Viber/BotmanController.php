<?php

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use Viber\ViberDriver;

class BotmanController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            $config =  [
                'viber' => [
                    'token' => 'your-viber-access-token',
                ]
            ];

            // 
            DriverManager::loadDriver(ViberDriver::class);
            $botman = BotManFactory::create($config, new LaravelCache());

            //
            $botman->fallback(fn(BotMan $bot)  => $bot->startConversation(new Conversation));

            //
            $botman->listen();
        } catch (\Throwable $th) {
            return response()->json([], 200);
        }
    }

    protected function isRequestValid()
    {
        return  in_array(false, [
            $this->getSenderId(),
            $this->getRecipientId(),
            $this->getMessageText(),
        ]);
    }

    protected function getSenderId(): string
    {
        return (string) (request('sender')['id'] ?? '');
    }

    protected function getRecipientId(): string
    {
        $parts = explode('/', request()->getPathInfo());
        return (string) end($parts);
    }

    protected function getMessageText(): string
    {
        return (string) (request('message')['text'] ?? '');
    }
}
