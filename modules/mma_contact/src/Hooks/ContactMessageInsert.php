<?php

namespace Drupal\mma_contact\Hooks;

use Drupal\contact\MessageInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\marketo_ma\Lead;
use Drupal\marketo_ma\Service\MarketoMaServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContactMessageInsert implements ContainerInjectionInterface {

  /**
   * @var \Drupal\marketo_ma\Service\MarketoMaServiceInterface
   */
  protected $mma_service;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Stores the loaded mapping configuration.
   *
   * @var array|NULL
   */
  protected $mappingConfiguration;

  /**
   * Creates a new ContactMessageInsert instance.
   *
   * @param \Drupal\marketo_ma\Service\MarketoMaServiceInterface $mma_service
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(MarketoMaServiceInterface $mma_service, EntityTypeManagerInterface $entityTypeManager) {
    $this->mma_service = $mma_service;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('marketo_ma'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Implements hook_contact_message_insert().
   */
  public function contactMessageInsert(MessageInterface $message) {
    if ($tracking_enabled = $this->isTrackingEnabled($message->bundle())) {
      $data = $this->determineMappedData($message);
      $this->mma_service->updateLead(new Lead($data));
    }
  }

  /**
   * Loads the mapping configuration for a specific contact form.
   *
   * @param string $contact_form_id
   *   The contact form id.
   *
   * @return array
   */
  protected function loadMappingConfiguration($contact_form_id) {
    if (!isset($this->mappingConfiguration)) {
      /** @var \Drupal\contact\ContactFormInterface $contact_form */
      $contact_form = $this->entityTypeManager->getStorage('contact_form')->load($contact_form_id);
      $this->mappingConfiguration = $contact_form->getThirdPartySetting('mma_contact', 'mapping', []);
    }
    return $this->mappingConfiguration;
  }

  /**
   * Determines whether some marketo tracking is enabled.
   *
   * @param string $contact_form_id
   *   The contact form id.
   *
   * @return bool
   *   TRUE if marketo tracking is enable.d
   */
  protected function isTrackingEnabled($contact_form_id) {
    $contact_form = $this->entityTypeManager->getStorage('contact_form')->load($contact_form_id);
    return ($contact_form->getThirdPartySetting('mma_contact', 'enabled', 0) === 1
      && !empty($this->loadMappingConfiguration($contact_form_id)));
  }

  /**
   * Determines data mapping from the contact form to marketo fields.
   *
   * @param \Drupal\contact\MessageInterface $message
   *
   * @return array
   *  The mapping data, keyed by marketo field name.
   */
  protected function determineMappedData(MessageInterface $message) {
    $enables_fields = $this->mma_service->getEnabledFields();

    $mapping = $this->loadMappingConfiguration($message->bundle());
    $data = [];

    foreach ($mapping as $contact_field_name => $marketo_field_id) {
      // Make sure there is a value to set and the field is still enabled.
      if (($field_item = $message->get($contact_field_name)->first()) && isset($enables_fields[$marketo_field_id])) {
        // Get the field name.
        $field_name = $enables_fields[$marketo_field_id]->getFieldName($this->mma_service->trackingMethod());
        // Adds the field value to the mapped data.
        $data[$field_name] = $field_item->{$field_item->mainPropertyName()};
      }
    }
    return $data;
  }

}
