(function ($, Drupal, once) {
  Drupal.behaviors.chatbotWidget = {
    attach: function (context, settings) {
      once('chatbot-widget', '#chatbot-widget', context).forEach(function (widget) {
        const $widget = $(widget);
        const $button = $('.chatbot-button', $widget);
        const $container = $('.chatbot-container', $widget);
        const $closeButton = $('.close-chat', $widget);
        const $input = $('#chatbot-input-field', $widget);
        const $sendButton = $('#chatbot-send-button', $widget);
        const $messages = $('.chatbot-messages', $widget);

        // Get the API endpoint and user email from Drupal settings
        const apiEndpoint = settings.chatbotWidget.apiEndpoint || '/api/chatbot';
        const userEmail = settings.chatbotWidget.userEmail || '';

        // Toggle chat container visibility
        $button.on('click', function () {
          $container.toggle();
        });

        // Close chat container
        $closeButton.on('click', function () {
          $container.hide();
        });

        function sendMessage() {
          const message = $input.val().trim();
          if (message) {
            addMessage('user', message);
            $input.val('');
            
            // Prepare the payload
            const payload = {
              prompt: message,
              timestamp: new Date().toISOString(),
              user_id: 'anonymous', // You might want to replace this with an actual user ID if available
              sessionId: generateSessionId(),
              language: navigator.language || navigator.userLanguage,
              'X-User-Email': userEmail
            };

            // Send message to API endpoint
            $.ajax({
              url: apiEndpoint,
              method: 'POST',
              data: JSON.stringify(payload),
              contentType: 'application/json',
              headers: {
                'X-API-Key': settings.chatbotWidget.apiKey || 'default-api-key',
                'Accept': 'application/json'
              },
              success: function(response) {
                addMessage('bot', response.message);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                addMessage('bot', 'Sorry, there was an error processing your message.');
              }
            });
          }
        }

        $sendButton.on('click', sendMessage);
        $input.on('keypress', function (e) {
          if (e.which === 13) {
            sendMessage();
            e.preventDefault();
          }
        });

        // Add message to chat
        function addMessage(sender, text) {
          const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          const $message = $('<div>').addClass(`message ${sender}-message`);
          const $messageContent = $('<div>').addClass('message-content').text(text);
          const $messageTimestamp = $('<div>').addClass('message-timestamp').text(timestamp);
          
          $message.append($messageContent).append($messageTimestamp);
          $messages.append($message);
          $messages.scrollTop($messages[0].scrollHeight);
        }

        // Helper function to generate a session ID
        function generateSessionId() {
          // This is a simple implementation. You might want to use a more robust method
          // or retrieve an existing session ID from cookies or local storage
          return 'session_' + Math.random().toString(36).substr(2, 9);
        }
      });
    }
  };
})(jQuery, Drupal, once);