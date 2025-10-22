<?php

require __DIR__ . '/vendor/autoload.php';

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\Drivers\Web\WebDriver;
use BotMan\BotMan\Drivers\DriverManager;

$config = [
    // Your driver-specific configuration
    // "messenger" => [
    //    "token" => "TOKEN"
    // ]
];

// Load the driver(s) you want to use
DriverManager::loadDriver(WebDriver::class);

// Create an instance
$botman = BotManFactory::create($config);

// 1️⃣ Greeting
$botman->hears("hello", function (BotMan $bot) {
    $bot->reply("Hey there 👋 How's your day going?");
});

// 2️⃣ Asking the bot’s name
$botman->hears("what is your name", function (BotMan $bot) {
    $bot->reply("I'm your friendly chat assistant 🤖");
});

// 3️⃣ Asking about time
$botman->hears("what time is it", function (BotMan $bot) {
    $bot->reply("⏰ The current time is " . date("h:i A"));
});

// 4️⃣ Asking about help
$botman->hears("help", function (BotMan $bot) {
    $bot->reply("🧭 Sure! You can say things like “hello”, “what time is it”, or “tell me a joke.”");
});

// 5️⃣ Telling a joke
$botman->hears("tell me a joke", function (BotMan $bot) {
    $bot->reply("😂 Why don't programmers like nature? Too many bugs!");
});

$botman->fallback(function (BotMan $bot) {
    $bot->reply("😅 Sorry, I didn't quite get that. Could you try rephrasing?");
});

// Start listening
$botman->listen();
