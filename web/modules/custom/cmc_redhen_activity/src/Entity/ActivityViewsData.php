<?php

namespace Drupal\cmc_redhen_activity\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Activity entities.
 */
class ActivityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
