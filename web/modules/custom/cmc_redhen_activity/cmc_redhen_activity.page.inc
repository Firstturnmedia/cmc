<?php

/**
 * @file
 * Contains cmc_redhen_activity.page.inc.
 *
 * Page callback for Activity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Activity templates.
 *
 * Default template: cmc_redhen_activity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_cmc_redhen_activity(array &$variables) {
  // Fetch Activity Entity Object.
  $cmc_redhen_activity = $variables['elements']['#cmc_redhen_activity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
