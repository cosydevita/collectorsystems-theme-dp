<?php
namespace Drupal\customize_object_detail_fields;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Get fields from api.
 * used in Object Details field customization
 * used in Object List field customization
 */
class ObjectFieldsService {

  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory)
  {
      $this->configFactory = $configFactory;
  }

  public static function create(ContainerInterface $container)
  {
      return new static(
          $container->get('config.factory')
      );
  }

  public function getObjectFields() {
    $config = $this->configFactory->get('custom_api_integration.settings');
    $subscriptionKey = $config->get('subscription_key');
    $accountGuid = $config->get('account_guid');

    $client = new Client(); // Assuming you've imported the Guzzle HTTP Client namespace.

    $apiUrl = "https://apis.collectorsystems.com/public/v2/{$accountGuid}/ObjectFields";
    $response = $client->get($apiUrl, [
      'headers' => [
        'Ocp-Apim-Subscription-Key' => $subscriptionKey,
      ],
    ]);
    $data = json_decode($response->getBody(), true);

    $ObjectFields =  $data['value'];

    return $ObjectFields;

  }
}

