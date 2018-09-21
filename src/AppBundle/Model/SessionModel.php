<?php

namespace AppBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;
use AppBundle\Entity\Session;

class SessionModel {

  private $doctrine;

  public function __construct(ManagerRegistry $doctrine) {
    $this->doctrine = $doctrine;
  }

  /**
   * Delete all expired sessions
   *
   * @return int Amount of sessions deleted
   */
  public function deleteExpired() {
    $db = $this->doctrine->getManager();
    $query = $db->getRepository(Session::class)->createQueryBuilder('s')
      ->where('s.sess_time + s.sess_lifetime <= :time')
      ->setParameter('time', intval(gmdate('U')))
      ->getQuery();
    $sessions = $query->getResult();
    foreach ($sessions as $session) {
      $db->remove($session);
    }
    $db->flush();
    return count($sessions);
  }

}

// EOF
