<?php

namespace Drupal\redemption_codes\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\redemption_codes\CodeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the Code constraint.
 */
class RedemptionCodeValueConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {

    $code = $entity->getCode();

    $entity_type_id = $entity->getEntityTypeId();
    $value_taken = (bool) \Drupal::entityQuery($entity_type_id)
      ->condition('code',  $code)
      ->range(0, 1)
      ->count()
      ->execute();
    if ($value_taken) {
      $this->context->addViolation($constraint->notUnique, ['%code' => $code]);
    }
  }

}

