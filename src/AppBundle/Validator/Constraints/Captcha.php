<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Captcha extends Constraint {
  public $message = 'The CAPTCHA value provided is incorrect';
}

// EOF
