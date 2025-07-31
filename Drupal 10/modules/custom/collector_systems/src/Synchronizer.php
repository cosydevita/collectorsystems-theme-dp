<?php

namespace Drupal\collector_systems;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\collector_systems\DataSyncManager;
use Drupal\collector_systems\ImagesSyncManager;

/**
 * Collector Systems Synchronizer Class
 */
class Synchronizer
{
  /**
   * Logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Data sync manager service.
   *
   * @var \Drupal\collector_systems\Service\DataSyncManager
   */

  protected $dataSyncManager;

  /**
   * Images sync manager service.
   *
   * @var \Drupal\collector_systems\Service\ImagesSyncManager
   */
  protected $imagesSyncManager;


  public function __construct(LoggerChannelFactoryInterface $logger_factory, DataSyncManager $dataSyncManager, ImagesSyncManager $imagesSyncManager)
  {  
    $this->logger = $logger_factory->get('collector_systems');
    $this->dataSyncManager = $dataSyncManager;
    $this->imagesSyncManager = $imagesSyncManager;
  }

  /**
   * Add items to the queue for automatic sync.
   */
  public function addItemsToQueue()
  {
    $queue = \Drupal::queue('collector_systems_sync_queue_worker');

    try {
      collector_systems_update_CSSynced_table('data_and_images', 'automatic', true, false);
    } catch (\Throwable $e) {
      $this->logger->error('Error in collector_systems_update_CSSynced_table: @message', ['@message' => $e->getMessage()]);
    }

    // Collect data for processing.
    $data = $this->dataSyncManager->getDataForProcessing();
    // Add dataset items to the queue.
    $queue_type = 'dataset';
    foreach ($data as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    $this->logger->debug('Data items added to queue.');

    //Add Other Images data to the queue
    $data_other_images = $this->imagesSyncManager->getDataForProcessingOtherImages();
    // Add items to the queue.
    $queue_type = 'other_images';
    foreach ($data_other_images as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    $this->logger->debug('Other Images item added to queue.');

    //Add object Images data to the queue
    $data_object_images = $this->imagesSyncManager->getDataForProcessingObjectImages();
    // Add items to the queue.
    $queue_type = 'object_images';
    foreach ($data_object_images as $item) {
      $queue->createItem([
        'data' => $item,
        'queue_type' => $queue_type
      ]);
    }

    $this->logger->debug('Object Images item added to queue.');
  }
}
