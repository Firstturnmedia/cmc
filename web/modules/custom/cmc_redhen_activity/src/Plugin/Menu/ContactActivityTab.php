<?php

namespace Drupal\cmc_redhen_activity\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Menu\LocalActionInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides route parameters needed for redhen activity and notes tabs/local tasks
 */
class ContactActivityTab extends LocalTaskDefault {

  /**
   * Current contact id.
   */
  protected $currentContactId;

  /**
   * Gets the current contact id from path
   */
  protected function currentContactId() {
    // Retrieve an array which contains the path pieces.
    // @todo fix this to get the actual contact object
    $path = \Drupal::request()->getpathInfo();
    $arg  = explode('/',$path);
    $this->currentContactId = $arg[3];

    return $this->currentContactId;
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match) {
    //return ['contact' => 17];
    return ['contact' => $this->currentContactId()];
  }

}
