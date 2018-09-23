<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\TwigTest;
use AppBundle\Model\PasteModel;

class AppExtension extends AbstractExtension {

  private $pastes;

  public function __construct(PasteModel $pastes) {
    $this->pastes = $pastes;
  }

  public function getFunctions() {
    $pastes = $this->pastes;
    return array(
      new TwigFunction('total_pastes', function() use ($pastes) {
        return $pastes->getTotalPastes();
      }),
    );
  }

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
