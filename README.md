# BotMan Drivers

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![BotMan](https://img.shields.io/badge/BotMan-Driver-brightgreen)](https://github.com/botman/botman)

**BotMan Driver** provides seamless integration between [BotMan](https://github.com/botman/botman) and multiple messaging platforms.  
It enables developers to connect their bots to different channels with minimal configuration.


## 🚀 Features

- 🤖 **Plug-and-play** integration with BotMan  
- 🔌 **Supports multiple** messaging platforms  
- ⚡ **Quick setup** and easy configuration  
- 🛠️ **Extensible and customizable** driver system  

## 📦 Installation

```bash
composer require exei/botman-drivers
```

## 🧠 Usage

After installing, register the driver in your BotMan service provider or bootstrap file:
```bash
DriverManager::loadDriver(\Exei\BotManDrivers\YourDriver::class);
```

Then start listening to messages from the supported platforms:
```bash
$botman->hears('hello', function ($bot) {
    $bot->reply('Hello! How can I help you today?');
});
```

## 💬 Currently Supported Drivers

- [x] Messenger
- [x] WhatsApp
- [x] Viber

## 🚀 Driver Roadmap

- [ ] Discord
- [ ] Telegram
- [ ] Slack
- [ ] Microsoft Teams
- [ ] Google Chat

## 🤝 Contributing

Contributions are welcome! 🎉


Please see the [CONTRIBUTING](CONTRIBUTING)
guide for details on how to get started.

## 🔒 Security Vulnerabilities

If you discover a security vulnerability within BotMan Driver,

please contact: 📧 Angelo Arcillas — angeloarcillas64@gmail.com

All security issues will be promptly addressed.
