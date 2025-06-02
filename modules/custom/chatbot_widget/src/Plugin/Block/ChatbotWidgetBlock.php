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
    return [
      '#theme' => 'chatbot_widget',
      '#attached' => [
        'library' => [
          'chatbot_widget/chatbot_widget',
        ],
      ],
    ];
  }

}