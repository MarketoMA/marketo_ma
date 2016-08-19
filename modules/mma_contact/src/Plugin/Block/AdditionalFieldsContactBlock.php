<?php

namespace Drupal\mma_contact\Plugin\Block;

use Drupal\Component\Utility\NestedArray;
use Drupal\contact\Entity\Message;
use Drupal\contact\MessageInterface;
use Drupal\contact_block\Plugin\Block\ContactBlock;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'ContactBlock' block with additional field values.
 *
 * @Block(
 *  id = "mma_contact_block__additional_field_values",
 *  admin_label = @Translation("Contact block (with additional values) "),
 * )
 *
 * @todo config schema
 * @todo tests
 */
class AdditionalFieldsContactBlock extends ContactBlock {

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
      'wrapper' => 'mma_contact_block__additional_field_values',
    ];

    $form['fields'] = [
      '#prefix' => '<div id="mma_contact_block__additional_field_values">',
      '#type' => 'container',
      '#suffix' => '</div>',
    ];
    if (($contact_form_id = $form_state->getValue(['settings', 'contact_form'])) || ($contact_form_id = $this->configuration['contact_form'])) {
      $form['fields']['#type'] = 'details';
      $form['fields']['#open'] = TRUE;

      $contact_message = Message::create([
        'contact_form' => $contact_form_id,
      ]);
      $this->applyFieldValuesToEntity($contact_message, $this->configuration['fields']);
      $form_display = $this->getFormDisplay($contact_form_id);

      // We need to setup the right paths, so $form_display::extractFormValues
      // works in the submit function.
      $form['fields']['#parents'] = ['settings', 'fields'];
      $form_display->buildForm($contact_message, $form['fields'], $form_state);
    }

    return $form;
  }

  /**
   * Gets the contact message form display for a specific contact form.
   *
   * @param string $contact_form_id
   *   The contact form ID.
   *
   * @return \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   *   The entity form display.
   */
  protected function getFormDisplay($contact_form_id) {
    return entity_get_form_display('contact_message', $contact_form_id, 'block_form');
  }

  /**
   * Applies the preconfigured field values to the contact message entity.
   *
   * @param \Drupal\contact\MessageInterface $contact_message
   *   The contact message entity.
   * @param array $field_values
   *   The preconfigured field values.
   */
  protected function applyFieldValuesToEntity(MessageInterface $contact_message, array $field_values) {
    foreach ($this->configuration['fields'] as $field_name => $field_values) {
      $contact_message->get($field_name)->setValue($field_values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    if ($contact_form_id = $form_state->getValue(['contact_form'])) {
      $contact_message = Message::create([
        'contact_form' => $contact_form_id,
      ]);

      $form_display = $this->getFormDisplay($contact_form_id);
      // We need to setup the right paths, so $form_display::extractFormValues
      // works.
      $entity_form = $form['settings']['fields'];
      $entity_form['#parents'] = ['fields'];
      $extracted_fields = $form_display->extractFormValues($contact_message, $entity_form, $form_state);

      $pre_configured_field_values = [];
      foreach ($extracted_fields as $extract_field) {
        $field_value = $contact_message->{$extract_field}->getValue();
        $pre_configured_field_values[$extract_field] = $field_value;
      }

      $this->configuration['fields'] = $pre_configured_field_values;
    }
  }

  /**
   * Ajax callback when changing the contact form.
   *
   * @param array $form
   *   The build form.
   *
   * @return array
   *   The subpart of the form which should be rendered.
   */
  public function onContactFormChange($form) {
    return NestedArray::getValue($form, ['settings', 'fields']);
  }

  /**
   * Creates the contact message entity without saving it.
   *
   * @return \Drupal\contact\Entity\Message|null
   *   The contact message entity. NULL if the entity does not exist.
   */
  protected function createContactMessage() {
    if ($contact_message = parent::createContactMessage()) {
      $this->applyFieldValuesToEntity($contact_message, $this->configuration['fields']);
    }
    return $contact_message;
  }

}
