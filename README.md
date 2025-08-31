# BotMan Drivers

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![BotMan](https://img.shields.io/badge/BotMan-Driver-brightgreen)](https://github.com/botman/botman)

BotMan Driver provides seamless integration between [BotMan](https://github.com/botman/botman) and multiple messaging platforms.  
It enables developers to connect their bots to different channels with minimal configuration.


## Features

- 🤖 Plug-and-play integration with BotMan  
- 🔌 Support for multiple messaging platforms  
- ⚡ Simple configuration and setup  
- 🛠️ Extensible and customizable  


## Installation

```bash
composer require your-namespace/botman-driver
```

## Usage

After installing, register the driver in your BotMan service provider or bootstrap file:

```php
DriverManager::loadDriver(\Exei\BotManDriver\YourDriver::class);
```

Now you can start listening to messages from the supported platform(s):

```php
$botman->hears('hello', function ($bot) {
    $bot->reply('Hello! How can I help you today?');
});
```

## Contributing

Contributions are welcome! 🎉
Please see the [CONTRIBUTING](CONTRIBUTING.md)
 guide for details on how to get started.

## Security Vulnerabilities

If you discover a security vulnerability within BotMan Driver, please contact:

📧 Angelo Arcillas — angeloarcillas64@gmail.com

All security issues will be promptly addressed.
