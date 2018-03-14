<?php

namespace Drupal\marketo_ma_contact_block\Plugin\Block;

use Drupal\contact\MessageInterface;
use Drupal\contact_block\Plugin\Block\ContactBlock;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ContactBlock' block with additional field values.
 *
 * @Block(
 *  id = "marketo_ma_contact_block",
 *  admin_label = @Translation("Contact block (with additional values) "),
 * )
 */
class MarketoMaContactBlock extends ContactBlock {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, EntityFormBuilderInterface $entity_form_builder, RendererInterface $renderer, EntityFieldManagerInterface $entityFieldManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $config_factory, $entity_form_builder, $renderer);

    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('entity.form_builder'),
      $container->get('renderer'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = parent::defaultConfiguration();

    $defaults['contact_form'] = '';
    $defaults['fields'] = [];
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['contact_form']['#ajax'] = [
      'callback' => [$this, 'onContactFormChange'],
      'wrapper' => 'marketo_ma_contact_block',
      '#limit_validation_errors' => [],
    ];

    $form['fields'] = [
      '#prefix' => '<div id="marketo_ma_contact_block">',
      '#type' => 'container',
      '#suffix' => '</div>',
    ];

    if (($contact_form_id = $form_state->getCompleteFormState()->getValue(['settings', 'contact_form'])) || ($contact_form_id = $this->configuration['contact_form'])) {
      $form['fields']['#type'] = 'details';
      $form['fields']['#open'] = TRUE;

      $fields = $this->entityFieldManager->getFieldDefinitions('contact_message', $contact_form_id);
      // Exclude any base field, which excludes stuff like subject and body.
      $fields = array_filter($fields, function (FieldDefinitionInterface $fieldDefinition) {
        return !$fieldDefinition instanceof BaseFieldDefinition;
      });

      $form['fields'] += array_map(function (FieldDefinitionInterface $fieldDefinition) {
        $element = [
          '#type' => 'textfield',
          '#default_value' => isset($this->configuration['fields'][$fieldDefinition->getName()]) ? $this->configuration['fields'][$fieldDefinition->getName()] : '',
          '#title' => $fieldDefinition->getLabel(),
        ];
        return $element;
      }, $fields);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    if ($contact_form_id = $form_state->getValue(['contact_form'])) {
      $this->configuration['fields'] = $form_state->getValue(['fields']);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function createContactMessage() {
    if ($contact_message = parent::createContactMessage()) {
      // Loop through the fields.
      if(isset($this->configuration['fields'])){
        foreach ($this->configuration['fields'] as $field_name => $field_value) {
          // Make sure the field is currently available.
          if ($contact_message instanceof MessageInterface && ($field = $contact_message->getFieldDefinition($field_name))) {
            // Set the field value.
            $main_property = $field->getFieldStorageDefinition()->getMainPropertyName();
            $contact_message->get($field_name)->{$main_property} = $field_value;
          }
        }
      }
    }
    return $contact_message;
  }

}
