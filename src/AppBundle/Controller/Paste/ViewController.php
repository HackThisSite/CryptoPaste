<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;
use AppBundle\Model\PasteModel;

class ViewController extends Controller {

  /**
   * Render the view_paste page
   *
   * @Route("/{paste_id}", name="view")
   */
  public function viewAction(Request $request, TwigHelper $view, PasteModel $pastes, $paste_id) {
    // Get Paste object for headers (non-volatile read)
    $paste = $pastes->getPaste($paste_id, false);

    // Paste not found
    if (empty($paste)) {
      return new Response($this->renderView($view->getViewPath('message.html.twig'), array(
        'heading' => 'Paste Not Found',
        'message' => 'This paste has either expired or the ID is invalid.',
      )), 404);
    }

    // Render view_paste
    return $this->render($view->getViewPath('view_paste.html.twig'), array(
      'paste_id'      => $paste_id,
      'is_burnable'   => ($paste->getExpiry() === 0),
      'show_expiry'   => $this->getParameter('show_paste_expiry'),
    ));
  }

}

// EOF
