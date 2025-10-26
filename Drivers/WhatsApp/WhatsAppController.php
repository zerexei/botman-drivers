<?php

namespace Drivers\WhatsApp;

use Illuminate\Http\Request;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Drivers\BotConversation;
use Drivers\WhatsApp\WhatsAppDriver;

class WhatsAppController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            // Verify webhook
            $challenge = $request->hub_challenge;

            if ($challenge) {
                $secret = $request->hub_verify_token;
                if ($secret !== 'your-secret-token') return;
                return $challenge;
            }

            if (!$this->isRequestValid()) {
                return response()->json();
            }

            $config =  [
                'whatsApp' => [
                    'token' => 'your-meta-app-access-token',
                ]
            ];

            // 
            DriverManager::loadDriver(WhatsAppDriver::class);
            $botman = BotManFactory::create($config, new LaravelCache());

            //
            $botman->fallback(fn(BotMan $bot)  => $bot->startConversation(new BotConversation));

            //
            $botman->listen();
        } catch (\Throwable $th) {
            return response()->json();
        }
    }

    protected function isRequestValid()
    {
        return  in_array(false, [
            $this->getConversationId(),
            $this->getSenderId(),
            $this->getRecipientId(),
            $this->getMessageText(),
        ]);
    }

    protected function getConversationId(): string
    {
        return (string) request("entry.0.id");
    }

    protected function getSenderId(): string
    {
        return (string) request('entry.0.changes.0.value.messages.0.from');
    }

    protected function getRecipientId(): string
    {
        return (string) request('entry.0.changes.0.value.metadata.phone_number_id');
    }

    protected function getMessageText(): string
    {
        return (string) request('entry.0.changes.0.value.messages.0.text.body');
    }
}
