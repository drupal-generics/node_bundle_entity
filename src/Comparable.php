<?php

namespace Drupal\node_bundle_entity;

/**
 * Interface Comparable.
 *
 * @package Drupal\node_bundle_entity
 */
interface Comparable {

  /**
   * Define the rule that defines the equality of two node objects.
   *
   * @param mixed $object
   *   The other object we compare to.
   *
   * @return bool
   *   The result.
   */
  public function equals($object);

}
