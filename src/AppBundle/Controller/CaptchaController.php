<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaptchaController extends Controller {

  /**
   * Renders a SecurImage captcha image or audio file
   *
   * @Route(
   *     "/captcha.{_format}",
   *     name="captcha",
   *     defaults={"_format": "png"},
   *     requirements={
   *         "_format": "jpg|gif|png|wav|mp3"
   *     }
   * )
   */
  public function renderAction(Request $request, $_format) {
    $format = $_format;

    // Get format and set MIME type
    $si_image_type = '';
    switch ($format) {
      case 'jpg':
        $mime = 'image/jpeg';
        $si_image_type = \Securimage::SI_IMAGE_JPEG;
        break;
      case 'gif':
        $mime = 'image/gif';
        $si_image_type = \Securimage::SI_IMAGE_GIF;
        break;
      case 'png':
        $mime = 'image/png';
        $si_image_type = \Securimage::SI_IMAGE_PNG;
        break;
      case 'wav':
        $mime = 'audio/wav';
        break;
      case 'mp3':
        $mime = 'audio/mpeg';
        break;
    }

    // Setup response stream object and headers
    $response = new StreamedResponse();
    $response->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
    $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s').' GMT');
    $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Content-Type', $mime);

    // Captcha options
    $captcha_opts = array(
      'captchaId'    => 'cryptopaste',
      'send_headers' => false,
      'no_exit'      => true,
      'expiry_time'  => 600,
      'use_database' => false,
      'code_length'  => 7,
      'charset'      => 'ABCDEFGHKMNPRSTUVWYZabcdefghkmnprstuvwyz23456789',
    );

    // Generate an image captcha
    if (in_array($format, array('jpg', 'gif', 'png'))) {
      // Configure and define Captcha object
      $captcha_opts['image_type'] = $si_image_type;
      $captcha_opts['image_width'] = 235;
      $captcha = new \Securimage($captcha_opts);
      $captcha->createCode();

      // Set Captcha callback
      $response->setCallback(function () use ($captcha) {
        $captcha->show();
      });
    }

    // Generate an audio captcha
    if (in_array($format, array('wav', 'mp3'))) {

      // Set extra headers
      $uniq = bin2hex(random_bytes(6));
      $response->headers->set('Accept-Ranges', 'bytes');
      $response->headers->set('Content-Disposition', sprintf('attachment; filename="captcha-%s.%s"', $uniq, $format));

      // Define Captcha object
      $captcha = new \Securimage($captcha_opts);

      // Set LAME path
      if ($format == 'mp3') {
        \Securimage::$lame_binary_path = '/usr/bin/lame';
      }

      // Set Captcha callback
      $response->setCallback(function () use ($captcha, $format) {
        $captcha->outputAudioFile($format == 'mp3' ? 'mp3' : null);
      });
    }

    // Return stream
    return $response->send();
  }

}

// EOF
