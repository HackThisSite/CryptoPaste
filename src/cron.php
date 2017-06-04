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
// Set base working directory global
//
define('BASE_DIR', realpath(dirname(__FILE__).'/../'));


//
// Load Composer autoloader
//
require_once BASE_DIR.'/vendor/autoload.php';


//
// Set namespaces
//
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;


//
// Load and test global application configuration
//
$config = @parse_ini_file(BASE_DIR.'/config.ini', true);
if ($config === FALSE) throw new ErrorException('Cannot find config.ini');
// Assert [db]
assert(!empty($config['db']), 'db category is set');
assert(in_array($config['db']['driver'], array('mysql', 'sqlite')), 'db.driver is set to: mysql, sqlite');
if ($config['db']['driver'] == 'mysql') {
  assert(!empty($config['db']['host']), 'db.host is set');
  if (!empty($config['db']['port'])) {
    assert(intval($config['db']['port']), 'db.port is set to integer value');
  }
  assert(!empty($config['db']['username']), 'db.username is set');
  assert(!empty($config['db']['password']), 'db.password is set');
  assert(!empty($config['db']['database']), 'db.database is set');
} else if ($config['db']['driver'] == 'sqlite') {
  assert(!empty($config['db']['path']), 'db.path is set');
}


// Instantiate Doctrine DBAL connection
$dbalcfg = new Configuration();
if ($config['db']['driver'] == 'mysql') {
  $dbalparams = array(
    'driver'   => 'pdo_mysql',
    'host'     => $config['db']['host'],
    'user'     => $config['db']['username'],
    'password' => $config['db']['password'],
    'dbname'   => $config['db']['database'],
  );
  if (isset($config['db']['port'])) $dbalparams['port'] = $config['db']['port'];
} else {
  $dbalparams = array(
    'driver' => 'pdo_sqlite',
    'path'   => $config['db']['path'],
  );
}
$db = DriverManager::getConnection($dbalparams, $dbalcfg);

$prefix = (!empty($config['db']['table_prefix']) ? $config['db']['table_prefix'] : '');
$count = $db->executeUpdate('DELETE FROM '.$prefix.'cryptopaste WHERE `expiry` NOT IN (-1, 0) AND UNIX_TIMESTAMP(NOW()) >= `expiry`');

if ($count > 0) {
  echo '['.gmdate('r').'] Deleted '.$count." row(s).\n";
}
