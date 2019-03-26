<?php

namespace Drupal\redemption_codes;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Redemption entity.
 *
 * @see \Drupal\redemption_codes\Entity\Redemption.
 */
class RedemptionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\redemption_codes\Entity\RedemptionInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'administer redemption codes');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'administer redemption codes');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'administer redemption codes');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'redeem redemption codes');
  }

}
