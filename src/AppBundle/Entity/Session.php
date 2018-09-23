<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sessions")
 */
class Session {


  /**
   * @ORM\Id
   * @ORM\Column(name="sess_id", type="binary", length=128)
   */
  protected $id;


  /**
   * @ORM\Column(name="sess_time", type="bigint")
   */
  protected $time;


  /**
   * @ORM\Column(name="sess_lifetime", type="integer")
   */
  protected $ttl;


  /**
   * @ORM\Column(name="sess_data", type="blob")
   */
  protected $data;


  public function getID() {
    return $this->id;
  }

  public function setID($id) {
    $this->id = $id;
  }

  public function getTime() {
    return $this->time;
  }

  public function setTime($time) {
    $this->time = $time;
  }

  public function getTTL() {
    return $this->ttl;
  }

  public function setTTL($ttl) {
    $this->ttl = $ttl;
  }

  public function getData() {
    return $this->data;
  }

  public function setData($data) {
    $this->uuid = $data;
  }

}

// EOF
