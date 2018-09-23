<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;
use AppBundle\Model\PasteModel;

class GetController extends Controller {

  /**
   * Fetch an encrypted paste
   *
   * @Route("/get.json", name="get")
   */
  public function getAction(Request $request, TwigHelper $view, PasteModel $pastes) {
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
    $data = array(
      'result'      => 'ok',
      'timestamp'   => $paste->getTimestamp(),
      'paste'       => $paste->getData(),
      'views'       => $views,
      'is_burnable' => $is_burnable,
    );
    if ($this->getParameter('show_paste_expiry')) {
      $data['expiry'] = $paste->getExpiry();
    }
    return new JsonResponse($data);
  }

}

// EOF
