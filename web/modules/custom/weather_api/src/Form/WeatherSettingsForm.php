<?php

namespace Drupal\weather_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Weather API settings.
 */
class WeatherSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['weather_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'weather_api_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('weather_api.settings');

    $form['api_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API Configuration'),
      '#collapsible' => FALSE,
    ];

    $form['api_settings']['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenWeatherMap API Key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Your OpenWeatherMap API key. You can get one at <a href="@url" target="_blank">OpenWeatherMap</a>.', [
        '@url' => 'https://openweathermap.org/api',
      ]),
      '#required' => TRUE,
    ];

    $form['display_settings']['units'] = [
      '#type' => 'select',
      '#title' => $this->t('Temperature Units'),
      '#default_value' => $config->get('units') ?? 'metric',
      '#options' => [
        'metric' => $this->t('Metric (°C)'),
        'imperial' => $this->t('Imperial (°F)'),
      ],
      '#description' => $this->t('Default temperature unit to display.'),
    ];

    $form['display_settings']['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Weather Language'),
      '#default_value' => $config->get('language') ?? 'en',
      '#options' => [
        'en' => $this->t('English'),
        'es' => $this->t('Spanish'),
      ],
      '#description' => $this->t('Language for weather descriptions.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $api_key = $form_state->getValue('api_key');
    
    // Validate API key format (OpenWeatherMap API keys are 32 characters)
    if (!empty($api_key) && strlen($api_key) !== 32) {
      $form_state->setErrorByName('api_key', $this->t('OpenWeatherMap API key should be 32 characters long.'));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('weather_api.settings');
    
    // Save all form values to configuration
    $config
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('units', $form_state->getValue('units'))
      ->set('language', $form_state->getValue('language'))
      ->save();

    parent::submitForm($form, $form_state);
    
    $this->messenger()->addMessage($this->t('Weather API configuration has been saved.'));
  }

}