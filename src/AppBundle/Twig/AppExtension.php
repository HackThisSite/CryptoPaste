<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

class AppExtension extends AbstractExtension {

  public function getFilters() {
    return array(
      new TwigFilter('substr', 'substr'),
    );
  }

  public function getTests() {
    return array(
      new TwigTest('valid_email', array($this, 'is_email')),
    );
  }

  public function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL);
  }

}

// EOF
