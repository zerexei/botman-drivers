<?php

namespace App\Http\Controllers\Botman;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Illuminate\Http\Request;

use App\Conversations\WhatsAppConversation;
use App\Conversations\Drivers\WhatsAppDriver;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Cache;

class WhatsAppBotController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        /**
         * Verify webhook
         */
        $challenge = $request->hub_challenge;

        if ($challenge) {
            $secret = $request->hub_verify_token;
            if ($secret !== 'your-verify-token') return;
            return $challenge;
        }

        if ($request->object !== 'whatsapp_business_account') {
            Log::error("Invalid webhook request: ", $request->all());
            return;
        }

        $isMessage = $request->entry[0]['changes'][0]['value']['messages'] ?? null;
        if (!$isMessage) return;
;
        $config =  [
            'whatsapp_business_account' => [
                'token' => "your-access-token",
            ]
        ];

        DriverManager::loadDriver(WhatsAppDriver::class);
        $botman = BotManFactory::create($config, new LaravelCache());

        $botman->fallback(
            fn (BotMan $bot)  =>
            $bot->startConversation(new WhatsAppConversation($story, $user, $integration))
        );

        $botman->listen();
    }
  
    protected function getConversationId(): string
    {
        return (string) request('entry')[0]['id'] ?? "";
    }

    protected function getSenderId(): string
    {
        return (string) request('entry')[0]["changes"][0]['value']['messages'][0]['from'] ?? "";
    }

    protected function getRecipientId(): string
    {
        return (string) request('entry')[0]["changes"][0]['value']['metadata']['phone_number_id'] ?? "";
    }

    protected function getMessageText(): string
    {
        if (!isset(request('entry')[0]["changes"][0]['value']['messages'][0]['text']['body'])) {
            return "";
        }

        return (string) request('entry')[0]["changes"][0]['value']['messages'][0]['text']['body'] ?? "";
    }
}
