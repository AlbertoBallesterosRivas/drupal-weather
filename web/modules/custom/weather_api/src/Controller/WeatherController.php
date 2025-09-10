<?php

namespace Drupal\weather_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\weather_api\Service\WeatherApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeatherController extends ControllerBase {

  public function __construct(
    protected WeatherApiService $weatherApiService,
  ) {}

  public static function create(ContainerInterface $container): static {

    return new static(
      $container->get('weather_api.weather_service'),
    );
  }

  public function weatherPage(string $city): array {
    
    $city = urldecode($city);
    $city = trim($city);
    
    
    if (empty($city)) {
      return [
        '#markup' => '<div class="weather-error">' . $this->t('No city specified.') . '</div>',
      ];
    }
    
    $data = $this->weatherApiService->getWeather($city);
    
    if ($data === NULL) {
      \Drupal::logger('weather_api')->error('No se pudieron obtener datos meteorológicos para: @city', ['@city' => $city]);
      return [
        '#markup' => '<div class="weather-error">' . 
          $this->t('Could not get weather information for "@city"', ['@city' => $city]) . 
          '</div>',
      ];
    }

    return [
      '#theme' => 'weather_display',
      '#weather_data' => $data,
      '#config' => [
        'units' => 'metric',
        'language' => 'es',
      ],
      '#cache' => [
        'max-age' => 1800,
        'tags' => ['weather_api:' . strtolower($city)],
      ],
      '#attached' => [
        'library' => [
          'weather_api/weather-display',
          'weather_api/weather-search-form',
          'weather_api/weather-metrics',
        ],
      ],
    ];
  }

  public function ajaxSearch(): JsonResponse {
    
    $city = \Drupal::request()->query->get('city');
    
    if (!$city) {
      return new JsonResponse(['error' => 'Ciudad requerida'], 400);
    }

    $data = $this->weatherApiService->getWeather($city);
    
    return new JsonResponse([
      'success' => $data !== NULL,
      'data' => $data,
      'redirect_url' => $data ? "/weather/" . urlencode($city) : NULL,
    ]);
  }
}