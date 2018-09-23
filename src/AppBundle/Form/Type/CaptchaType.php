<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CaptchaType extends AbstractType {

  public function getParent() {
    return TextType::class;
  }

}

// EOF
