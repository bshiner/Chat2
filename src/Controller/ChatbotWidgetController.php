<?php

namespace Drupal\chatbot_widget\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ChatbotWidgetController extends ControllerBase implements ContainerInjectionInterface {

  protected $loggerFactory;

  public function __construct(LoggerChannelFactoryInterface $logger_factory) {
    $this->loggerFactory = $logger_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')
    );
  }

  public function handleRequest(Request $request) {
    $config = $this->config('chatbot_widget.settings');
    $apiEndpoint = $config->get('api_endpoint');
    $apiKey = $config->get('api_key');
    $current_user = \Drupal::currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    $email_field = $config->get('user_email_field') ?: 'field_public_email';
    $user_email = $user->hasField($email_field) ? $user->get($email_field)->value : '';

    $content = json_decode($request->getContent(), true);
    $message = $content['message'] ?? '';

    if ($config->get('enable_logging')) {
      $this->loggerFactory->get('chatbot_widget')->info('Request to API: @request', ['@request' => json_encode($content)]);
    }

    $client = \Drupal::httpClient();

    try {
      $response = $client->post($apiEndpoint, [
        'json' => [
          'prompt' => $message,
          'sessionId' => '12345',
          // Add any other fields you want to send to the API
        ],
        'headers' => [
          'X-API-Key' => $apiKey,
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'X-User-Email' => $user_email,
        ],
      ]);

      $body = json_decode($response->getBody(), true);      

      if ($config->get('enable_logging')) {
        $this->loggerFactory->get('chatbot_widget')->info('Response from API: @response', ['@response' => json_encode($body)]);
      }

      // Format the message
      $formattedMessage = $this->formatMessage($body['response']);
    
    // Extract citations from the API response
    $citations = $body['citations'] ?? [];

    return new JsonResponse([
      'message' => $formattedMessage,
      'citations' => $citations,
    ]);
    }
    catch (RequestException $e) {
      \Drupal::logger('chatbot_widget')->error('API request failed: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse(['error' => 'An error occurred while processing your request.'], 500);
    }
  }

  public function handleFeedback(Request $request) {
    $config = $this->config('chatbot_widget.settings');
    $apiEndpoint = $config->get('feedback_uri');
    $apiKey = $config->get('api_key');

    $content = json_decode($request->getContent(), true);

    if ($config->get('enable_logging')) {
      $this->loggerFactory->get('chatbot_widget')->info('Feedback request: @request', ['@request' => json_encode($content)]);
    }

    $email_field = $config->get('user_email_field') ?: 'field_public_email';
    $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    $user_email = $user->hasField($email_field) ? $user->get($email_field)->value : '';

    $client = \Drupal::httpClient();

    try {
      $response = $client->post($apiEndpoint . '/feedback', [
        'json' => [
          'sessionId' => '12345',
          'rating' => $content['rating'] ?? 'up',
        ],
        'headers' => [
          'X-API-Key' => $apiKey,
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
          'X-User-Email' => $user_email,
        ],
      ]);

      $body = json_decode($response->getBody(), true);

      if ($config->get('enable_logging')) {
        $this->loggerFactory->get('chatbot_widget')->info('Feedback response: @response', ['@response' => json_encode($body)]);
      }

      return new JsonResponse(['message' => 'Feedback submitted successfully']);
    }
    catch (RequestException $e) {
      \Drupal::logger('chatbot_widget')->error('Feedback API request failed: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse(['error' => 'An error occurred while submitting feedback.'], 500);
    }
  }

  private function formatMessage($message) {
    // Split the message into lines
    $lines = explode("\n", $message);
    $formattedLines = [];
    $inList = false;
    $inCodeBlock = false;

    foreach ($lines as $line) {
      $trimmedLine = trim($line);

      //handle empty lines aka line breaks
      if (strlen($trimmedLine) == 0) {
        $formattedLines[] = '<br/>';
      }

      // Handle code blocks
      if (strpos($trimmedLine, '```') === 0) {
        if ($inCodeBlock) {
          $formattedLines[] = '</code></pre>';
          $inCodeBlock = false;
        } else {
          $formattedLines[] = '<pre><code>';
          $inCodeBlock = true;
        }
        continue;
      }

      if ($inCodeBlock) {
        $formattedLines[] = htmlspecialchars($line);
        continue;
      }

      // Handle lists
      if (strpos($trimmedLine, '- ') === 0) {
        if (!$inList) {
          $formattedLines[] = '<ul>';
          $inList = true;
        }
        $listItem = substr($trimmedLine, 2);
        $formattedLines[] = '<li>' . $this->formatInline($listItem) . '</li>';
      } else {
        if ($inList) {
          $formattedLines[] = '</ul>';
          $inList = false;
        }
        $formattedLines[] = $this->formatInline($trimmedLine);
      }
    }

    if ($inList) {
      $formattedLines[] = '</ul>';
    }

    if ($inCodeBlock) {
      $formattedLines[] = '</code></pre>';
    }

    return implode("\n", $formattedLines);
  }

  private function formatInline($text) {
    // Convert inline code
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

    // Convert bold
    $text = preg_replace('/\*\*([^\*]+)\*\*/', '<strong>$1</strong>', $text);

    // Convert italic
    $text = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $text);

    // Convert links
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);

    return $text;
  }
}