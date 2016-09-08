<?php

namespace Drupal\mma_contact\Form;

use Drupal\contact\ContactFormInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\marketo_ma\MarketoFieldDefinition;
use Drupal\marketo_ma\MarketoMaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a configuration form per contact_form instance.
 *
 * This form adds:
 *
 * - A toggle to enable marketo support for this specific form
 * - Provide a way to provide field mapping between contact fields and marketo
 *   ones.
 */
class MmaContactConfiguration extends FormBase {

  /**
   * The contact form entity.
   *
   * @todo Could we also implement an entity form instead?
   *
   * @var \Drupal\contact\ContactFormInterface
   */
  protected $contactForm;

  /** @var \Drupal\marketo_ma\MarketoMaServiceInterface */
  protected $service;

  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface */
  protected $entityFieldManager;

  /**
   * Creates a new MmaContactConfiguration instance.
   *
   * @param \Drupal\marketo_ma\MarketoMaServiceInterface
   *   The marketo ma service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   */
  public function __construct(MarketoMaServiceInterface $service, EntityFieldManagerInterface $entityFieldManager) {
    $this->service = $service;
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('marketo_ma'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mma_contact_configuration';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContactFormInterface $contact_form = NULL) {
    $this->contactForm = $contact_form;

    $mapping = $this->contactForm->getThirdPartySetting('mma_contact', 'mapping');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable tracking'),
      '#description' => $this->t('Set to "Enable tracking" to turn on tracking for this contact form.'),
      '#default_value' => $this->contactForm->getThirdPartySetting('mma_contact', 'enabled'),
    ];

    $form['mapping'] = [
      '#type' => 'table',
      '#header' => [
        'title' => $this->t('Title'),
        'tracking' => $this->t('Tracking enabled'),
        'mapping' => $this->t('Components mapping'),
      ],
    ];

    $marketo_field_options = ['' => $this->t('None')] + $this->getMarketoFields();
    foreach ($this->getContactFields() as $field_name => $label) {
      $form['mapping'][$field_name] = [
        'title' => ['#markup' => $label],
        'tracking' => ['#markup' => ''],
        'mapping' => [
          '#type' => 'select',
          '#title' => $this->t('Select mapped component'),
          '#title_display' => 'hidden',
          '#options' => $marketo_field_options,
          '#default_value' => isset($mapping[$field_name]) ? $mapping[$field_name] : FALSE,
        ],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Returns all available contact form field labels, keyed by machine name.
   *
   * @return string[]
   */
  protected function getContactFields() {
    $fields = $this->entityFieldManager->getFieldDefinitions('contact_message', $this->contactForm->id());
    return array_map(function (FieldDefinitionInterface $field_definition) {
      return $field_definition->getLabel();
    }, $fields);
  }

  /**
   * Returns the available marketo field labels, keyed by machine name.
   *
   * @return string[]
   */
  protected function getMarketoFields() {
    return array_map(function ($field) {
      return $field instanceof MarketoFieldDefinition ? $field->getDisplayName() : '';
    }, $this->service->getEnabledFields());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set the enabled value.
    $this->contactForm->setThirdPartySetting('mma_contact', 'enabled', $form_state->getValue('enabled'));

    // Get the mapping values.
    $mapping = array_map(function ($form_value) {
      return $form_value['mapping'];
    }, $form_state->getValue('mapping'));
    // Remove any unassociated fields.
    $mapping = array_filter($mapping);

    // Set the third party settings for field mappings and save.
    $this->contactForm->setThirdPartySetting('mma_contact', 'mapping', $mapping);
    $this->contactForm->save();
  }

}
