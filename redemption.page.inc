<?php

/**
 * @file
 * Contains redemption.page.inc.
 *
 * Page callback for Redemption entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Redemption templates.
 *
 * Default template: redemption.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_redemption(array &$variables) {
  // Fetch Redemption Entity Object.
  $redemption = $variables['elements']['#redemption'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
