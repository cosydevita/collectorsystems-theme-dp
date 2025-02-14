<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class DashboardController.
 */
class DashboardController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(AccountInterface $current_user, ConfigFactoryInterface $config_factory) {
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * Build the response for the custom page.
   */
  public function dashboardPage() {
    $database = Database::getConnection();
    $table_CSSynced = 'CSSynced';
    $table_exists = $database->schema()->tableExists($table_CSSynced);
    $last_synced_date_time = '';
    $last_synced_by = '';
    if($table_exists){
       // Check if the record exists and fetch the LastSyncedDateTime and LastSyncedBy.
      $record_exists = $database->select($table_CSSynced)
      ->fields($table_CSSynced, ['LastSyncedDateTime', 'LastSyncedBy'])
      ->execute()
      ->fetchAssoc();

      // Check if a record was found.
      if ($record_exists) {
      // Retrieve the values.
      $last_synced_date_time = $record_exists['LastSyncedDateTime'];
      $last_synced_by = $record_exists['LastSyncedBy'];

      }

    }

    $automatic_sync_settings_form = \Drupal::formBuilder()->getForm('Drupal\collector_systems\Form\AutomaticSyncSettingsForm');

    $scheduled_date_and_time_information = $this->getScheduledDateAndTimeInformation();

    $build = [
      '#theme' => 'dashboard',
      '#automatic_sync_settings_form' => $automatic_sync_settings_form,
      '#API_Synced_On' => $last_synced_date_time,
      '#API_Synced_By' => $last_synced_by,
      '#scheduled_date_and_time_information' => $scheduled_date_and_time_information
    ];
    $build['#attached']['library'][] = 'collector_systems/dashboard';

    return $build;
  }


  public function getScheduledDateAndTimeInformation() {
    $config = $this->configFactory->get('collector_systems.settings');
    $saved_value_collector_systems_automatic_sync = $config->get('collector_systems_automatic_sync') ?? 'manually';

    if ($saved_value_collector_systems_automatic_sync !== 'manually') {
      $event_timestamp = $config->get('collector_systems_automatic_sync_time');
      if ($event_timestamp) {
        // Create a DateTime object from the timestamp
        $timezone = $this->configFactory->get('system.date')->get('timezone')['default'] ?? 'UTC';
        $date_time = new DrupalDateTime($event_timestamp, new \DateTimeZone($timezone));

        $formatted_time = $date_time->format('l, F j, Y \a\t g:i A T');

        return '<p>The sync is scheduled for: ' . $formatted_time . ' based on the frequency you choose below </p>';
      }
    }
    return NULL;
  }

}
