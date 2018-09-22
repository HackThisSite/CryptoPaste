<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="pastes")
 * @ORM\HasLifecycleCallbacks()
 */
class Paste {

  /**
   * Paste numeric ID
   *
   * @ORM\Id
   * @ORM\Column(name="id", type="bigint")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * Timestamp when this paste was created
   *
   * @ORM\Column(name="timestamp", type="bigint")
   */
  protected $timestamp;

  /**
   * Timestamp when this paste expires and will be deleted
   *
   * @ORM\Column(name="expiry", type="bigint")
   */
  protected $expiry;

  /**
   * Number of views for this paste
   *
   * @ORM\Column(name="views", type="integer", options={"default": 0})
   */
  protected $views = 0;

  /**
   * Encrypted paste data
   *
   * @ORM\Column(name="data", type="blob")
   */
  protected $data;

  public function getID() {
    return $this->id;
  }

  public function setID($id) {
    $this->id = $id;
    return $this;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }

  public function setTimestamp($timestamp) {
    $this->timestamp = $timestamp;
    return $this;
  }

  /**
   * @ORM\PrePersist
   */
  public function setTimestampValue() {
    $this->timestamp = intval(gmdate('U'));
  }

  public function getExpiry() {
    return $this->expiry;
  }

  public function setExpiry($expiry) {
    $this->expiry = $expiry;
    return $this;
  }

  public function getViews() {
    return $this->views;
  }

  public function setViews($views) {
    $this->views = $views;
    return $this;
  }

  public function getData() {
    if (gettype($this->data) == 'resource' && get_resource_type($this->data) == 'stream') {
      return stream_get_contents($this->data);
    } else {
      return $this->data;
    }
  }

  public function setData($data) {
    $this->data = $data;
    return $this;
  }

}

// EOF
