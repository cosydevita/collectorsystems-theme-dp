<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Database\Database;

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
      $container->get('current_user')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
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

    $build = [
      '#theme' => 'dashboard',
      '#API_Synced_On' => $last_synced_date_time,
      '#API_Synced_By' => $last_synced_by

    ];
    $build['#attached']['library'][] = 'collector_systems/dashboard';

    return $build;
  }






}
