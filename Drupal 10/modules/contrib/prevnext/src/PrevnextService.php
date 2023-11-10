<?php

namespace Drupal\prevnext;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Class PrevnextService.
 *
 * @package Drupal\prevnext
 */
class PrevnextService implements PrevnextServiceInterface {

  /**
   * The entity manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Previous / Next nids.
   *
   * @var array
   */
  public $prevnext;

  /**
   * PrevnextService constructor.
   *
   * @param EntityTypeManager $entityTypeManager
   *   The entity type manager instance.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousNext(Node $node) {
    $nodes = $this->getNodesOfType($node);
    $current_nid = $node->id();

    $current_key = array_search($current_nid, $nodes);
    $this->prevnext['prev'] = ($current_key == 0) ? '' : $nodes[$current_key - 1];
    $this->prevnext['next'] = ($current_key == count($nodes) - 1) ? '' : $nodes[$current_key + 1];

    return $this->prevnext;
  }

  /**
   * Retrieves all nodes of the same type and language of given.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node entity.
   *
   * @return array
   *   An array of nodes filtered by type, status and language.
   */
  protected function getNodesOfType(Node $node) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $bundle = $node->bundle();
    $langcode = $node->language()->getId();
    $nodes = $query->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $bundle)
      ->condition('langcode', $langcode)
      ->addMetaData('type', $bundle)
      ->addMetaData('langcode', $langcode)
      ->addTag('prev_next_nodes_type')
      ->execute();

    return array_values($nodes);
  }

}
