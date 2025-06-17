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
            
            // Show loading indicator
            showLoadingIndicator();

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
                // Hide loading indicator
                hideLoadingIndicator();
                addMessage('bot', response.message, true, response.citations);
              },
              error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                // Hide loading indicator
                hideLoadingIndicator();
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
        function addMessage(sender, text, isHtml = false, citations = []) {
          const timestamp = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          const $message = $('<div>').addClass(`message ${sender}-message`);
          const $messageContent = $('<div>').addClass('message-content');
          
          if (isHtml) {
            $messageContent.html(text);
          } else {
            $messageContent.text(text);
          }
          
          $message.append($messageContent);

          if (citations.length > 0) {
            const $citationsContainer = $('<div>').addClass('citations-container');
            const $citationsList = $('<ul>').addClass('citations-list');

            $citationsContainer.append($('<h4>').text('Citations:'));
            citations.forEach(function(citation) {
              $citationsList.append($('<li>').html('<a href="' + citation + '" target="_blank">' + citation + '</a>'));
            });

            $citationsContainer.append($citationsList);
            $messageContent.append($citationsContainer);
          }

          const $messageFooter = $('<div>').addClass('message-footer');
          const $messageTimestamp = $('<span>').addClass('message-timestamp').text(timestamp);
          
          $messageFooter.append($messageTimestamp);

          if (sender === 'bot') {
            const $feedbackContainer = $('<span>').addClass('feedback-container');
            const $thumbsUp = $('<span>').addClass('feedback-icon thumbs-up').html('👍');
            const $thumbsDown = $('<span>').addClass('feedback-icon thumbs-down').html('👎');

            $feedbackContainer.append($thumbsUp).append($thumbsDown);
            $messageFooter.append($feedbackContainer);

            $thumbsUp.on('click', function() { submitFeedback('up', $message); });
            $thumbsDown.on('click', function() { submitFeedback('down', $message); });
          }

          $message.append($messageFooter);
          $messages.append($message);
          $messages.scrollTop($messages[0].scrollHeight);
        }

        function submitFeedback(rating, $messageElement) {
          $.ajax({
            url: '/chatbot-widget/feedback',
            method: 'POST',
            data: JSON.stringify({
              sessionId: sessionId,
              rating: rating
            }),
            contentType: 'application/json',
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
          //return 'session_' + Math.random().toString(36).substr(2, 9);
          return settings.chatbotWidget.chatSessionId || 'sesion0000';
        }

        function showLoadingIndicator() {
          const $loadingIndicator = $('<div class="loading-indicator"><div class="spinner"></div></div>');
          $messages.append($loadingIndicator);
          $messages.scrollTop($messages[0].scrollHeight);
        }

        function hideLoadingIndicator() {
          $('.loading-indicator', $messages).remove();
        }

        // Add initial disclaimer message
        function addDisclaimerMessage() {
          const disclaimerText = settings.chatbotWidget.disclaimerText || 'Welcome! This is an AI-powered chatbot. While it strives to provide helpful information, please note that its responses may not always be accurate or complete. For critical matters, consult with appropriate professionals.';
          addMessage('bot', disclaimerText, false);
        }

        // Call the function to add the disclaimer message
        addDisclaimerMessage();
      });
    }
  };
})(jQuery, Drupal, once);