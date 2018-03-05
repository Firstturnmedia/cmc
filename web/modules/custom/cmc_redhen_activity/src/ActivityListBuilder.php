<?php

namespace Drupal\cmc_redhen_activity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Activity entities.
 *
 * @ingroup cmc_redhen_activity
 */
class ActivityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Activity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\cmc_redhen_activity\Entity\Activity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.cmc_redhen_activity.edit_form',
      ['cmc_redhen_activity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
