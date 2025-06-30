<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class SyncQueueProcessController extends ControllerBase
{

  public function run() {
      return $this->processQueue('collector_systems_sync_queue_worker');
  }
      
  private function processQueue($queue_id) {
    $queue = \Drupal::queue($queue_id);
    $manager = \Drupal::service('plugin.manager.queue_worker');
    $worker = $manager->createInstance($queue_id);
  
    $i = 0;
    $max = 1;
    while ($i++ < $max && ($item = $queue->claimItem())) {
      try {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      } catch (\Exception $e) {
        $queue->releaseItem($item);
      }
    }
  
    return new Response("Processed $i items.");
  }
}
