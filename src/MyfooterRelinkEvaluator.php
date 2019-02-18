<?php

namespace Drupal\myfooter_relink;

use Drupal\myfooter_relink\Entity\MyfooterRelink;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ConditionEvaluator.
 *
 * @package Drupal\myfooter_relink
 */
class MyfooterRelinkEvaluator {

  use ConditionAccessResolverTrait;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * @var array
   */
  protected $evaluations = [];


  /**
   * @var int
   */
  protected $evaluation_all = NULL;


  /**
   * Constructor.
   *
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->contextRepository = $context_repository;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  public function evaluateAll() {
    if (!isset($this->evaluation_all)) {
      $myfooter_relinks = $this->entityTypeManager->getStorage('myfooter_relink')
        ->loadMultiple();
      uasort($myfooter_relinks, function (MyfooterRelink $a, MyfooterRelink $b) {
        $a = $a->getWeight();
        $b = $b->getWeight();
        if ($a == $b) {
          return 0;
        }
        return ($a < $b) ? -1 : 1;
      });
      if (!empty($myfooter_relinks)) {
        foreach ($myfooter_relinks as $myfooter_relink) {
          /** @var \Drupal\myfooter_relink\Entity\MyfooterRelink $myfooter_relink */
          if ($this->evaluate($myfooter_relink)) {
            $this->evaluation_all = $myfooter_relink->id();
            return $this->evaluation_all; // Сразу возращаем, берем 1й из списка
          }
        }
      }
      if (!isset($this->evaluation_all)) {
        $this->evaluation_all = FALSE;
      }
    }
    return $this->evaluation_all;
  }

  /**
   * @param \Drupal\myfooter_relink\Entity\MyfooterRelink $myfooter_relink
   *
   * @return boolean
   */
  public function evaluate(MyfooterRelink $myfooter_relink) {
    $id = $myfooter_relink->id();
    if (!isset($this->evaluations[$id])) {
      /** @var ConditionPluginCollection $conditions */
      $conditions = $myfooter_relink->getConditions();
      if (empty($conditions)) {
        return TRUE;
      }
      $logic = $myfooter_relink->getLogic();
      if ($this->applyContexts($conditions, $logic)) {
        /** @var \Drupal\Core\Condition\ConditionInterface[] $conditions */
        $this->evaluations[$id] = $this->resolveConditions($conditions, $logic);
      }
      else {
        $this->evaluations[$id] = FALSE;
      }
    }
    return $this->evaluations[$id];
  }

  /**
   * @param \Drupal\Core\Condition\ConditionPluginCollection $conditions
   * @param string $logic
   *
   * @return bool
   */
  protected function applyContexts(ConditionPluginCollection &$conditions, $logic) {
    $have_1_testable_condition = FALSE;
    foreach ($conditions as $id => $condition) {
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          //dump($contexts);
          $this->contextHandler->applyContextMapping($condition, $contexts);
          $have_1_testable_condition = TRUE;
        } catch (ContextException $e) {
          if ($logic == 'and') {
            // Logic is all and found condition with contextException.
            return FALSE;
          }
          $conditions->removeInstanceId($id);
        }

      }
      else {
        $have_1_testable_condition = TRUE;
      }
    }
    if ($logic == 'or' && !$have_1_testable_condition) {
      return FALSE;
    }
    return TRUE;
  }

}
