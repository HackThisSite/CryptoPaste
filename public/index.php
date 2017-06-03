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
// Load Composer autoloader
//
require_once __DIR__.'/../vendor/autoload.php';


//
// Define application object
//
$app = new Silex\Application();


//
// Load application source
//
require_once __DIR__.'/../src/app.php';


//
// Run application
//
$app->run();


//### EOF
