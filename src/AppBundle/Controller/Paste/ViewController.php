<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;

class ViewController extends Controller {

  /**
   * Gets an encrypted paste and renders the view_paste view
   *
   * @Route("/{paste_id}", name="view")
   */
  public function viewAction(Request $request, TwigHelper $view, $paste_id) {
    return new Response();
  }

}

// EOF
