<?php

namespace Drupal\cmc_redhen_activity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Activity type entity.
 *
 * @ConfigEntityType(
 *   id = "cmc_redhen_activity_type",
 *   label = @Translation("Activity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmc_redhen_activity\ActivityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmc_redhen_activity\Form\ActivityTypeForm",
 *       "edit" = "Drupal\cmc_redhen_activity\Form\ActivityTypeForm",
 *       "delete" = "Drupal\cmc_redhen_activity\Form\ActivityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmc_redhen_activity\ActivityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmc_redhen_activity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "cmc_redhen_activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/cmc_redhen_activity_type/{cmc_redhen_activity_type}",
 *     "add-form" = "/admin/structure/cmc_redhen_activity_type/add",
 *     "edit-form" = "/admin/structure/cmc_redhen_activity_type/{cmc_redhen_activity_type}/edit",
 *     "delete-form" = "/admin/structure/cmc_redhen_activity_type/{cmc_redhen_activity_type}/delete",
 *     "collection" = "/admin/structure/cmc_redhen_activity_type"
 *   }
 * )
 */
class ActivityType extends ConfigEntityBundleBase implements ActivityTypeInterface {

  /**
   * The Activity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Activity type label.
   *
   * @var string
   */
  protected $label;

}
