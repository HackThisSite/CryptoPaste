<?php

namespace AppBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Hashids\Hashids;
use AppBundle\Entity\Paste;

class PasteModel {

  private $doctrine;

  private $container;

  public function __construct(ManagerRegistry $doctrine, ContainerInterface $container) {
    $this->doctrine = $doctrine;
    $this->container = $container;
  }

  /**
   * Create a new paste DB record
   *
   * @param int $expiry Either: a) UTC Unix timestamp of when to expire this paste, b) 0 for burn-after-reading, c) -1 for never
   * @param string $paste_data
   * @return string String identifier of the new paste
   */
  public function createPaste($expiry, $paste_data) {
    // Prepare the Paste object
    $paste = new Paste();
    $paste->setExpiry($expiry);
    $paste->setData($paste_data);

    // Persist to database
    $db = $this->doctrine->getManager();
    $db->persist($paste);
    $db->flush();

    // Return paste ID
    return $this->_encodeHashids($paste->getID());
  }

  /**
   * Get a paste's data. Also optionally deletes paste if set to burn-after-reading.
   *
   * @param string $paste_id String identifier of a paste
   * @param boolean $volatile [optional] Perform a volatile read (reads then deletes paste if burn-after-reading). Default: true
   * @return AppBundle\Entity\Paste|boolean Fully populated AppBundle\Entity\Paste object, or false on failure
   */
  public function getPaste($paste_id, $volatile = true) {
    // Transform $paste_id into numeric
    $pid = $this->_decodeHashids($paste_id);

    // Return an explicit false if paste ID is invalid
    if ($pid === false) {
      return false;
    }

    // Get Paste object
    $db = $this->doctrine->getManager();
    $query = $db->createQuery('
      SELECT p
      FROM AppBundle:Paste p
      WHERE
        p.id = :id
      AND (
        p.expiry IN (:range) OR
        p.expiry < :expiration
      )
    ')
      ->setParameter('id', $pid)
      ->setParameter('range', array(-1, 0))
      ->setParameter('expiration', intval(gmdate('U')));
    $paste = $query->getOneOrNullResult();

    // Return an explicit false if paste not found
    if (empty($paste)) {
      return false;
    }

    // Delete paste if burn-after-reading is set
    if ($paste->getExpiry() === 0 && $volatile == true) {
      $this->deletePaste($paste);
    }

    // Return paste
    return $paste;
  }

  /**
   * Increment the view counter of a paste
   *
   * @param AppBundle\Entity\Paste|string $paste_obj_or_id An AppBundle\Entity\Paste object, or string identifier of a paste
   * @return int|boolean Updated view counter of paste, or false if not found
   */
  public function incrementViewCounter($paste_obj_or_id) {
    // Get Paste object
    $paste = ($paste_obj_or_id instanceof Paste ? $paste_obj_or_id : $this->getPaste($paste_obj_or_id, false));

    // Return explicit false if paste not found
    if (empty($paste)) {
      return false;
    }

    // Increment the counter in the DB
    $db = $this->doctrine->getManager();
    $views = ($paste->getViews() + 1);
    $paste->setViews($views);
    $db->flush();

    // Return the updated count
    return $views;
  }

  /**
   * Deletes a specified paste
   *
   * @param AppBundle\Entity\Paste|string $paste_obj_or_id An AppBundle\Entity\Paste object, or string identifier of a paste
   */
  public function deletePaste($paste_obj_or_id) {
    // Get Paste object
    $paste = ($paste_obj_or_id instanceof Paste ? $paste_obj_or_id : $this->getPaste($paste_obj_or_id, false));

    // If paste is found, delete it
    if (!empty($paste)) {
      $db = $this->doctrine->getManager();
      $db->remove($paste);
      $db->flush();
    }
  }

  /**
   * Delete all expired pastes
   *
   * @return int Amount of pastes deleted
   */
  public function deleteExpired() {
    $db = $this->doctrine->getManager();
    $query = $db->getRepository(Paste::class)->createQueryBuilder('p')
      ->where('p.expiry > 0')
      ->andWhere('p.expiry <= :expiration')
      ->setParameter('expiration', intval(gmdate('U')))
      ->getQuery();
    $pastes = $query->getResult();
    foreach ($pastes as $paste) {
      $db->remove($paste);
    }
    $db->flush();
    return count($pastes);
  }

  private function _encodeHashids($numeric_id) {
    $secret = $this->container->getParameter('secret');
    $length = $this->container->getParameter('uri_length');
    $hashids = new Hashids($secret, $length);
    return $hashids->encode($numeric_id);
  }

  private function _decodeHashids($paste_id) {
    $secret = $this->container->getParameter('secret');
    $length = $this->container->getParameter('uri_length');
    $hashids = new Hashids($secret, $length);
    $pid = $hashids->decode($paste_id);
    return (empty($pid) ? false : $pid[0]);
  }

}

// EOF
