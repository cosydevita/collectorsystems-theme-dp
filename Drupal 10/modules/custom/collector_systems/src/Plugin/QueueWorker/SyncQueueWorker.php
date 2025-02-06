<?php

namespace Drupal\collector_systems\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * @QueueWorker(
 *   id = "collector_systems_sync_queue_worker",
 *   title = @Translation("Collector Systems Sync Queue Worker"),
 *   cron = {"time" = 60}
 * )
 */
class SyncQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected $syncService;
  protected $logger;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('collector_systems');
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  public function processItem($item) {
    $queue_type = $item['queue_type'];
    // Process each item in the queue.
    $this->logger->info('Processing item: ' . print_r($item, TRUE));

    if ($queue_type == 'dataset') {
      $btn_action = 'update_dataset';
      $form_object = new \Drupal\collector_systems\Form\CreateTablesForm();
      $form_object->processItem($item['data'], $btn_action);
    }
    elseif ($queue_type == 'object_images'){
      $selected_option = 'save_to_database';
      $form_object = new \Drupal\collector_systems\Form\ObjectImagesImportForm();
      $form_object->processItem($item['data'], $selected_option);
    }
    elseif ($queue_type == 'other_images'){
      $selected_option = 'save_to_database';
      $form_object = new \Drupal\collector_systems\Form\OtherImagesImportForm();
      $form_object->processItem($item['data'], $selected_option);
    }
  }
}
