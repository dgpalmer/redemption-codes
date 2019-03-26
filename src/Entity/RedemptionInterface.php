<?php

namespace Drupal\redemption_codes\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Redemption entities.
 *
 * @ingroup redemption_codes
 */
interface RedemptionInterface extends  ContentEntityInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Redemption Code.
   *
   * @return integer
   *   Integer id of the Code.
   */
  public function getRedemptionCode();

  /**
   * Sets the Redemption Code.
   *
   * @param integer $code
   *   The Redemption Code id.
   *
   * @return RedemptionCode
   */
  public function setRedemptionCode($code);

  /**
   * Gets the Redemption Code.
   *
   * @return integer
   */
  public function getRedemptionCodeId();

  /**
   * Sets the Redemption Code.
   *
   * @param integer $id
   *   The Redemption Code id.
   *
   * @return RedemptionCode
   */
  public function setRedemptionCodeId($id);

  /**
   * Gets the Redemption Code.
   *
   * @return integer
   */
  public function _getRedemptionCodeId();

  /**
   * Sets the Redemption Code.
   *
   * @param integer $id
   * @return RedemptionCode
   */
  public function _setRedemptionCodeId($id);
  /**
   * Gets the Redemption creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Redemption.
   */
  public function getCreatedTime();

  /**
   * Sets the Redemption creation timestamp.
   *
   * @param int $timestamp
   *   The Redemption creation timestamp.
   *
   * @return \Drupal\redemption_codes\Entity\RedemptionInterface
   *   The called Redemption entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Redemption published status indicator.
   *
   * Unpublished Redemption are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Redemption is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Redemption.
   *
   * @param bool $published
   *   TRUE to set this Redemption to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\redemption_codes\Entity\RedemptionInterface
   *   The called Redemption entity.
   */
  public function setPublished($published);

}
