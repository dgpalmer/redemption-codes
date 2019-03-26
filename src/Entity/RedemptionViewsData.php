<?php

namespace Drupal\redemption_codes\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Redemption entities.
 */
class RedemptionViewsData extends EntityViewsData {

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
