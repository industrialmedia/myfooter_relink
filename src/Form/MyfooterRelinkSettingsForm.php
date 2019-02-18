<?php

namespace Drupal\myfooter_relink\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Condition\ConditionManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class MyfooterRelinkSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {


  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;


  /**
   * Constructs
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Condition\ConditionManager $condition_manager
   *   The condition manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConditionManager $condition_manager) {
    parent::__construct($config_factory);
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'myfooter_relink_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['myfooter_relink.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('myfooter_relink.settings');
    $options = [];
    $available_plugins = $this->conditionManager->getDefinitions();
    foreach ($available_plugins as $available_plugin_name => $available_plugin) {
      $options[$available_plugin_name] = $available_plugin['label'];
    }

    $form['used_conditions'] = [
      '#type' => 'checkboxes',
      '#title' => 'Использовать условия',
      '#options' => $options,
      '#default_value' => !empty($config->get('used_conditions')) ? $config->get('used_conditions') : [],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $used_conditions = $form_state->getValue('used_conditions');
    $used_conditions = array_filter($used_conditions);
    $this->config('myfooter_relink.settings')
      ->set('used_conditions', $used_conditions)
      ->save();
    parent::submitForm($form, $form_state);
  }


}
