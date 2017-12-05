<?php

namespace Drupal\node_bundle_entity\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for NodeBundleEntity plugins.
 *
 * @Annotation
 */
class NodeBundleEntity extends Plugin {

  /**
   * The content type of the node which to extend.
   *
   * @var string
   */
  public $bundle;

}
