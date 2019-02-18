<?php

namespace Drupal\myfooter_relink\Controller;

use Drupal\myfooter_relink\Entity\MyfooterRelink;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * Class MyfooterRelinkConditionController.
 *
 * @package Drupal\myfooter_relink\Controller
 */
class MyfooterRelinkConditionController extends ControllerBase {

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs
   *
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConditionManager $condition_manager) {
    $this->configFactory = $config_factory;
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $container->get('config.factory');
    /* @var \Drupal\Core\Condition\ConditionManager $condition_manager */
    $condition_manager = $container->get('plugin.manager.condition');
    return new static(
      $config_factory,
      $condition_manager
    );
  }


  /**
   * Presents a list of conditions to add to the myfooter_relink entity.
   *
   * @param \Drupal\myfooter_relink\Entity\MyfooterRelink $myfooter_relink
   *   The myfooter_relink entity
   * @return array
   *   The condition selection page.
   */
  public function selectCondition(MyfooterRelink $myfooter_relink) {
    $config = $this->configFactory->get('myfooter_relink.settings');
    $used_conditions = $config->get('used_conditions');
    if (empty($used_conditions)) {
      $used_conditions = [];
    }
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitions();
    foreach ($available_plugins as $condition_id => $condition) {
      if (in_array($condition_id, $used_conditions)) {
        $build['#links'][$condition_id] = [
          'title' => $condition['label'],
          'url' => Url::fromRoute('myfooter_relink.condition_add', [
            'myfooter_relink' => $myfooter_relink->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 'auto',
            ]),
          ],
        ];
      }
    }
    return $build;
  }

}
