<?php

namespace Drupal\redemption_codes\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Supports validating comment author names.
 *
 * @Constraint(
 *   id = "RedemptionCodeValue",
 *   label = @Translation("Unique Redemption Code", context = "Validation"),
 *   type = "entity:redemption_code"
 * )
 */
class RedemptionCodeValueConstraint extends CompositeConstraintBase {

  /**
   * Message shown when a code is already in the system.
   *
   * @var string
   */
  public $notUnique = 'The redemption code you used (%code) already exists in the system.';

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['code'];
  }

}
