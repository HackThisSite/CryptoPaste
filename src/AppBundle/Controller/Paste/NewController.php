<?php

namespace AppBundle\Controller\Paste;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Twig\TwigHelper;
use AppBundle\Form\PasteFormGenerator;

class NewController extends Controller {

  /**
   * Default page. Presents user the form to create a new paste.
   *
   * @Route("/", name="new")
   */
  public function newAction(Request $request, TwigHelper $view, PasteFormGenerator $generator) {
    $form = $generator->generateForm();
    return $this->render($view->getViewPath('new_paste.html.twig'), array(
      'form' => $form->createView(),
    ));
  }

}

// EOF
