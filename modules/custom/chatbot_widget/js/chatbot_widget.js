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

        // Toggle chat container visibility
        $button.on('click', function () {
          $container.toggle();
        });

        // Close chat container
        $closeButton.on('click', function () {
          $container.hide();
        });

        // Send message
        function sendMessage() {
          const message = $input.val().trim();
          if (message) {
            addMessage('user', message);
            $input.val('');
            // TODO: Send message to API endpoint
            // For now, we'll just simulate a response
            setTimeout(() => {
              addMessage('bot', 'Thank you for your message. This is a placeholder response.');
            }, 1000);
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
          const $message = $('<div>')
            .addClass(`message ${sender}-message`)
            .text(text);
          $messages.append($message);
          $messages.scrollTop($messages[0].scrollHeight);
        }
      });
    }
  };
})(jQuery, Drupal, once);