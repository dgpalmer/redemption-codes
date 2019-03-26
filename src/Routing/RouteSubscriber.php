<?php

namespace Drupal\redemption_codes\Routing;

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
    $config = \Drupal::config('redemption_codes.settings');

    if ($route = $collection->get('entity.redemption.add_form')) {
      $redemptions = $config->get('redemptions');
      $route->setOption('_admin_route', FALSE);
      $route->setPath($redemptions['default_path']);
      $route->setDefault('_title', 'Redeem your code');
      // unset the _title_callback; alternatively you could override it here
      $route->setDefault('_title_callback', '');
    }


    /**
     * Alter the Registration Flow Route Path
     */
    if ($route = $collection->get('redemption_codes.user.register')) {
      $registration_flow = $config->get('registration_flow');

      $route->setPath($registration_flow['path']);
      if ($registration_flow['enabled']) {
        $route->setRequirement('_access', 'TRUE');
      } else {
        $route->setRequirement('_access', 'FALSE');
      }
    }

    /**
     * Alter the Pre Registration Flow Route Path
     */
    if ($route = $collection->get('redemption_codes.user.pre_register')) {
      $pre_registration_flow = $config->get('pre_registration_flow');

      $route->setPath($pre_registration_flow['path']);
      if ($pre_registration_flow['enabled']) {
        $route->setRequirement('_access', 'TRUE');
      } else {
        $route->setRequirement('_access', 'FALSE');
      }
    }
  }
}
