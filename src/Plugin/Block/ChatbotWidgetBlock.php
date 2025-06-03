<?php

namespace Drupal\chatbot_widget\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Chatbot Widget Block.
 *
 * @Block(
 *   id = "chatbot_widget_block",
 *   admin_label = @Translation("Chatbot Widget"),
 * )
 */
class ChatbotWidgetBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->get('chatbot_widget.settings');
    return [
      '#theme' => 'chatbot_widget',
      '#attached' => [
        'library' => [
          'chatbot_widget/chatbot_widget',
        ],
        'drupalSettings' => [
          'chatbotWidget' => [
            // ... other settings ...
            'disclaimerText' => $config->get('disclaimer_text') ?: 'Welcome! This is an AI-powered chatbot. While it strives to provide helpful information, please note that its responses may not always be accurate or complete. For critical matters, consult with appropriate professionals.',
          ],
        ],
      ],
    ];
  }

}