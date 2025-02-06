<?php

namespace Drupal\collector_systems;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Collector Systems Synchronizer Class
 */
class Synchronizer
{
  protected $logger;

  public function __construct(LoggerChannelFactoryInterface $logger_factory)
  {
      $this->logger = $logger_factory->get('collector_systems');
  }

  /**
   * Add items to the queue for automatic sync.
   */
  public function addItemsToQueue()
  {
    $queue = \Drupal::queue('collector_systems_sync_queue_worker');

    // Collect data for processing.
    $form_object = new \Drupal\collector_systems\Form\CreateTablesForm();
    $data = $form_object->getDataForProcessing();
    // Add dataset items to the queue.
    $queue_type = 'dataset';
    foreach ($data as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    //Add object Images data to the queue
    $form_object_images = new \Drupal\collector_systems\Form\ObjectImagesImportForm();
    $data_object_images = $form_object_images->getDataForProcessing();
    // Add items to the queue.
    $queue_type = 'object_images';
    foreach ($data_object_images as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    //Add Other Images data to the queue
    $form_other_images = new \Drupal\collector_systems\Form\OtherImagesImportForm();
    $data_other_images = $form_other_images->getDataForProcessing();
    // Add items to the queue.
    $queue_type = 'other_images';
    foreach ($data_other_images as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    $this->logger->debug('Items added to queue.');
  }
}
