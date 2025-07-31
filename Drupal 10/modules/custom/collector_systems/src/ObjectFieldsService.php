<?php
namespace Drupal\collector_systems;
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
    $config = $this->configFactory->get('collector_systems.settings');
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

  /**
   * Get the value of ObjectField label from the database
   *
   * @param string $field_name
   *
   * @return string
   *  Label string
   */
  public function getObjectFieldLabelFromDatabase($field_name){

    $db = \Drupal::database();
    $table = "collector_systems_clsobjects_fields";

    $query = $db->select($table, 'c')
    ->fields('c', ['fieldvalue'])
    ->condition('fieldname', $field_name)
    ->condition('fieldtype', 'ObjectDetail');

    $field_value = $query->execute()->fetchField();
    if ($field_value === FALSE) {
      // No value found
      $field_value = $field_name;
    }

    return $field_value;

  }
}

