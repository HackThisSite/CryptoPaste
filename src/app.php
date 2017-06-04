<?php

/*************************************************************
 *   _____                  _        _____          _        *
 *  / ____|                | |      |  __ \        | |       *
 * | |     _ __ _   _ _ __ | |_ ___ | |__) |_ _ ___| |_ ___  *
 * | |    | '__| | | | '_ \| __/ _ \|  ___/ _` / __| __/ _ \ *
 * | |____| |  | |_| | |_) | || (_) | |  | (_| \__ \ ||  __/ *
 *  \_____|_|   \__, | .__/ \__\___/|_|   \__,_|___/\__\___| *
 *               __/ | |                                     *
 *              |___/|_|                                     *
 *                                                           *
 *        https://github.com/HackThisCode/CryptoPaste        *
 *                                                           *
 *  Copyright (C) 2017 HackThisSite. Licensed under GPLv3.   *
 * Please see LICENSE for complete license and restrictions. *
 *                                                           *
 *************************************************************/


//
// Set namespaces
//
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\Debug;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


//
// Register PHP error and exception handlers
//
ErrorHandler::register();
ExceptionHandler::register();


//
// Load and test global application configuration
//
$config = @parse_ini_file(BASE_DIR.'/config.ini', true);
if ($config === FALSE) throw new ErrorException('Cannot find config.ini');
$app['config'] = $config;
unset($config);
// Assert [global]
assert(!empty($app['config']['global']), 'global category is set');
assert(in_array($app['config']['global']['log_level'], array('debug', 'info', 'warn', 'warning', 'err', 'error')), 'global.log_level is set to: debug, info, warning, error');
assert(intval($app['config']['global']['body_max_length']), 'global.body_max_length is set to integer value');
assert(intval($app['config']['global']['cookie_lifetime']), 'global.cookie_lifetime is set to integer value');
// Assert [ui]
assert(!empty($app['config']['ui']), 'global category is set');
assert(!empty($app['config']['ui']['admin_contact']), 'ui.admin_contact is set');
assert(isset($app['config']['ui']['show_paste_total']), 'ui.show_paste_total is boolean');
// Assert [hashids]
assert(!empty($app['config']['hashids']), 'hashids category is set');
assert(intval($app['config']['hashids']['length']), 'hashids.length is set to integer value');
assert(!empty($app['config']['hashids']['salt']), 'hashids.salt is set');
// Assert [db]
assert(!empty($app['config']['db']), 'db category is set');
assert(in_array($app['config']['db']['driver'], array('mysql', 'sqlite')), 'db.driver is set to: mysql, sqlite');
if ($app['config']['db']['driver'] == 'mysql') {
  assert(!empty($app['config']['db']['host']), 'db.host is set');
  if (!empty($app['config']['db']['port'])) {
    assert(intval($app['config']['db']['port']), 'db.port is set to integer value');
  }
  assert(!empty($app['config']['db']['username']), 'db.username is set');
  assert(!empty($app['config']['db']['password']), 'db.password is set');
  assert(!empty($app['config']['db']['database']), 'db.database is set');
} else if ($app['config']['db']['driver'] == 'sqlite') {
  assert(!empty($app['config']['db']['path']), 'db.path is set');
}


//
// Set environment and enable debug mode if in development environment
//
$app['development'] = !empty($app['config']['global']['development']);
if ($app['development']) {
  Debug::enable();
  $app['debug'] = true;
}


//
// Register logging facility
//
if (isset($app['config']['global']['log_level'])) {
  switch (strtolower($app['config']['global']['log_level'])) {
    case 'debug':
      $log_level = Logger::DEBUG;
      break;
    case 'info':
      $log_level = Logger::INFO;
      break;
    case 'warn':
    case 'warning':
      $log_level = Logger::WARNING;
      break;
    case 'err':
    case 'error':
      $log_level = Logger::ERROR;
      break;
    default:
      $log_level = Logger::WARNING;
      break;
  }
} else {
  $log_level = Logger::WARNING;
}
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => BASE_DIR.'/cache/log/cryptopaste.log',
  'monolog.level'   => $log_level,
  'monolog.name'    => 'CRYPTOPASTE',
));


