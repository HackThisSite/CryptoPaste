<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;

class ProcessController extends Controller {

  /**
   * Process an encrypted paste
   *
   * @Route("/process.json", name="process", methods="POST")
   */
  public function processAction(Request $request, TwigHelper $view) {
    return new Response();
  }

}

// EOF
