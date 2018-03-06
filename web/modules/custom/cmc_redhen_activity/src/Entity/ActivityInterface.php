<?php

namespace Drupal\cmc_redhen_activity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Activity entities.
 *
 * @ingroup cmc_redhen_activity
 */
interface ActivityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here
  /**
   * Gets the Activity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Activity.
   */
  public function getCreatedTime();

  /**
   * Sets the Activity creation timestamp.
   *
   * @param int $timestamp
   *   The Activity creation timestamp.
   *
   * @return \Drupal\cmc_redhen_activity\Entity\ActivityInterface
   *   The called Activity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Activity published status indicator.
   *
   * Unpublished Activity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Activity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Activity.
   *
   * @param bool $published
   *   TRUE to set this Activity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\cmc_redhen_activity\Entity\ActivityInterface
   *   The called Activity entity.
   */
  public function setPublished($published);

  /**
   * Retrieve the message arguments.
   *
   * @return array
   *   The arguments of the message.
   */
  public function getArguments();

  /**
   * Set the arguments of the message.
   *
   * @param array $values
   * Array of arguments.
   *
   * @code
   *   $values = [
   *     '@name_without_callback' => 'John doe',
   *     '@name_with_callback' => [
   *       'callback' => 'User::load',
   *       'arguments' => [1],
   *     ],
   *   ];
   * @endcode
   *
   * @return \Drupal\message\MessageInterface
   *   Returns the message object.
   */
  public function setArguments(array $values);

  /**
   * {@inheritdoc}
   */
  public function setContactId($contact_id);

  /**
   * {@inheritdoc}
   */
  public function getContactId();
}
