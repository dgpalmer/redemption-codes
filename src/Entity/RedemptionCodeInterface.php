<?php

namespace Drupal\redemption_codes\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Code entities.
 *
 * @ingroup redemption_codes
 */
interface RedemptionCodeInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Code.
   *
   * @return string
   *   String of the Code.
   */
  public function getCode();

  /**
   * Sets the Code.
   *
   * @param string $code
   *   The Code string.
   *
   * @return \Drupal\redemption_codes\Entity\RedemptionCodeInterface
   *   The called Code entity.
   */
  public function setCode($code);

  /**
   * Gets the redemption action for this code
   *
   * @return \Drupal\system\Entity\Action
   *   Redemption Action Entity
   */
  public function getRedemptionActions();

  /**
   * Sets the Redeemed By Reference.
   *
   * @param
   *
   * @return \Drupal\system\Entity\Action
   *   The called Code entity.
   */
  public function setRedemptionActions($redemption_actions);

  /**
   * Gets the Code creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Code.
   */
  public function getCreatedTime();

  /**
   * Sets the Code creation timestamp.
   *
   * @param int $timestamp
   *   The Code creation timestamp.
   *
   * @return \Drupal\redemption_codes\Entity\RedemptionCodeInterface
   *   The called Code entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Code published status indicator.
   *
   * Unpublished Code are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Code is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Code.
   *
   * @param bool $published
   *   TRUE to set this Code to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\redemption_codes\Entity\RedemptionCodeInterface
   *   The called Code entity.
   */
  public function setPublished($published);

}
