<?php

namespace Drupal\redemption_codes\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\user\UserInterface;

use Drupal\redemption_codes\Entity\RedemptionInterface;
use Drupal\redemption_codes\Entity\Redemption;
use Drupal\redemption_codes\Entity\RedemptionCode;
use Drupal\redemption_codes\Entity\RedemptionCodeInterface;
/**
* Validates the Redemption Eligibility constraint.
*/
class RedemptionEligibilityConstraintValidator extends ConstraintValidator {

  /**
  * {@inheritdoc}
  */
  public function validate($items, Constraint $constraint) {

    /** @var RedemptionInterface $redemptionInterface */
    $redemptionInterface = $items->getEntity();
    $type = 'code';
    $isOwner = FALSE;
    $taken = FALSE;
    foreach ($items as $item ) {
      $redemption_code = $item->value;
      if (!$redemption_code_ids = $this->exists($redemption_code)) {
        $this->context->addViolation($constraint->noExists, ['%value' => $redemption_code, '%type' => $type, 'error' => 'invalid']);
      } else {
        // loop through each redemption code to check its availability
        foreach ($redemption_code_ids as $redemption_code_id) {

          // Check if this redemption code is claimed
          if ($redemption = $this->isClaimed($redemption_code_id)) {

            // Load the claimed redemption entity to find its owner
            $redemption = current($redemption);
            $_redemption = Redemption::load($redemption);
            $owner = $_redemption->getOwner();

            // Grab the prospective owner of the new redemption
            $user = $redemptionInterface->getOwner();

            // Check if the prospective owner is the owner of an existing redemption of this code
            if ($isOwner = $this->isUserOwner($owner, $user)) {
              $isOwner = TRUE;
            } else {
              $taken = TRUE;
            }
          } else {
            // This redemption code is eliglbe, save the id of the code into the redemption entity
            $redemptionInterface->setRedemptionCodeId($redemption_code_id);

            $isOwner = FALSE;
            $taken = FALSE;
          }
        }
      }
      if ($isOwner) {
        $this->context->addViolation($constraint->ownerAlready, ['%value' => $redemption_code, '%type' => $type, 'error' => 'ownerAlready']);

        // If the redemption code was already claimed by someone else
      } else if ($taken) {
        $this->context->addViolation($constraint->taken, ['%value' => $redemption_code, '%type' => $type, 'error' => 'taken']);
      }
    }
  }

  // Check if matching redemption code(s) exists
  private function exists($redemption_code) {
    return \Drupal::entityQuery('redemption_code')
      ->condition('code',  $redemption_code)
      ->execute();
  }

  // Check if the matching redemption code has been claimed
  private function isClaimed($redemption_code_id) {
    return \Drupal::entityQuery('redemption')
      ->condition('redemption_code_id',  $redemption_code_id)
      ->execute();
  }

  // Check if the Prospective Owner is an Existing Owner
  private function isUserOwner($owner, $user) {
    if ($owner->uid->value == $user->uid->value) {
      $isOwner = TRUE;
    } else {
      $isOwner = FALSE;
    }
    return $isOwner;
  }

}
