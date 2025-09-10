<?php

namespace Drupal\weather_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class WeatherSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'weather_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#attributes'] = [
      'class' => ['weather-search-form'],
      'data-behavior' => 'weather-search',
    ];

    $form['header'] = [
    '#type' => 'html_tag',
    '#tag' => 'h2',
    '#value' => $this->t('Find a forecast'),
    '#attributes' => [
      'class' => ['weather-form-header'],
    ],
    '#weight' => -10,
  ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ciudad'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Search for a place'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['search-input'],
        'data-behavior' => 'search-input',
      ],
      '#wrapper_attributes' => ['class' => ['search-container']],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#attributes' => ['class' => ['search-btn']],
    ];

    $cities = ['Madrid', 'Sidney', 'Tokyo', 'Ottawa'];
    $form['quick_cities'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['quick-cities']],
      '#markup' => '<div class="quick-cities-title">' . $this->t('Quick search:') . '</div>',
    ];

    foreach ($cities as $city) {
      $form['quick_cities'][$city] = [
        '#type' => 'link',
        '#title' => $city,
        '#url' => Url::fromRoute('weather_api.result', ['city' => $city]),
        '#attributes' => [
          'class' => ['quick-btn'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    
    $city = trim($form_state->getValue('city'));
    
    if (empty($city)) {
      $form_state->setErrorByName('city', $this->t('Please enter a city name.'));
      return;
    }
    
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\.\']+$/u', $city)) {
      $form_state->setErrorByName('city', $this->t('Please enter a valid city name.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $city = trim($form_state->getValue('city'));
    
    if (!empty($city)) {
      $clean_city = preg_replace('/\s+/', ' ', $city);
      \Drupal::logger('weather_api')->notice('Redirigiendo a: @route con parámetro: @city', [
        '@route' => 'weather_api.result',
        '@city' => $clean_city
      ]);
      
      $form_state->setRedirect('weather_api.result', ['city' => $clean_city]);
    } else {
      \Drupal::messenger()->addError($this->t('Please enter a city name.'));
    }
  }
}