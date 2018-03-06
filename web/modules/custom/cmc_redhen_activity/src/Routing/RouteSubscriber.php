<?php

namespace Drupal\cmc_redhen_activity\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Force admin theme for Redhen activity views
    // Activity per contact view
    if ($route = $collection->get('view.redhen_activity.contact_activity')) {
      $route->setOption('_admin_route', TRUE);
    }

    // Notes per contact view
    if ($route = $collection->get('view.cmc_redhen_activity.contact_notes')) {
      $route->setOption('_admin_route', TRUE);
    }
  }

}
