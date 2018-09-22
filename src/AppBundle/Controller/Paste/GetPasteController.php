<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;
use AppBundle\Model\PasteModel;

class GetPasteController extends Controller {

  /**
   * Fetch an encrypted paste. Used for burn-after-reading pastes to prevent social
   * media or other mobile app preloads from burning a paste.
   *
   * @Route("/get_paste.json", name="get_paste")
   */
  public function processAction(Request $request, TwigHelper $view, PasteModel $pastes) {
    // Get paste ID
    $paste_id = $request->query->get('id');
    if (empty($paste_id)) {
      return new JsonResponse(array(
        'result'  => 'error',
        'message' => 'Query string parameter "id" must be set.',
      ), 400);
    }

    // Get Paste object (volatile read, will delete burn-after-reading paste if applicable)
    $paste = $pastes->getPaste($paste_id);

    // Paste not found
    if (empty($paste)) {
      return new JsonResponse(array(
        'result'  => 'error',
        'message' => 'This paste has either expired or the ID is invalid.',
      ), 404);
    }

    // Set burn flag and view count
    $is_burnable = ($paste->getExpiry() === 0);
    $views = ($is_burnable ? 1 : $pastes->incrementViewCounter($paste));

    // Return paste data
    return new JsonResponse(array(
      'timestamp' => $paste->getTimestamp(),
      'paste'     => $paste->getData(),
      'expiry'	  => $paste->getExpiry(),
      'views'     => $views,
      'burned'    => $is_burnable,
    ));
  }

}

// EOF
