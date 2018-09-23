<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CsrfToken extends Constraint {

  public $csrf_token_id;

  public $message = 'CSRF token invalid';

  public function getRequiredOptions() {
    return ['csrf_token_id'];
  }
}

// EOF
