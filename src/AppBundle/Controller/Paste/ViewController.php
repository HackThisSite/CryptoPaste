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
   * Gets an encrypted paste and renders the view_paste view
   *
   * @Route("/{paste_id}", name="view")
   */
  public function viewAction(Request $request, TwigHelper $view, PasteModel $pastes, $paste_id) {
    // Get Paste object (volatile read, will delete burn-after-reading paste if applicable)
    $paste = $pastes->getPaste($paste_id);
    $is_burnable = ($pastes->getExpiry() === 0);

    // Paste not found
    if (empty($paste)) {
      return $this->render($view->getViewPath('message.html.twig'), array(
        'heading' => 'Paste Not Found',
        'message' => 'This paste has either expired or the ID is invalid.',
      ), 404);
    }

    // Handle other burn-after-reading and view counter stuff
    if ($is_burnable) {
      $burnnotice = 'This paste has been deleted from the database now that you have opened it.';
      $views = 1;
    } else {
      $burnnotice = '';
      $views = $pastes->incrementViewCounter($paste);
    }

    // Render paste
    return $this->render($view->getViewPath('view_paste.html.twig'), array(
      'timestamp'  => $paste->getTimestamp(),
      'paste'      => $paste->getData(),
      'views'      => $views,
      'expiry'	   => $paste->getExpiry(),
      'burnnotice' => $burnnotice,
    ));
  }

}

// EOF
