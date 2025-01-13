<?php

namespace Drupal\collector_systems;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Collector Systems Synchronizer Class
 */
class Synchronizer
{

    protected $logger;
    protected $apiService;

    public function __construct(LoggerChannelFactoryInterface $logger_factory, $api_service)
    {
        $this->logger = $logger_factory->get('collector_systems');
        $this->apiService = $api_service;
    }

    /**
     * Run automatic synchronization.
     */
    public function runAutomaticSync()
    {
      $btn_action = 'update_dataset';

      // Collect data for processing.
      $form_object = new \Drupal\collector_systems\Form\CreateTablesForm();
      $data = $form_object->getDataForProcessing();

      // Start the batch process.
      $form_object->startBatchProcess($data, $btn_action);

      $this->logger->info('Automatic sync triggered.');
    }
}
