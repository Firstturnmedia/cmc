<?php

namespace Drupal\cmc_redhen_activity\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Activity entity.
 *
 * @ingroup cmc_redhen_activity
 *
 * @ContentEntityType(
 *   id = "cmc_redhen_activity",
 *   label = @Translation("Activity"),
 *   bundle_label = @Translation("Activity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmc_redhen_activity\ActivityListBuilder",
 *     "views_data" = "Drupal\cmc_redhen_activity\Entity\ActivityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\cmc_redhen_activity\Form\ActivityForm",
 *       "add" = "Drupal\cmc_redhen_activity\Form\ActivityForm",
 *       "edit" = "Drupal\cmc_redhen_activity\Form\ActivityForm",
 *       "delete" = "Drupal\cmc_redhen_activity\Form\ActivityDeleteForm",
 *     },
 *     "access" = "Drupal\cmc_redhen_activity\ActivityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\cmc_redhen_activity\ActivityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "cmc_redhen_activity",
 *   admin_permission = "administer activity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cmc_redhen_activity/{cmc_redhen_activity}",
 *     "add-page" = "/admin/structure/cmc_redhen_activity/add",
 *     "add-form" = "/admin/structure/cmc_redhen_activity/add/{cmc_redhen_activity_type}",
 *     "edit-form" = "/admin/structure/cmc_redhen_activity/{cmc_redhen_activity}/edit",
 *     "delete-form" = "/admin/structure/cmc_redhen_activity/{cmc_redhen_activity}/delete",
 *     "collection" = "/admin/redhen/activity",
 *   },
 *   bundle_entity_type = "cmc_redhen_activity_type",
 *   field_ui_base_route = "entity.cmc_redhen_activity_type.edit_form"
 * )
 */
class Activity extends ContentEntityBase implements ActivityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  // CUSTOM
  /**
   * {@inheritdoc}
   */
  public function getArguments() {
    $arguments = $this->get('arguments')->getValue();

    // @todo: See if there is a easier way to get only the 0 key.
    return $arguments ? $arguments[0] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function setArguments(array $values) {
    $this->set('arguments', $values);
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getContactId() {
    return $this->get('contact_id');
  }

  /**
   * {@inheritdoc}
   */
  public function setContactId($contact_id) {
    $this->set('contact_id', $contact_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Activity entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Activity is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    // Add arguments base field (property)
    $fields['arguments'] = BaseFieldDefinition::create('map')
      ->setLabel(t('Arguments'))
      ->setDescription(t('Holds the arguments of the message in serialise format.'));

    // Add contact id base field (property)
    $fields['contact_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Redhen Contact'))
      ->setDescription(t('Redhen Contact referenced by this activity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'redhen_contact')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        // @todo this might be wrong?
        //'type' => 'redhen_contact',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
