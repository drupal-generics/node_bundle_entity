<?php

namespace Drupal\node_bundle_entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for the node bundle entity plugins.
 *
 * @package Drupal\node_bundle_entity
 */
class NodeBundleEntityManager extends DefaultPluginManager {

  /**
   * The discovered node entity classes.
   *
   * @var array
   */
  protected $nodeClasses = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Entity',
      $namespaces,
      $module_handler,
      'Drupal\node_bundle_entity\NodeBundleEntityPluginInterface',
      'Drupal\node_bundle_entity\Annotation\NodeBundleEntity'
    );

    $this->setCacheBackend($cache_backend, 'node_bundle_entity');
  }

  /**
   * Get the node bundle entity extension for the content type.
   *
   * @param string $bundle
   *   The content type of the node.
   *
   * @return \Drupal\node_bundle_entity\NodeBundleEntityPluginInterface|null
   *   The entity class if exists.
   */
  public function getClass($bundle) {
    if (array_key_exists($bundle, $this->nodeClasses)) {
      return $this->nodeClasses[$bundle];
    }

    $this->nodeClasses[$bundle] = NULL;

    foreach ($this->getDefinitions() as $id => $definition) {
      if ($definition['bundle'] == $bundle) {
        $this->nodeClasses[$bundle] = $definition['class'];
        return $this->nodeClasses[$bundle];
      }
      else {
        $this->nodeClasses[$definition['bundle']] = $definition['class'];
      }
    }

    $this->nodeClasses[$bundle] = NULL;
    return NULL;
  }

}
