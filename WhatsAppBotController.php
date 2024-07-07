<?php

namespace App\Modules\BotMan;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;

use Illuminate\Http\Request;

use App\Modules\BotMan\WhatsAppDriver;
use App\Modules\BotMan\WhatsAppConversation;

use App\Http\Controllers\Controller;

class WhatsAppController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {

            /**
             * Verify webhook
             */
            $challenge = $request->hub_challenge;

            if ($challenge) {
                $secret = $request->hub_verify_token;
                if ($secret !== 'your-secret-token') return;
                return $challenge;
            }

            if ($request->object !== 'whatsapp_business_account') {
                // log request error
                return;
            }

            // validate if event is message
            if (!$this->getMessageText()) return;

            $config =  [
                'whatsapp_business_account' => [
                    'token' => 'your-meta-app-access-token',
                ]
            ];

            // 
            DriverManager::loadDriver(WhatsAppDriver::class);
            $botman = BotManFactory::create($config, new LaravelCache());

            // 
            $botman->fallback(
                fn (BotMan $bot)  =>
                $bot->startConversation(new WhatsAppConversation())
            );

            $botman->listen();
        } catch (\Exception $e) {
            // log error
        } finally {
            return response()->json([], 200);
        }
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
        return (string) request('entry')[0]["changes"][0]['value']['messages'][0]['text']['body'] ?? "";
    }
}
