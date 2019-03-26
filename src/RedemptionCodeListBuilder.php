<?php

namespace Drupal\redemption_codes;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\redemption_codes\Entity\RedemptionInterface;
use Drupal\redemption_codes\Entity\Redemption;

/**
 * Defines a class to build a listing of Code entities.
 *
 * @ingroup redemption_codes
 */
class RedemptionCodeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['code'] = $this->t('Code');
    $header['redeemed_by'] = $this->t('Redeemed By');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\redemption_codes\Entity\RedemptionCode */
    $row['id'] = $entity->id();
    $row['code'] = Link::createFromRoute(
      $entity->getCode(),
      'entity.redemption_code.edit_form',
      ['redemption_code' => $entity->id()]
    );
    $redemption = current($this->findRedemption($entity->id()));
    if (!empty($redemption)) {
      $_redemption = Redemption::load($redemption);
      $owner = $_redemption->getOwner();
      $row['redeemed_by'] = Link::createFromRoute(
        $owner->getAccountName(),
        'entity.user.canonical',
        ['user' => $owner->id()]
      );
    } else {
      $row['redeemed_by'] = 'Unclaimed';
    }

    return $row + parent::buildRow($entity);
  }

  private function findRedemption($id) {
    return \Drupal::entityQuery('redemption')
      ->condition('redemption_code_id',  $id)
      ->execute();
  }

}
