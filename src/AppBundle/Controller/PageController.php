<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;

class PageController extends Controller {

  /**
   * Renders a static page
   *
   * @Route(
   *     "/p/{page}",
   *     name="page",
   *     requirements={
   *         "page": "[^_][a-zA-Z09\.\-_]+"
   *     }
   * )
   */
  public function pageAction(Request $request, TwigHelper $view, $page) {
    $page_path = $view->getViewPath(sprintf('pages/%s.html.twig', $page));
    if (empty($page_path)) {
      throw $this->createNotFoundException('Page not found');
    }
    return $this->render($page_path, array(
      'contact' => 'foobar',
    ));
  }

}

// EOF
