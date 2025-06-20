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
      '#default_value' => $config->get('api_endpoint'),
      '#required' => TRUE,
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Enter the API key for the chatbot service.'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['chatbot_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chatbot Title'),
      '#description' => $this->t('Enter the title for the chatbot widget.'),
      '#default_value' => $config->get('chatbot_title'),
      '#required' => TRUE,
    ];

    $form['chatbot_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Chatbot Width'),
      '#description' => $this->t('Enter the width of the chatbot widget in pixels.'),
      '#default_value' => $config->get('chatbot_width'),
      '#required' => TRUE,
      '#min' => 300,
      '#max' => 1200,
    ];

    $form['chatbot_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Chatbot Height'),
      '#description' => $this->t('Enter the height of the chatbot widget in pixels.'),
      '#default_value' => $config->get('chatbot_height'),
      '#required' => TRUE,
      '#min' => 300,
      '#max' => 800,
    ];

    $form['feedback_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Feedback URI'),
      '#description' => $this->t('Enter the URI for submitting feedback.'),
      '#default_value' => $config->get('feedback_uri'),
      '#required' => TRUE,
    ];

    $form['disclaimer_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Disclaimer Text'),
      '#default_value' => $config->get('disclaimer_text'),
      '#description' => $this->t('Enter the disclaimer text to be shown at the start of each chat session.'),
    ];

    $form['enable_logging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable logging'),
      '#description' => $this->t('Log all messages sent to and received from the chatbot API.'),
      '#default_value' => $config->get('enable_logging'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('chatbot_widget.settings')
      ->set('api_endpoint', $form_state->getValue('api_endpoint'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('chatbot_title', $form_state->getValue('chatbot_title'))
      ->set('chatbot_width', $form_state->getValue('chatbot_width'))
      ->set('chatbot_height', $form_state->getValue('chatbot_height'))
      ->set('feedback_uri', $form_state->getValue('feedback_uri'))
      ->set('disclaimer_text', $form_state->getValue('disclaimer_text'))
      ->set('enable_logging', $form_state->getValue('enable_logging'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}