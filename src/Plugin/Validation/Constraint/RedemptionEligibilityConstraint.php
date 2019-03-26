<?php

namespace Drupal\redemption_codes\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
* Checks that the submitted code is eligible for redemption
*
* @Constraint(
*   id = "RedemptionEligibility",
*   label = @Translation("Redemption Eligibility Constraint", context = "Validation"),
* )
*/
class RedemptionEligibilityConstraint extends Constraint {
  // The message that will be shown if the code does not exist
  public $noExists = '%value is not a valid %type';

  // The message that will be shown if the code has been claimed by someone else
  public $taken = '%type has been claimed by someone else';

  // The message that will be shown if this code has already been claimed by this user
  public $ownerAlready = 'You have already redeemed this code.';

  // The message that will be shown if this user can claim the code
  public $eligble = 'This %type is eligible to be claimed.';
}
