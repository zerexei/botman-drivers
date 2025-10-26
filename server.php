<?php

require __DIR__ . '/vendor/autoload.php';

use Drivers\Viber\MessengerController;
use Illuminate\Http\Request;
use Drivers\Web\WebController;
use Drivers\Viber\ViberController;
use Drivers\WhatsApp\WhatsAppController;

try {
    // Create a Request instance
    $request = Request::capture();

    $driver = $request->input('driver');

    $controller = match ($driver) {
        'web' => WebController::class,
        'messenger' => MessengerController::class,
        'whatsApp' => WhatsAppController::class,
        'viber' => ViberController::class,
    };

    $instance = new $controller();

    $response = $instance($request);
} catch (\Throwable $th) {
    $message = sprintf(
        "[%s] %s in %s:%d\nStack trace:\n%s\n\n",
        date('Y-m-d H:i:s'),
        $th->getMessage(),
        $th->getFile(),
        $th->getLine(),
        $th->getTraceAsString()
    );

    file_put_contents(__DIR__ . '/logs/error.log', $message, FILE_APPEND);
}
