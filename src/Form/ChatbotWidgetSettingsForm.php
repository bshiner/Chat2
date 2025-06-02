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

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatbot_widget.settings')
      ->set('api_endpoint', $form_state->getValue('api_endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('user_email_field', $form_state->getValue('user_email_field'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}