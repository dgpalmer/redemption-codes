<?php

namespace Drupal\redemption_codes;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Redemption entities.
 *
 * @ingroup redemption_codes
 */
class RedemptionListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['code'] = $this->t('Redemption Code');
    $header['owner'] = $this->t('Owner');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\redemption_codes\Entity\Redemption */
    $row['id'] = $entity->id();
    $owner = $entity->getOwner();
    $code = $entity->getRedemptionCode();
    $row['code'] = $code;
    $row['owner'] = Link::createFromRoute(
      $owner->getAccountName(),
      'entity.user.canonical',
      ['user' => $owner->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
