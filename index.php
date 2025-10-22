<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Botman</title>

    <!-- 
    HOW TO RUN:
    run: php -S localhost:8000
    url: http://localhost:8000
    -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body class="bg-gray-900 text-gray-100 h-screen p-12">
    <!-- Chat Container -->
    <div class="flex flex-col max-w-3xl mx-auto w-full h-full border border-gray-800 rounded-2xl shadow-lg">

        <!-- Header -->
        <div class="bg-gray-800 p-4 border-b border-gray-700 text-center font-semibold text-lg">
            Chat
        </div>

        <!-- Messages -->
        <div id="chat" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-900">
            <!-- Example outgoing message -->
            <div class="flex justify-end">
                <div class="bg-blue-600 text-white p-3 rounded-2xl rounded-br-none max-w-xs">
                    Hey, how are you?
                </div>
            </div>

            <!-- Example incoming message -->
            <div class="flex justify-start">
                <div class="bg-gray-800 p-3 rounded-2xl rounded-bl-none max-w-xs">
                    Doing good! How about you?
                </div>
            </div>
        </div>

        <!-- Input area -->
        <form id="chat-form" class="flex items-center gap-2 p-4 bg-gray-800 border-t border-gray-700">
            <input
                id="message"
                type="text"
                placeholder="Type a message..."
                class="flex-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                autocomplete="off" />
            <button
                type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded-xl hover:bg-blue-700 transition">
                Send
            </button>
        </form>
    </div>

    <!-- Script -->
    <script src="index.js" defer></script>
</body>

</html>