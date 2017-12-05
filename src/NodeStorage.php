<?php

namespace Drupal\node_bundle_entity;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeStorage as OriginalNodeStorage;

/**
 * NodeStorage storage replacement.
 *
 * This custom storage will use the plugin manager and will instantiate nodes
 * with the corresponding class.
 *
 * @package Drupal\node_entity_bundle
 */
class NodeStorage extends OriginalNodeStorage {

  /**
   * Node bundle entity class manager.
   *
   * @var \Drupal\node_bundle_entity\NodeBundleEntityManager
   */
  protected $nodeClassManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityManagerInterface $entity_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager) {
    parent::__construct($entity_type, $database, $entity_manager, $cache, $language_manager);
    $this->nodeClassManager = \Drupal::service('plugin.manager.node_bundle_entity');
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    // We have to determine the bundle first.
    $bundle = FALSE;
    if ($this->bundleKey) {
      if (!isset($values[$this->bundleKey])) {
        throw new EntityStorageException('Missing bundle for entity type ' . $this->entityTypeId);
      }
      $bundle = $values[$this->bundleKey];
    }

    // CUSTOM!! Get the node class.
    $class = $this->nodeClassManager->getClass($bundle) ?: $this->entityClass;
    $entity = new $class([], $this->entityTypeId, $bundle);
    $this->initFieldValues($entity, $values);
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records, $load_from_revision = FALSE) {
    // NOTE!! Code in here is mostly the parents code.
    if (!$records) {
      return [];
    }

    $values = [];
    foreach ($records as $id => $record) {
      $values[$id] = [];
      // Skip the item delta and item value levels (if possible) but let the
      // field assign the value as suiting. This avoids unnecessary array
      // hierarchies and saves memory here.
      foreach ($record as $name => $value) {
        // Handle columns named [field_name]__[column_name] (e.g for field types
        // that store several properties).
        if ($field_name = strstr($name, '__', TRUE)) {
          $property_name = substr($name, strpos($name, '__') + 2);
          $values[$id][$field_name][LanguageInterface::LANGCODE_DEFAULT][$property_name] = $value;
        }
        else {
          // Handle columns named directly after the field (e.g if the field
          // type only stores one property).
          $values[$id][$name][LanguageInterface::LANGCODE_DEFAULT] = $value;
        }
      }
    }

    // Initialize translations array.
    $translations = array_fill_keys(array_keys($values), []);

    // Load values from shared and dedicated tables.
    $this->loadFromSharedTables($values, $translations);
    $this->loadFromDedicatedTables($values, $load_from_revision);

    $entities = [];
    foreach ($values as $id => $entity_values) {
      $bundle = $this->bundleKey ? $entity_values[$this->bundleKey][LanguageInterface::LANGCODE_DEFAULT] : FALSE;
      // CUSTOM!! Get the node class.
      $class = $this->nodeClassManager->getClass($bundle) ?: $this->entityClass;
      // Turn the record into an entity class.
      $entities[$id] = new $class($entity_values, $this->entityTypeId, $bundle, array_keys($translations[$id]));
    }

    return $entities;

  }

}
