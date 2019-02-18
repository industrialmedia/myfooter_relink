<?php

namespace Drupal\myfooter_relink;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for defining MyfooterRelink entities.
 */
interface MyfooterRelinkInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  
  /**
   * Gets the weight.
   *
   * @return int
   *   weight of the MyfooterRelink.
   */
  public function getWeight();

  /**
   * Sets the MyfooterRelink weight.
   *
   * @param int $weight
   *   The MyfooterRelink weight.
   *
   * @return \Drupal\myfooter_relink\MyfooterRelinkInterface
   *   The called MyfooterRelink entity.
   */
  public function setWeight($weight);



  /**
   * Gets the items.
   *
   * @return array
   *   items of the MyfooterRelink.
   */
  public function getItems();

  /**
   * Sets the MyfooterRelink items.
   *
   * @param array $items
   *   The MyfooterRelink items.
   *
   * @return \Drupal\myfooter_relink\MyfooterRelinkInterface
   *   The called MyfooterRelink entity.
   */
  public function setItems($items);



  /**
   * Gets logic used to compute, either 'and' or 'or'.
   *
   * @return string
   *   Either 'and' or 'or'.
   */
  public function getLogic();

  /**
   * Sets logic used to compute, either 'and' or 'or'.
   *
   * @param string $logic
   *   Either 'and' or 'or'.
   */
  public function setLogic($logic);


  /**
   * Returns the conditions.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getConditions();


  /**
   * Returns the condition.
   *
   * @param string $condition_id
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function getCondition($condition_id);


  /**
   *  Add condition to Conditions.
   *
   * @param array $configuration
   * @return \Drupal\Core\Condition\ConditionInterface
   *   condition.
   */
  public function addCondition($configuration);


  /**
   *  Remove condition from Conditions.
   *
   * @param string $condition_id
   * @return \Drupal\myfooter_relink\MyfooterRelinkInterface
   *   The called MyfooterRelink entity.
   */
  public function removeCondition($condition_id);


  

  
}
