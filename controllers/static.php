<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//
// GET /faq - Frequently Asked Questions
//
$app->get('/faq', function (Request $req) use ($app) {
  return $app['twig']->render('faq.twig', array(
    'contact' => $app['config']['ui']['admin_contact'],
  ));
})->bind('faq');


//### EOF
