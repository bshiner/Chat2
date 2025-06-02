(function ($, Drupal) {
  Drupal.behaviors.chatbotWidget = {
    attach: function (context, settings) {
      const $widget = $('#chatbot-widget', context);
      const $button = $('.chatbot-button', $widget);
      const $container = $('.chatbot-container', $widget);
      const $closeButton = $('.close-chat', $widget);
      const $input = $('#chatbot-input-field', $widget);
      const $sendButton = $('#chatbot-send-button', $widget);
      const $messages = $('.chatbot-messages', $widget);

      // Toggle chat container visibility
      $button.once('chatbot-widget').on('click', function () {
        $container.toggle();
      });

      // Close chat container
      $closeButton.once('chatbot-widget').on('click', function () {
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

      $sendButton.once('chatbot-widget').on('click', sendMessage);
      $input.once('chatbot-widget').on('keypress', function (e) {
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
    }
  };
})(jQuery, Drupal);