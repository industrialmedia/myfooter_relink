<?php

namespace Drupal\myfooter_relink\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginAssignmentTrait;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\myfooter_relink\MyfooterRelinkInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Provides a base form for editing and adding a condition.
 */
abstract class ConditionFormBase extends FormBase {

  use ContextAwarePluginAssignmentTrait;


  /**
   * The entity this condition belongs to.
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
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * ConditionFormBase constructor.
   *
   * @param ContextRepositoryInterface $context_repository
   *   The context repository
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ContextRepositoryInterface $context_repository, MessengerInterface $messenger) {
    $this->contextRepository = $context_repository;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var ContextRepositoryInterface $context_repository */
    $context_repository = $container->get('context.repository');
    /* @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static (
      $context_repository,
      $messenger
    );
  }

  /**
   * Prepares the condition used by this form.
   *
   * @param string $condition_id
   *   Either a condition ID, or the plugin ID used to create a new
   *   condition.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The condition object.
   */
  abstract protected function prepareCondition($condition_id);

  /**
   * Returns the text to use for the submit button.
   *
   * @return string
   *   The submit button text.
   */
  abstract protected function submitButtonText();

  /**
   * Returns the text to use for the submit message.
   *
   * @return string
   *   The submit message text.
   */
  abstract protected function submitMessageText();

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, MyfooterRelinkInterface $myfooter_relink = NULL, $condition_id = NULL) {
    $this->myfooter_relink = $myfooter_relink;
    $this->condition = $this->prepareCondition($condition_id);
    $form_state->setTemporaryValue('gathered_contexts', $this->contextRepository->getAvailableContexts());
    // Allow the condition to add to the form.
    $form['condition'] = $this->condition->buildConfigurationForm([], $form_state);
    $form['condition']['#tree'] = TRUE;
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->submitButtonText(),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Allow the condition to validate the form.
    $condition_values = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->validateConfigurationForm($form, $condition_values);
    // Update the original form values.
    $form_state->setValue('condition', $condition_values->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Allow the condition to submit the form.
    $condition_values = (new FormState())->setValues($form_state->getValue('condition'));
    $this->condition->submitConfigurationForm($form, $condition_values);
    // Update the original form values.
    $form_state->setValue('condition', $condition_values->getValues());
    if ($this->condition instanceof ContextAwarePluginInterface) {
      $this->condition->setContextMapping($condition_values->getValue('context_mapping', []));
    }
    // Set the submission message.
    $this->messenger->addStatus($this->submitMessageText());
    $configuration = $this->condition->getConfiguration();
    // If this condition is new, add it to the myfooter_relink.
    if (!isset($configuration['uuid'])) {
      $this->myfooter_relink->addCondition($configuration);
    }
    // Save the myfooter_relink entity.
    $this->myfooter_relink->save();
    $form_state->setRedirectUrl(Url::fromRoute('entity.myfooter_relink.edit_form', [
      'myfooter_relink' => $this->myfooter_relink->id(),
    ]));
  }

}
