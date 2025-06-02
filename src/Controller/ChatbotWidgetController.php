<?php

namespace Drupal\chatbot_widget\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ChatbotWidgetController extends ControllerBase {

  public function handleRequest(Request $request) {
    $config = $this->config('chatbot_widget.settings');
    $current_user = \Drupal::currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    $apiEndpoint = $config->get('api_endpoint');
    $apiKey = $config->get('api_key');
    $email_field = $config->get('user_email_field') ?: 'field_public_email';
    $user_email = $user->hasField($email_field) ? $user->get($email_field)->value : '';

    $content = json_decode($request->getContent(), true);
    $message = $content['message'] ?? '';
//    $userEmail = $content['X-User-Email'] ?? '';

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
      return new JsonResponse($body);
    }
    catch (RequestException $e) {
      \Drupal::logger('chatbot_widget')->error('API request failed: @error', ['@error' => $e->getMessage()]);
      return new JsonResponse(['error' => 'An error occurred while processing your request.'], 500);
    }
  }
}