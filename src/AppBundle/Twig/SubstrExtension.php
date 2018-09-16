<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class SubstrExtension extends AbstractExtension {
  public function getFilters() {
    return array(
      new TwigFilter('substr', 'substr'),
    );
  }
}

// EOF
