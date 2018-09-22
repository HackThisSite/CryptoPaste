<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfTokenValidator extends ConstraintValidator {

  private $csrf;

  public function __construct(CsrfTokenManagerInterface $csrf) {
    $this->csrf = $csrf;
  }

  public function validate($value, Constraint $constraint) {
    if (!$this->csrf->isTokenValid(new CsrfToken($constraint->csrf_token_id, $value))) {
      $this->context
        ->buildViolation($constraint->message)
        ->addViolation();
    }
  }

}

// EOF
