const chatForm = document.getElementById("chat-form");
const chat = document.getElementById("chat");
const input = document.getElementById("message");

chatForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const text = input.value.trim();
  if (!text) return;

  // Add user message
  chat.innerHTML += `
    <div class="flex justify-end">
      <div class="bg-blue-600 text-white p-3 rounded-2xl rounded-br-none max-w-xs">
        ${text}
      </div>
    </div>
  `;
  input.value = "";
  chat.scrollTop = chat.scrollHeight;

  const response = await fetch("server.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams({
      driver: "web",
      userId: "user1",
      message: text,
    }),
  });

  if (!response.ok) return;

  const payload = await response.json();

  if (payload?.messages?.length) {
    const reply = payload.messages[0].text;
    chat.innerHTML += `
        <div class="flex justify-start">
          <div class="bg-gray-800 p-3 rounded-2xl rounded-bl-none max-w-xs">
            ${reply}
          </div>
        </div>
    `;
    chat.scrollTop = chat.scrollHeight;
  }
});
