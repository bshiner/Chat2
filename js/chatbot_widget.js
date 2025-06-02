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

        // Get the API endpoint from Drupal settings
        const apiEndpoint = settings.chatbotWidget.apiEndpoint || '/api/chatbot';

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
            
            // Send message to API endpoint
            $.ajax({
              url: apiEndpoint,
              method: 'POST',
              data: JSON.stringify({ message: message }),
              contentType: 'application/json',
              success: function(response) {
                addMessage('bot', response.message);
              },
              error: function() {
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
      });
    }
  };
})(jQuery, Drupal, once);