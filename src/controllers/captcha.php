<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


//
// GET /captcha.{format} - CAPTCHA files
//
$app->get('/captcha.{_format}', function (Request $req) use ($app) {

  $format = $req->getRequestFormat();

  $mime = '';

  // Image
  if (in_array($format, array('jpg', 'gif', 'png'))) {

    switch ($format) {
      case 'jpg':
        $mime = 'image/jpeg';
        $image_type = Securimage::SI_IMAGE_JPEG;
        break;
      case 'gif':
        $mime = 'image/gif';
        $image_type = Securimage::SI_IMAGE_GIF;
        break;
      case 'png':
        $mime = 'image/png';
        $image_type = Securimage::SI_IMAGE_PNG;
        break;
    }

    // Define and configure CAPTCHA object
    $captcha = new Securimage(array(
      'send_headers'                 => false,
      'no_exit'                      => true,
      'expiry_time'                  => 600,
      'image_type'                   => $image_type,
      'use_database'                 => false,
      'code_length'                  => 7,
      'perturbation'                 => 0.75,
      'use_transparent_text'         => true,
      'text_transparency_percentage' => 25,
      'text_color'                   => new Securimage_Color("#555555"),
      'line_color'                   => new Securimage_Color("#BBBBBB"),
      'num_lines'                    => rand(5, 7),
      'charset'                      => 'ABCDEFGHKMNPRSTUVWYZabcdefghkmnprstuvwyz23456789',
    ));
    $captcha->image_height = 90;
    $captcha->image_width  = ($captcha->image_height + 20) * M_E;

    // Capture CAPTCHA raw image data
    $stream = function() use ($captcha) {
      $captcha->show($captcha->securimage_path.'/backgrounds/bg3.jpg');
    };

  // Audio
  } else {

    // Set MIME type
    switch ($format) {
      case 'wav':
        $mime = 'audio/wav';
        break;
      case 'mp3':
        $mime = 'audio/mpeg';
        break;
    }

    // Define and configure CAPTCHA object
    $captcha = new Securimage(array(
      'send_headers'    => false,
      'no_exit'         => true,
      'expiry_time'     => 600,
      'use_database'    => false,
      'code_length'     => 7,
      'audio_use_noise' => true,
      'degrade_audio'   => false,
      'audio_gap_min'   => 10,
      'audio_gap_max'   => 700,
      'charset'         => 'ABCDEFGHKMNPRSTUVWYZabcdefghkmnprstuvwyz23456789',
    ));

    // Set LAME path
    if ($format == 'mp3') {
      Securimage::$lame_binary_path = '/usr/local/bin/lame';
    }

    // Capture CAPTCHA audio raw data
    $stream = function() use ($captcha, $format) {
      $captcha->outputAudioFile($format == 'mp3' ? 'mp3' : null);
    };

    // Set extra headers
    $uniq = random_int(10000, 10000000);
    $headers_extra = array(
      'Accept-Ranges'       => 'bytes',
      'Content-Disposition' => sprintf('attachment; filename="captcha-%s.%s"', $uniq, $format),
    );

  }

  // Set headers
  $headers = array(
    'Expires'       => 'Mon, 26 Jul 1997 05:00:00 GMT',
    'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
    'Cache-Control' => 'no-store, no-cache, must-revalidate',
    'Pragma'        => 'no-cache',
    'Content-Type'  => $mime,
  );
  if (isset($headers_extra)) {
    $headers = array_merge($headers, $headers_extra);
  }

  // Return stream
  return $app->stream($stream, 200, $headers);

})
->bind('captcha')
->assert('_format', 'jpg|gif|png|wav|mp3');


//### EOF
