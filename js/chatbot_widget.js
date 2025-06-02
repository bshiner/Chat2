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
        const $header = $('.chatbot-header h3', $widget);

        // Apply configurations
        $container.css({
          width: settings.chatbotWidget.chatbotWidth + 'px',
          height: settings.chatbotWidget.chatbotHeight + 'px'
        });
        $header.text(settings.chatbotWidget.chatbotTitle);

        // Get the API endpoint and user email from Drupal settings
        const apiEndpoint = settings.chatbotWidget.apiEndpoint || '/api/chatbot';
        const userEmail = settings.chatbotWidget.userEmail || '';
        const feedbackUri = settings.chatbotWidget.feedbackUri || '/api/chatbot/feedback';
        let sessionId = generateSessionId();

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
              message: message
            };

            // Send message to our module's endpoint
            $.ajax({
              url: '/chatbot-widget/api',
              method: 'POST',
              data: JSON.stringify(payload),
              contentType: 'application/json',
              success: function(response) {
                addMessage('bot', response.message, true);
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
        function addMessage(sender, text, isHtml = false) {
          const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          const $message = $('<div>').addClass(`message ${sender}-message`);
          const $messageContent = $('<div>').addClass('message-content');
          
          if (isHtml) {
            $messageContent.html(text);
          } else {
            $messageContent.text(text);
          }
          
          const $messageTimestamp = $('<div>').addClass('message-timestamp').text(timestamp);
          
          $message.append($messageContent).append($messageTimestamp);

          if (sender === 'bot') {
            const $feedbackContainer = $('<div>').addClass('feedback-container');
            const $thumbsUp = $('<span>').addClass('feedback-icon thumbs-up').html('👍');
            const $thumbsDown = $('<span>').addClass('feedback-icon thumbs-down').html('👎');

            $feedbackContainer.append($thumbsUp).append($thumbsDown);
            $message.append($feedbackContainer);

            $thumbsUp.on('click', function() { submitFeedback(1, $message); });
            $thumbsDown.on('click', function() { submitFeedback(-1, $message); });
          }

          $messages.append($message);
          $messages.scrollTop($messages[0].scrollHeight);
        }

        function submitFeedback(rating, $messageElement) {
          $.ajax({
            url: feedbackUri,
            method: 'POST',
            data: JSON.stringify({
              sessionId: sessionId,
              rating: rating
            }),
            contentType: 'application/json',
            headers: {
              'X-User-Email': userEmail
            },
            success: function(response) {
              console.log('Feedback submitted successfully');
              $messageElement.find('.feedback-container').html('Thank you for your feedback!');
            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.error('Error submitting feedback:', textStatus, errorThrown);
            }
          });
        }

        // Helper function to generate a session ID
        function generateSessionId() {
          return 'session_' + Math.random().toString(36).substr(2, 9);
        }
      });
    }
  };
})(jQuery, Drupal, once);