//
// Register database connection
//
if ($app['config']['db']['driver'] == 'sqlite') {
  $dbopts = array(
    'driver' => 'pdo_sqlite',
    'path'   => $app['config']['db']['path'],
  );
} else if ($app['config']['db']['driver'] == 'mysql') {
  $dbopts = array(
    'driver'   => 'pdo_mysql',
    'host'     => $app['config']['db']['host'],
    'port'     => $app['config']['db']['port'],
    'user'     => $app['config']['db']['username'],
    'password' => $app['config']['db']['password'],
    'dbname'   => $app['config']['db']['database'],
    'charset'  => 'utf8',
  );
}
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => $dbopts,
));


//
// Register session storage handler
//

// Set max session lifetime
@ini_set('session.gc_maxlifetime', $app['config']['global']['cookie_lifetime']);

// Register handler
$app->register(new Silex\Provider\SessionServiceProvider(), array(
  'session.storage.options' => array(
    'cookie_lifetime' => $app['config']['global']['cookie_lifetime'],
  ),
));


//
// Register views service
//
$twig_opts = array(
  'cache' => BASE_DIR.'/cache/twig',
);
if ($app['development']) {
  $twig_opts['debug'] = true;
  $twig_opts['auto_reload'] = true;
}
$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path'    => BASE_DIR.'/src/views',
  'twig.options' => $twig_opts,
));


//
// Register assets service
//
$app->register(new Silex\Provider\AssetServiceProvider(), array(
  'assets.named_packages' => array(
    'css'        => array('base_path' => '/css'),
    'hjscss'     => array('base_path' => '/css/hjs'),
    'js'         => array('base_path' => '/js'),
    'img'        => array('base_path' => '/img'),
    'securimage' => array('base_path' => '/securimage'),
  ),
));


//
// Preset Twig globals
//
$app->before(function (Request $req) use ($app) {
  // Max body length
  $app['twig']->addGlobal('max_length', $app['config']['global']['body_max_length']);
  // Total pastes (deleted and active)
  if ($app['config']['ui']['show_paste_total']) {
    $prefix = (!empty($app['config']['db']['table_prefix']) ? $app['config']['db']['table_prefix'] : '');
    if ($app['config']['db']['driver'] == 'mysql') {
      $query = 'SELECT AUTO_INCREMENT AS next_id FROM information_schema.tables WHERE table_name="'.$prefix.'cryptopaste" AND table_schema=DATABASE()';
      $result = $app['db']->fetchAssoc($query);
      $total = $result['next_id'] - 1;
    } else if ($app['config']['db']['driver'] == 'sqlite') {
      $query = 'SELECT * FROM SQLITE_SEQUENCE WHERE name = ?';
      $result = $app['db']->fetchAssoc($query, array($prefix.'cryptopaste'));
      $ressplit = explode('|', $result['seq']);
      $total = (count($ressplit) > 1 ? $ressplit[1] : 0);
    }
    $app['twig']->addGlobal('total_pastes', number_format($total));
  }
});


//
// Rotate session IDs to prevent reuse
//
$app->after(function (Request $req) use ($app) {
  if ($app['session']->isStarted()) {
    $app['monolog']->debug('Rotating session ID');
    $app['session']->migrate(true);
  }
});


// Register HashIDs service
$app['hashids'] = function() use ($app) {
  return new Hashids\Hashids($app['config']['hashids']['salt'], $app['config']['hashids']['length']);
};


//
// Register additional providers
//
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SerializerServiceProvider());
$app->register(new Silex\Provider\CsrfServiceProvider());


//
// Register web profiler for debugging (development environment only)
//
if ($app['development']) {
  $app->register(new Silex\Provider\HttpFragmentServiceProvider());
  $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
    'profiler.cache_dir'    => BASE_DIR.'/cache/profiler',
    'profiler.mount_prefix' => '/_profiler', // Needs to be unique since this is the URI prefix
  ));
  $app->register(new Sorien\Provider\DoctrineProfilerServiceProvider());
}


//
// Define routing components
//
$controllers = array(
  'captcha',
  'static',
  'paste', // This should always be last
);
foreach ($controllers as $controller) {
  require_once BASE_DIR.'/src/controllers/'.$controller.'.php';
}


//
// Run application
//
$app->run();


//### EOF
