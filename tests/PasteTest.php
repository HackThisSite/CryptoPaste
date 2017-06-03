<?php
// TODO - This needs done

use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

class PasteTest extends TestCase {

  static private $pdo = null;

  private $conn = null;

  /**
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  final public function getConnection() {
    if ($this->conn === null) {
      if (self::$pdo == null) {
        self::$pdo = new PDO('sqlite::memory:');
      }
      $this->conn = $this->createDefaultDBConnection(self::$pdo, ':memory:');
    }
    return $this->conn;
  }

  /**
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  public function getDataSet() {
    $ds = $this->createXMLDataSet(dirname(__FILE__).'/_files/db.xml');
    $rds = new PHPUnit_Extensions_Database_DataSet_ReplacementDataSet($ds);
//    $rds->addFullReplacement('##EXPIRY##', time()+5);
    return $rds;
  }

  public function testTODO() {
    $this->assertEmpty(null);
  }

}
