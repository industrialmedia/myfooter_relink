<?php

namespace Drupal\myfooter_relink\Entity;

use Drupal\myfooter_relink\MyfooterRelinkInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the MyfooterRelink entity.
 *
 * @ConfigEntityType(
 *   id = "myfooter_relink",
 *   label = @Translation("MyfooterRelink"),
 *   handlers = {
 *     "list_builder" = "Drupal\myfooter_relink\Controller\MyfooterRelinkListBuilder",
 *     "form" = {
 *       "add" =    "Drupal\myfooter_relink\Form\MyfooterRelinkForm",
 *       "edit" =   "Drupal\myfooter_relink\Form\MyfooterRelinkForm",
 *       "delete" = "Drupal\myfooter_relink\Form\MyfooterRelinkDeleteForm"
 *     }
 *   },
 *   config_prefix = "myfooter_relink",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "items",
 *     "logic",
 *     "conditions",
 *     "weight",
 *   },
 *   links = {
 *     "canonical" =   "/admin/seo/myfooter_relink/list/{myfooter_relink}",
 *     "edit-form" =   "/admin/seo/myfooter_relink/list/{myfooter_relink}/edit",
 *     "delete-form" = "/admin/seo/myfooter_relink/list/{myfooter_relink}/delete",
 *     "collection" =  "/admin/seo/myfooter_relink/list"
 *   }
 * )
 */
class MyfooterRelink extends ConfigEntityBase implements MyfooterRelinkInterface {
  /**
   * The MyfooterRelink ID.
   *
   * @var string
   */
  protected $id;


  /**
   * The MyfooterRelink label.
   *
   * @var string
   */
  protected $label;



  /**
   * The weight.
   *
   * @var int
   */
  protected $weight = 0;


  /**
   * @var array
   */
  protected $items = [];


  /**
   * The configuration of conditions.
   *
   * @var array
   */
  protected $conditions = [];

  /**
   * Tracks the logic used to compute, either 'and' or 'or'.
   *
   * @var string
   */
  protected $logic = 'and';


  /**
   * The plugin collection that holds the conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $conditionCollection;


  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'conditions' => $this->getConditions(),
    ];
  }
  


  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * {@inheritdoc}
   */
  public function setItems($items) {
    $this->items = $items;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getLogic() {
    return $this->logic;
  }

  /**
   * {@inheritdoc}
   */
  public function setLogic($logic) {
    $this->logic = $logic;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    if (!$this->conditionCollection) {
      $this->conditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('conditions'));
    }
    return $this->conditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getCondition($condition_id) {
    return $this->getConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function addCondition($configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getConditions()
      ->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function removeCondition($condition_id) {
    $this->getConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags = parent::getCacheTags();
    return Cache::mergeTags($tags, ['myfooter_relink:' . $this->id]);
  }

}
