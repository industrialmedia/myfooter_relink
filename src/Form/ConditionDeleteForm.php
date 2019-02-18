<?php

namespace Drupal\myfooter_relink\Form;


use Drupal\myfooter_relink\Entity\MyfooterRelink;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting an condition.
 */
class ConditionDeleteForm extends ConfirmFormBase implements ContainerInjectionInterface {



  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The myfooter_relink entity this selection condition belongs to.
   *
   * @var \Drupal\myfooter_relink\Entity\MyfooterRelink
   */
  protected $myfooter_relink;

  /**
   * The condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $condition;


  /**
   * Constructs
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $messenger
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myfooter_relink_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the condition %name?', ['%name' => $this->condition->getPluginDefinition()['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->myfooter_relink->urlInfo('edit-form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, MyfooterRelink $myfooter_relink = NULL, $condition_id = NULL) {
    $this->myfooter_relink = $myfooter_relink;
    $this->condition = $myfooter_relink->getCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->myfooter_relink->removeCondition($this->condition->getConfiguration()['uuid']);
    $this->myfooter_relink->save();
    $this->messenger->addStatus($this->t('The condition %name has been removed.', ['%name' => $this->condition->getPluginDefinition()['label']]));
    $form_state->setRedirectUrl(Url::fromRoute('entity.myfooter_relink.edit_form', [
      'myfooter_relink' => $this->myfooter_relink->id(),
    ]));
  }

}
