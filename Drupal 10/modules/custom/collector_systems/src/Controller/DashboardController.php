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
    $sync_tracked_info = [];
    if($table_exists){
       // Check if the record exists and fetch the LastSyncedDateTime and LastSyncedBy.
      $info_manual_sync_data = $database->select($table_CSSynced)
      ->fields($table_CSSynced, ['LastSyncedBy', 'SyncStarted', 'SyncCompleted', 'SyncType', 'SyncTrigger', 'SyncCompletionTime'])
      ->condition('SyncType', 'data')
      ->condition('SyncTrigger', 'manual')
      ->execute()
      ->fetchAssoc();

      $info_manual_sync_images = $database->select($table_CSSynced)
      ->fields($table_CSSynced, ['LastSyncedBy', 'SyncStarted', 'SyncCompleted', 'SyncType', 'SyncTrigger', 'SyncCompletionTime'])
      ->condition('SyncType', 'images')
      ->condition('SyncTrigger', 'manual')
      ->execute()
      ->fetchAssoc();

      $info_automatic_sync = $database->select($table_CSSynced)
      ->fields($table_CSSynced, ['LastSyncedBy', 'SyncStarted', 'SyncCompleted', 'SyncType', 'SyncTrigger', 'SyncCompletionTime'])
      ->condition('SyncType', 'data_and_images')
      ->condition('SyncTrigger', 'automatic')
      ->execute()
      ->fetchAssoc();



      $sync_tracked_info['info_manual_sync_data'] = $info_manual_sync_data;
      $sync_tracked_info['info_manual_sync_images'] = $info_manual_sync_images;
      $sync_tracked_info['info_automatic_sync'] = $info_automatic_sync;
    }

    $automatic_sync_settings_form = \Drupal::formBuilder()->getForm('Drupal\collector_systems\Form\AutomaticSyncSettingsForm');

    $form_sync_images = \Drupal::formBuilder()->getForm('Drupal\collector_systems\Form\SyncImagesForm');


    $scheduled_date_and_time_information = $this->getScheduledDateAndTimeInformation();

    $config_collector_systems = \Drupal::config('collector_systems.settings');
    $checkbox_groups = $config_collector_systems->get('checkboxes.groups');
    $checkbox_collections = $config_collector_systems->get('checkboxes.collections');
    $checkbox_exhibitions = $config_collector_systems->get('checkboxes.exhibitions');
    $checkbox_artists = $config_collector_systems->get('checkboxes.artists');
    
    $checkboxes_data = [
      'checkbox_groups' => $checkbox_groups,
      'checkbox_collections' => $checkbox_collections,
      'checkbox_exhibitions' => $checkbox_exhibitions,
      'checkbox_artists' => $checkbox_artists,
    ];
  

    $build = [
      '#theme' => 'dashboard',
      '#automatic_sync_settings_form' => $automatic_sync_settings_form,
      '#scheduled_date_and_time_information' => $scheduled_date_and_time_information,
      '#form_sync_images' => $form_sync_images,
      '#sync_tracked_info' => $sync_tracked_info,
      '#checkboxes_data' => $checkboxes_data
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
