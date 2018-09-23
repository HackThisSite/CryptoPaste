<?php

namespace AppBundle\Twig;

use Twig\Environment;
use Symfony\Component\Yaml\Yaml;

class TwigHelper {

  private $twig;

  public function __construct(Environment $twig) {
    $this->twig = $twig;
  }

  public function getViewPath($view_file) {
    $page_path = null;
    $twig = $this->twig->getLoader();
    if ($twig->exists('custom/'.$view_file)) {
      $page_path = 'custom/'.$view_file;
    } elseif ($twig->exists('default/'.$view_file)) {
      $page_path = 'default/'.$view_file;
    }
    return $page_path;
  }

  public function getPages() {
    $pages = null;
    foreach (array('yaml','yml') as $ext) {
      $path = $this->getViewPath(sprintf('_menu.%s.twig', $ext));
      if (!empty($path)) {
        $pages = Yaml::parse($this->twig->getLoader()->getSourceContext($path)->getCode());
      }
    }
    return (!empty($pages) && array_key_exists('menu', $pages) ? $pages['menu'] : array());
  }

}

// EOF
