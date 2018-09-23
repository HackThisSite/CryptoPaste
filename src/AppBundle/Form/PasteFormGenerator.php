<?php

namespace AppBundle\Form;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use AppBundle\Entity\PasteForm;

class PasteFormGenerator {

  private $form_factory;
  private $csrf;
  private $body_max_length;

  public function __construct(
    FormFactoryInterface $form_factory,
    CsrfTokenManagerInterface $csrf,
    $body_max_length
  ) {
    $this->form_factory = $form_factory;
    $this->csrf = $csrf;
    $this->body_max_length = $body_max_length;
  }

  public function generateForm() {
    $paste = new PasteForm();
    return $this->form_factory->create('AppBundle\Form\PasteType', $paste, array(
      'body_max_length' => $this->body_max_length,
      'nonce' => $this->csrf->getToken('paste')->getValue(),
    ), array());
  }

}

// EOF
