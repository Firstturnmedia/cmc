<?php

namespace Drupal\cmc_redhen\Theme;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Routing\RouteMatchInterface;

class ThemeNegotiator implements ThemeNegotiatorInterface {
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $debug = $route_match->getRouteName();
    // Force admin theme for redhen contact detail pgs.
    $possible_routes = [
      'entity.redhen_contact.canonical',
      'cmc_mailchimp.contact_report'
    ];

    return (in_array($route_match->getRouteName(), $possible_routes));
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Load the config to get the current admin theme.
    $config = \Drupal::config('system.theme');
    // Get the admin theme name
    $admin_theme = $config->get('admin');

    // Return the theme name
    return $admin_theme;
  }

}
