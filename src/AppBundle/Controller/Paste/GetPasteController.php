<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;

class GetPasteController extends Controller {

  /**
   * Fetch an encrypted paste. Used for one-time-read pastes to prevent social
   * media or other mobile app preloads from killing a paste.
   *
   * @Route("/get_paste.json", name="get_paste")
   */
  public function processAction(Request $request, TwigHelper $view) {
    return new Response();
  }

}

// EOF
