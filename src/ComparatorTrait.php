<?php

namespace Drupal\node_bundle_entity;

use Drupal\node\NodeInterface;

/**
 * Class ComparatorTrait.
 *
 * @package Drupal\node_bundle_entity
 */
trait ComparatorTrait {

  /**
   * Implements the compareTo method.
   *
   * @param mixed $other
   *   The object to which we compare the current instance.
   *
   * @return bool
   *   The result of the comparison.
   *
   * @see \Doctrine\Common\Comparable::compareTo()
   */
  public function equals($other) {
    if ($other instanceof NodeInterface) {
      return $this->language()->getId() == $other->language()->getId() &&
        $this->id() == $other->id();
    }

    return FALSE;
  }

}
