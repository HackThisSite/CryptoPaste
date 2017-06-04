<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

//
// GET / - New paste
//
$app->get('/', function (Request $req) use ($app) {
  // Generate CSRF nonce token
  $nonce = $app['csrf.token_manager']->refreshToken('paste');
  $app['monolog']->debug('Nonce(paste) set to: '.$nonce);

  return $app['twig']->render('new_paste.twig', array(
    'nonce' => $nonce,
  ));
})->bind('new');


//
// POST /process - Process paste
//
$app->post('/process.json', function (Request $req) use ($app) {
  // Nuke password contents if we accidentally received any
  if ($req->get('password')) $req->remove('password');

  // Validate request type
  if(!$req->isXmlHttpRequest()) {
    return paste_error($app, 'Invalid request type.');
  }

  // Set form variables
  $nonce      = $req->get('nonce');
  $paste      = $req->get('paste');
  $expiration = $req->get('expiry');
  $captcha    = $req->get('captcha');

  // Validation

  // - Nonce
  if (empty($nonce) || !$app['csrf.token_manager']->isTokenValid(new CsrfToken('paste', $nonce))) {
    $app['monolog']->debug('Nonce(paste) invalid: '.$nonce);
    return paste_error($app, 'Invalid nonce, please refresh the page and try again.');
  }

  // - CAPTCHA
  if (empty($captcha)) {
    return paste_error($app, 'CAPTCHA cannot be blank.');
  }
  $securimage = new Securimage();
  if ($securimage->check($captcha) == false) {
    return paste_error($app, 'CAPTCHA is invalid.', 403);
  }

  // - Expiration
  if (empty($expiration)) {
    return paste_error($app, 'Expiration cannot be blank.');
  }

  // - Paste
  if (empty($paste) || !preg_match('#^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$#', $paste)) {
    return paste_error($app, 'Encrypted paste appears invalid.');
  }

  // End Validation

  // Set expiry value for database
  $expiry = -1;
  switch ($expiration) {
    case 'once':
      $expiry = 0;
      break;
    case '10min':
      $expiry = gmdate('U') + 600;
      break;
    case '1h':
      $expiry = gmdate('U') + 3600;
      break;
    case '1d':
      $expiry = gmdate('U') + 86400;
      break;
    case '1w':
      $expiry = gmdate('U') + 604800;
      break;
    case '1m':
      $expiry = gmdate('U') + 18144000;
      break;
    case '1y':
      $expiry = gmdate('U') + 6622560000;
      break;
    case 'never':
    default:
      $expiry = -1;
      break;
  }

  // Do database insert and get ID
  $prefix = (!empty($app['config']['db']['table_prefix']) ? $app['config']['db']['table_prefix'] : '');
  $insert = $app['db']->insert($prefix.'cryptopaste', array(
    'timestamp' => gmdate('U'),
    'expiry'    => $expiry,
    'data'      => $paste,
  ));
  if (!$insert) {
    return paste_error($app, 'Error inserting into the database. Please contact the owner of this CryptoPaste.', 500);
  }
  $row_id = $app['db']->lastInsertId();
  $hashid = $app['hashids']->encode($row_id);

  // Generate URL
  $url = $app['url_generator']->generate('view', array('paste_id' => $hashid), UrlGeneratorInterface::ABSOLUTE_URL);

  // Return OK result
  return $app->json(array(
    'status'  => 'ok',
    'url'     => $url,
    'expires' => ($expiry > 0 ? gmdate('r', $expiry) : 'Never'),
  ));
})->bind('process');


//
// GET /[a-zA-Z0-9] - View paste
//
$app->get('/{paste_id}', function (Request $req, $paste_id) use ($app) {

  // Get database integer ID from hash
  $hashids = new Hashids\Hashids($app['config']['hashids']['salt'], $app['config']['hashids']['length']);
  $pid = $hashids->decode($paste_id);

  // Malformed hash
  if (empty($pid)) {
    return $app['twig']->render('message.twig', array(
      'heading' => 'Paste Not Found',
      'message' => 'This paste has either expired or the ID is invalid.',
    ));
  }

  // Fetch paste from database
  $prefix = (!empty($app['config']['db']['table_prefix']) ? $app['config']['db']['table_prefix'] : '');
  $data = $app['db']->fetchAssoc('SELECT * FROM '.$prefix.'cryptopaste WHERE `id` = ? AND (`expiry` IN (-1, 0) OR UNIX_TIMESTAMP(NOW()) < `expiry`)', array($pid[0]));

  // Nothing found
  if (empty($data)) {
    return $app['twig']->render('message.twig', array(
      'heading' => 'Paste Not Found',
      'message' => 'This paste has either expired or the ID is invalid.',
    ));
  }

  // "Burn After Reading"
  $burnnotice = '';
  if (intval($data['expiry']) === 0) {
    $delres = $app['db']->delete($prefix.'cryptopaste', array(
      'expiry' => 0,
      'id'     => $data['id'],
    ));
    if ($delres) {
      $burnnotice = 'This paste has been deleted from the database now that you have opened it.';
    } else {
      $burnnotice = 'This paste is set to be deleted from the database once you have opened it, but there was an error doing that. Please inform the owner of this CryptoPaste immediately!';
    }
  // Update view counter
  } else {
    $app['db']->executeQuery('UPDATE '.$prefix.'cryptopaste SET `views`=`views`+1 WHERE `id` = ?', array($data['id']));
  }

  // Render page
  return $app['twig']->render('view_paste.twig', array(
    'timestamp'  => $data['timestamp'],
    'paste'      => $data['data'],
    'views'      => ++$data['views'],
    'burnnotice' => $burnnotice,
  ));
})->bind('view')
->assert('paste_id', '[a-zA-Z0-9]+');


function paste_error($app, $message, $code=400) {
  // Generate new CSRF nonce token
  $nonce = (string)$app['csrf.token_manager']->refreshToken('paste');
  // Generate and return JSON Response object
  return $app->json(array(
    'status'  => 'error',
    'message' => $message,
    'nonce'   => $nonce,
  ), $code);
}


//### EOF
