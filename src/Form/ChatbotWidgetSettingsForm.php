<?php

namespace Drupal\chatbot_widget\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ChatbotWidgetSettingsForm extends ConfigFormBase {

  protected function getEditableConfigNames() {
    return ['chatbot_widget.settings'];
  }

  public function getFormId() {
    return 'chatbot_widget_settings_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('chatbot_widget.settings');

    $form['api_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Endpoint'),
      '#description' => $this->t('Enter the URL for the chatbot API endpoint.'),
      '#default_value' => $config->get('api_endpoint') ?: '/api/chatbot',
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Enter the API key for the chatbot service.'),
      '#default_value' => $config->get('api_key') ?: 'default-api-key',
      '#required' => TRUE,
    ];

    $form['user_email_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User Email Field'),
      '#description' => $this->t('Enter the machine name of the field containing the user's public email (e.g., field_public_email).'),
      '#default_value' => $config->get('user_email_field') ?: 'field_public_email',
      '#required' => TRUE,
    ];

    $form['chatbot_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chatbot Title'),
      '#description' => $this->t('Enter the title for the chatbot widget.'),
      '#default_value' => $config->get('chatbot_title') ?: 'Chatbot',
      '#required' => TRUE,
    ];

    $form['chatbot_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Chatbot Width'),
      '#description' => $this->t('Enter the width of the chatbot widget in pixels.'),
      '#default_value' => $config->get('chatbot_width') ?: 950,
      '#required' => TRUE,
      '#min' => 300,
      '#max' => 1200,
    ];

    $form['chatbot_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Chatbot Height'),
      '#description' => $this->t('Enter the height of the chatbot widget in pixels.'),
      '#default_value' => $config->get('chatbot_height') ?: 500,
      '#required' => TRUE,
      '#min' => 300,
      '#max' => 800,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatbot_widget.settings')
      ->set('api_endpoint', $form_state->getValue('api_endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('user_email_field', $form_state->getValue('user_email_field'))
      ->set('chatbot_title', $form_state->getValue('chatbot_title'))
      ->set('chatbot_width', $form_state->getValue('chatbot_width'))
      ->set('chatbot_height', $form_state->getValue('chatbot_height'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}