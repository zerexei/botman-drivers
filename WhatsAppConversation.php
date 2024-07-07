<?php

namespace App\Modules\BotMan;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;

class WhatsAppConversation extends Conversation
{
    protected $firstname;
    protected $email;

    public function stopsConversation(IncomingMessage $message): bool
    {
        return in_array($message->getText(), ['get started']);
    }

    public function run()
    {
        try {
            $this->askFirstname();
        } catch (\Exception $e) {
            // log error
            $this->say("Something went wrong. Please try again or type \"get started\" to return to menu.");
        }
    }

    public function askFirstname()
    {
        $this->ask('Hello! What is your firstname?', function (Answer $answer) {
            // Save result
            $this->firstname = $answer->getText();

            $this->say('Nice to meet you ' . $this->firstname);
            $this->askEmail();
        });
    }

    public function askEmail()
    {
        $this->ask('One more thing - what is your email?', function (Answer $answer) {
            // Save result
            $this->email = $answer->getText();

            $this->say('Great - that is all we need, ' . $this->firstname);
        });
    }
}
