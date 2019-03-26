<?php

/**
 * @file
 * Contains code.page.inc.
 *
 * Page callback for Code entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Redemption Code templates.
 *
 * Default template: redemption_code.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_redemption_code(array &$variables) {
  // Fetch Redemption Code Entity Object.
  $redemption_code = $variables['elements']['#redemption_code'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
