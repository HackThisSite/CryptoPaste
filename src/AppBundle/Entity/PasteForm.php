<?php

namespace AppBundle\Entity;

class PasteForm {

  protected $paste;

  protected $expiration;

  public function getPaste() {
    return $this->paste;
  }

  public function setPaste($paste) {
    $this->paste = $paste;
    return $this;
  }

  public function getExpiration() {
    return $this->expiration;
  }

  public function setExpiration($expiration) {
    $this->expiration = $expiration;
    return $this;
  }

}

// EOF
