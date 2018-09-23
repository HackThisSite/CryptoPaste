<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Twig\TwigHelper;
use AppBundle\Form\PasteFormGenerator;
use AppBundle\Model\PasteModel;

class ProcessController extends Controller {

  /**
   * Process an encrypted paste
   *
   * @Route("/process.json", name="process", methods="POST")
   */
  public function processAction(Request $request, TwigHelper $view, PasteFormGenerator $generator, PasteModel $pastes) {
    // Process form
    $form = $generator->generateForm();
    $form->submit($request->request->get($form->getName()));

    // Process paste if form is valid
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();

      // Set expiration
      $expiry = -1;
      $expire_txt = '';
      switch ($data->getExpiration()) {
        case 'once':
          $expiry = 0;
          $expire_txt = 'burn';
          break;
        case '10min':
          $expiry = (intval(gmdate('U')) + 600);
          break;
        case '1h':
          $expiry = (intval(gmdate('U')) + 3600);
          break;
        case '1d':
          $expiry = (intval(gmdate('U')) + 86400);
          break;
        case '1w':
          $expiry = (intval(gmdate('U')) + 604800);
          break;
        case '1m':
          $expiry = (intval(gmdate('U')) + 2592000);
          break;
        case '1y':
          $expiry = (intval(gmdate('U')) + 31536000);
          break;
        case 'never':
        default:
          $expiry = -1;
          $expire_txt = 'Never';
          break;
      }
      $expire_txt = (empty($expire_txt) ? gmdate('r', $expiry) : $expire_txt);

      // Get paste ID
      $paste_id = $pastes->createPaste($expiry, $data->getPaste());

      // Get URL for that ID
      $url = $this->get('router')->generate('view', array(
        'paste_id' => $paste_id,
      ), UrlGeneratorInterface::ABSOLUTE_URL);

      // Return JSON response
      return new JsonResponse(array(
        'status'  => 'ok',
        'url'     => $url,
        'expires' => $expire_txt,
      ));

    // Return an error
    } else {
      // Get errors
      $errors = array();
      foreach ($form->getErrors(true, true) as $error) {
        $errors[] = $error->getMessage();
      }

      // Rotate nonce
      $nonce = $this->get('security.csrf.token_manager')->refreshToken('paste')->getValue();

      // Return JSON error
      return new JsonResponse(array(
        'status'  => 'error',
        'message' => 'Please correct the following errors and try again: '.implode('; ', $errors),
        'nonce'   => $nonce,
      ), 400);
    }
  }

}

// EOF
