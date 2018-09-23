<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CaptchaValidator extends ConstraintValidator {

  public function validate($value, Constraint $constraint) {
    if (empty($value)) {
     $this->context
      ->buildViolation('CAPTCHA cannot be blank')
      ->addViolation();
      return;
    }

    // Captcha options
    $captcha_opts = array(
      'captchaId'    => 'cryptopaste',
      'send_headers' => false,
      'no_exit'      => true,
      'expiry_time'  => 600,
      'use_database' => false,
      'no_session'   => false,
      'code_length'  => 7,
      'charset'      => 'ABCDEFGHKMNPRSTUVWYZabcdefghkmnprstuvwyz23456789',
    );

    $captcha = new \Securimage($captcha_opts);
    if ($captcha->check($value) == false) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('{{ string }}', $value)
        ->addViolation();
    }

  }

}

// EOF
