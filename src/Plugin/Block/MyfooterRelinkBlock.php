<?php

namespace Drupal\myfooter_relink\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\myfooter_relink\MyfooterRelinkEvaluator;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;


/**
 * @Block(
 *   id = "myfooter_relink",
 *   admin_label = "Футерна перелинковка",
 * )
 */
class MyfooterRelinkBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  /**
   * The MyfooterRelinkEvaluator service.
   *
   * @var \Drupal\myfooter_relink\MyfooterRelinkEvaluator
   */
  protected $myrouteBreadcrumbEvaluator;


  /**
   * The menu storage.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $myfooterRelinkStorage;


  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\myfooter_relink\MyfooterRelinkEvaluator $myfooter_relink_evaluator
   *   The myfooter relink evaluator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, MyfooterRelinkEvaluator $myfooter_relink_evaluator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->myrouteBreadcrumbEvaluator = $myfooter_relink_evaluator;
    $this->myfooterRelinkStorage = $this->entityTypeManager->getStorage('myfooter_relink');
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /* @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $container->get('entity_type.manager');
    /* @var \Drupal\myfooter_relink\MyfooterRelinkEvaluator $myfooter_relink_evaluator */
    $myfooter_relink_evaluator = $container->get('myfooter_relink.evaluator');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $myfooter_relink_evaluator
    );
  }


  /**
   * {@inheritdoc}
   */
  public function build() {

    if (!$myfooter_relink_id = $this->myrouteBreadcrumbEvaluator->evaluateAll()) {
      return [];
    }
    /** @var \Drupal\myfooter_relink\Entity\MyfooterRelink $myfooter_relink */
    $myfooter_relink = $this->myfooterRelinkStorage->load($myfooter_relink_id);

    $items = $myfooter_relink->getItems();
    if (empty($items)) {
      return [];
    }

    foreach ($items as &$item) {
      $name = $item['group_name'][0]['name'];
      $link = $item['group_name'][0]['link'];
      $item['name'] = $name;
      $item['url'] = Url::fromUserInput($link);
      unset($item['group_name']);
      if (!empty($item['group_items'])) {
        $item['children'] = $item['group_items'];
        unset($item['group_items']);
        foreach ($item['children'] as &$item_children) {
          $item_children['url'] = Url::fromUserInput($item_children['link']);
          unset($item_children['link']);
        }
      }
    }
    
    return [
      '#theme' => 'myfooter_relink',
      '#items' => $items,
      '#cache' => [
        'tags' => $myfooter_relink->getCacheTags(),
      ],
    ];

  }


  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url.path']);
  }


}