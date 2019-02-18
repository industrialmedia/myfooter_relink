<?php

namespace Drupal\myfooter_relink\Form;

use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Provides a form for adding a new condition.
 */
class ConditionAddForm extends ConditionFormBase {

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * Constructs a new ConditionAddForm.
   *
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager.
   */
  public function __construct(ContextRepositoryInterface $context_repository, MessengerInterface $messenger, ConditionManager $condition_manager) {
    parent::__construct($context_repository, $messenger);
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository */
    $context_repository = $container->get('context.repository');
    /* @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    /** @var \Drupal\Core\Condition\ConditionManager $condition_manager */
    $condition_manager = $container->get('plugin.manager.condition');
    return new static(
      $context_repository,
      $messenger,
      $condition_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myfooter_relink_condition_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    return $this->conditionManager->createInstance($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Add condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label condition has been added.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
