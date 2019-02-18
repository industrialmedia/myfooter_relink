<?php

namespace Drupal\myfooter_relink\Form;


use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\myfooter_relink\Entity\MyfooterRelink;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Entity form for MyfooterRelink entity.
 */
class MyfooterRelinkForm extends EntityForm implements ContainerInjectionInterface {


  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  


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
    /* @var MessengerInterface $messenger */
    $messenger = $container->get('messenger');
    return new static(
      $messenger
    );
  }


  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\myfooter_relink\Entity\MyfooterRelink $myfooter_relink */
    $myfooter_relink = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $myfooter_relink->label(),
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $myfooter_relink->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\myfooter_relink\Entity\MyfooterRelink::load',
      ),
      '#disabled' => !$myfooter_relink->isNew(),
    );


    if (!$myfooter_relink->isNew()) {
      $form['items_section'] = $this->createItemsSet($form, $form_state, $myfooter_relink);
      $form['conditions_section'] = $this->createConditionsSet($form, $myfooter_relink);
      $form['logic'] = [
        '#type' => 'radios',
        '#options' => [
          'and' => $this->t('All conditions must pass'),
          'or' => $this->t('Only one condition must pass'),
        ],
        '#default_value' => $myfooter_relink->getLogic(),
      ];
    }

    $form['#attached']['library'][] = 'myfooter_relink/form';

    return $form;
  }


  protected function createItemsSet(array $form, FormStateInterface $form_state, MyfooterRelink $myfooter_relink) {
    $items = $myfooter_relink->getItems();

    if ($items) {
      $num_items = $form_state->get('num_items');
      if (empty($num_items)) {
        foreach ($items as $item) {
          $num_items[] = count($item['group_items']);
        }
      }
      $form_state->set('num_items', $num_items);
    }
    else {
      $num_items = $form_state->get('num_items');
    }
    if ($num_items === NULL) {
      $form_state->set('num_items', [2]);
      $num_items = [2];
    }


    //$input = $form_state->getUserInput();
    //dsm($input);


    $form['items_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Items'),
      '#open' => TRUE,
      '#prefix' => '<div id="items-section-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['items_section']['items'] = [
      '#tree' => TRUE,
    ];
    for ($i = 0; $i < count($num_items); $i++) {
      $row = [
        '#prefix' => '<div class="row-wrapper">',
        '#suffix' => '</div>',
      ];
      $row['group_name'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Group name'),
          $this->t('Group link'),
        ],
        '#empty' => $this->t('There are no items.'),
      ];

      $row['group_name'][0]['name'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => !empty($items[$i]['group_name'][0]['name']) ? $items[$i]['group_name'][0]['name'] : '',
      ];
      $row['group_name'][0]['link'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#default_value' => !empty($items[$i]['group_name'][0]['link']) ? $items[$i]['group_name'][0]['link'] : '',
        '#element_validate' => [[get_class($this), 'validateLink']],
      ];


      $row['group_items'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Name'),
          $this->t('Link'),
        ],
        '#empty' => $this->t('There are no items.'),
      ];
      for ($j = 0; $j < $num_items[$i]; $j++) {
        $row2 = [];
        $row2['name'] = [
          '#type' => 'textfield',
          '#title' => '',
          '#default_value' => !empty($items[$i]['group_items'][$j]['name']) ? $items[$i]['group_items'][$j]['name'] : '',
        ];
        $row2['link'] = [
          '#type' => 'textfield',
          '#title' => '',
          '#default_value' => !empty($items[$i]['group_items'][$j]['link']) ? $items[$i]['group_items'][$j]['link'] : '',
          '#element_validate' => [[get_class($this), 'validateLink']],
        ];
        $row['group_items'][] = $row2;
      }





      $row['actions'] = [
        '#type' => 'actions',
      ];
      $row['actions']['add_name'] = [
        '#type' => 'submit',
        '#name' => 'add_' . $i,
        '#value' => $this->t('Add one more') . ' #' . $i,
        '#submit' => ['::itemsSectionAddOneSubmit'],
        '#i' => $i,
        '#ajax' => [
          'callback' => '::itemsSectionAjaxCallback',
          'wrapper' => 'items-section-fieldset-wrapper',
          'progress' => [
            'type' => 'throbber',
            'message' => '',
          ],
        ],

      ];
      if ($num_items[$i] > 1) {
        $row['actions']['remove_name'] = [
          '#type' => 'submit',
          '#name' => 'remove_' . $i,
          '#value' => $this->t('Remove one') . ' #' . $i,
          '#submit' => ['::itemsSectionRemoveOneSubmit'],
          '#i' => $i,
          '#ajax' => [
            'callback' => '::itemsSectionAjaxCallback',
            'wrapper' => 'items-section-fieldset-wrapper',
            'progress' => [
              'type' => 'throbber',
              'message' => '',
            ],
          ],
        ];
      }




      $form['items_section']['items'][] = $row;
    }




    $form['items_section']['actions'] = [
      '#type' => 'actions',
    ];
    $form['items_section']['actions']['add_name'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['::itemsSectionAddOneSubmit'],
      '#ajax' => [
        'callback' => '::itemsSectionAjaxCallback',
        'wrapper' => 'items-section-fieldset-wrapper',
      ],

    ];
    if (count($num_items) > 1) {
      $form['items_section']['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['::itemsSectionRemoveOneSubmit'],
        '#ajax' => [
          'callback' => '::itemsSectionAjaxCallback',
          'wrapper' => 'items-section-fieldset-wrapper',
        ],
      ];
    }


    return $form['items_section'];
  }


  protected function createConditionsSet(array $form, MyfooterRelink $myfooter_relink) {
    $attributes = [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => 'auto',
      ]),
    ];
    $add_button_attributes = NestedArray::mergeDeep($attributes, [
      'class' => [
        'button',
        'button--small',
        'button-action',
        'form-item',
      ],
    ]);
    $form['conditions_section'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Conditions'),
      '#open' => TRUE,
    ];
    $form['conditions_section']['add_condition'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new condition'),
      '#url' => Url::fromRoute('myfooter_relink.condition_select', [
        'myfooter_relink' => $myfooter_relink->id(),
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    if ($conditions = $myfooter_relink->getConditions()) {
      $form['conditions_section']['conditions'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#empty' => $this->t('There are no conditions.'),
      ];
      foreach ($conditions as $condition_id => $condition) {
        $row = [];
        $row['label']['#markup'] = $condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('myfooter_relink.condition_edit', [
            'myfooter_relink' => $myfooter_relink->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('myfooter_relink.condition_delete', [
            'myfooter_relink' => $myfooter_relink->id(),
            'condition_id' => $condition_id,
          ]),
          'attributes' => $attributes,
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['conditions_section']['conditions'][$condition_id] = $row;
      }
    }
    return $form['conditions_section'];
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $items = $form_state->getValue('items');
    foreach ($items as &$item) {
      unset($item['actions']);
    }
    $form_state->setValue('items', $items);


  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $myfooter_relink = $this->entity;
    $is_new = $myfooter_relink->isNew();
    $status = $myfooter_relink->save();
    if ($status) {
      $this->messenger->addStatus($this->t('Saved the %label MyfooterRelink.', array(
        '%label' => $myfooter_relink->label(),
      )));
    }
    else {
      $this->messenger->addStatus($this->t('The %label MyfooterRelink was not saved.', array(
        '%label' => $myfooter_relink->label(),
      )));
    }
    if ($is_new) {
      $form_state->setRedirectUrl($myfooter_relink->toUrl('edit-form'));
    }
    else {
      $form_state->setRedirectUrl($myfooter_relink->toUrl('collection'));
    }
  }


  public function itemsSectionAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['items_section'];
  }


  public function itemsSectionAddOneSubmit(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $num_items = $form_state->get('num_items');
    if (isset($triggering_element['#i'])) {
      $i = $triggering_element['#i'];
      $num_items[$i] = $num_items[$i] + 1;
    }
    else {
      $num_items[count($num_items)] = 2;
    }
    $form_state->set('num_items', $num_items);
    $form_state->setRebuild();
  }

  public function itemsSectionRemoveOneSubmit(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $num_items = $form_state->get('num_items');
    if (isset($triggering_element['#i'])) {
      $i = $triggering_element['#i'];
      $num_items[$i] = $num_items[$i] - 1;
    }
    else {
      unset($num_items[count($num_items) - 1]);
    }
    $form_state->set('num_items', $num_items);
    $form_state->setRebuild();
  }


  /**
   * Form element validation handler.
   */
  public static function validateLink(&$element, FormStateInterface $form_state, &$complete_form) {
    $uri = $element['#value'];
    if (!in_array($uri[0], ['/', '?', '#'], TRUE) && substr($uri, 0, 7) !== '<front>') {
      $form_state->setError($element, t('Manually entered paths should start with /, ? or #.'));
      return;
    }
  }



}
