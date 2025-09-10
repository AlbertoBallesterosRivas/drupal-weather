<?php

namespace Drupal\weather_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Service for weather API operations.
 */
class WeatherApiService {

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $httpClient;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerFactory;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Constructs the WeatherApiService object.
   */
  public function __construct(
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger_factory,
    ConfigFactoryInterface $config_factory,
  ) {
    $this->httpClient = $http_client;
    $this->loggerFactory = $logger_factory;
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the API key from configuration.
   */
  protected function getApiKey(): ?string {
    $config = $this->configFactory->get('weather_api.settings');
    $api_key = $config->get('api_key');
    
    $this->loggerFactory->get('weather_api')->notice('API key desde config: @key', [
      '@key' => $api_key ? 'SET (' . strlen($api_key) . ' chars)' : 'NOT SET'
    ]);
    
    // Fallback to environment variable if not set in config
    if (empty($api_key)) {
      $api_key = $_ENV['OPENWEATHER_API_KEY'] ?? NULL;
      $this->loggerFactory->get('weather_api')->notice('API key desde ENV: @key', [
        '@key' => $api_key ? 'SET (' . strlen($api_key) . ' chars)' : 'NOT SET'
      ]);
    }
    
    return $api_key;
  }

  /**
   * Gets weather data by city name.
   */
  public function getWeather(string $city): ?array {
    $this->loggerFactory->get('weather_api')->notice('WeatherApiService::getWeather llamado para ciudad: @city', [
      '@city' => $city
    ]);
    
    $api_key = $this->getApiKey();
    
    if (empty($api_key)) {
      $this->loggerFactory->get('weather_api')->error('No API key configured for Weather API.');
      return NULL;
    }

    try {
      $config = $this->configFactory->get('weather_api.settings');
      
      $url = 'https://api.openweathermap.org/data/2.5/weather';
      $params = [
        'q' => $city,
        'appid' => $api_key,
        'units' => $config->get('units') ?? 'metric',
        'lang' => $config->get('language') ?? 'en',
      ];

      $this->loggerFactory->get('weather_api')->notice('Haciendo petición a API: @url con parámetros: @params', [
        '@url' => $url,
        '@params' => json_encode($params)
      ]);

      $response = $this->httpClient->get($url, ['query' => $params]);
      
      $this->loggerFactory->get('weather_api')->notice('Respuesta de API recibida. Status: @status', [
        '@status' => $response->getStatusCode()
      ]);

      $data = json_decode($response->getBody(), TRUE);

      if (!$data || !isset($data['name'])) {
        $this->loggerFactory->get('weather_api')->warning('Invalid API response for city: @city. Response: @response', [
          '@city' => $city,
          '@response' => $response->getBody()
        ]);
        return NULL;
      }

      $formatted_weather_data = $this->formatWeatherData($data);

      $this->saveWeatherData($formatted_weather_data);

      $this->loggerFactory->get('weather_api')->notice('Datos meteorológicos formateados y listos para devolver');
      
      return $formatted_weather_data;
    }
    catch (RequestException $e) {
      $this->loggerFactory->get('weather_api')->error('API request failed: @message', [
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('weather_api')->error('Error general en getWeather: @message', [
        '@message' => $e->getMessage(),
      ]);
      return NULL;
    }
  }

  /**
   * Format weather data from API response.
   */
  protected function formatWeatherData(array $data): array {
    $this->loggerFactory->get('weather_api')->notice('Formateando datos meteorológicos');
    
    return [
      'city' => $data['name'] ?? '',
      'country' => $data['sys']['country'] ?? NULL,
      'latitude' => $data['coord']['lat'] ?? NULL,
      'longitude' => $data['coord']['lon'] ?? NULL,
      'temperature' => $data['main']['temp'] ?? NULL,
      'feels_like' => $data['main']['feels_like'] ?? NULL,
      'humidity' => $data['main']['humidity'] ?? NULL,
      'pressure' => $data['main']['pressure'] ?? NULL,
      'wind_speed' => $data['wind']['speed'] ?? NULL,
      'wind_direction' => $data['wind']['deg'] ?? NULL,
      'condition' => $data['weather'][0]['description'] ?? '',
      'condition_code' => $data['weather'][0]['id'] ?? NULL,
      'icon' => $data['weather'][0]['icon'] ?? NULL,
      'visibility' => $data['visibility'] ?? NULL,
      'date' => $data['dt'] ?? time(),
      'created' => time(),
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * Save weather data to database.
   */
  protected function saveWeatherData(array $weather_data): void {
    $connection = \Drupal::database();
    
    try {
      $connection->insert('weather_data')
        ->fields($weather_data)
        ->execute();
        
      $this->loggerFactory->get('weather_api')->info('Weather data saved for city: @city', [
        '@city' => $weather_data['city'],
      ]);
      
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('weather_api')->error('Failed to save weather data: @message', [
        '@message' => $e->getMessage(),
      ]);
    }
  }
}