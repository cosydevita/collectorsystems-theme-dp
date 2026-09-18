<?php

namespace Drupal\collector_systems\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class SyncQueueProcessController extends ControllerBase
{

  public function run() {
      return $this->processQueue('collector_systems_sync_queue_worker');
  }

  /**
   * Access callback: only requests carrying the current sync queue key.
   *
   * hook_cron() triggers this route via an unauthenticated loopback curl
   * call to process the sync queue in parallel, so it can't be gated by a
   * Drupal permission. A shared secret (mirroring core's /cron/{cron_key})
   * keeps it closed to everyone else instead.
   */
  public function access(string $key): AccessResultInterface {
    return AccessResult::allowedIf(hash_equals(static::getQueueProcessKey(), $key))->setCacheMaxAge(0);
  }

  /**
   * Gets the sync queue processing key, generating and storing one if unset.
   */
  public static function getQueueProcessKey(): string {
    $key = \Drupal::state()->get('collector_systems.sync_queue_key');
    if (!$key) {
      $key = Crypt::randomBytesBase64(55);
      \Drupal::state()->set('collector_systems.sync_queue_key', $key);
    }
    return $key;
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
