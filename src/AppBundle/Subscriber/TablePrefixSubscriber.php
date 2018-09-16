<?php

namespace AppBundle\Subscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TablePrefixSubscriber implements EventSubscriber {

  protected $prefix = '';

  public function __construct($prefix) {
    $this->prefix = (string) $prefix;
  }

  public function getSubscribedEvents() {
    return array('loadClassMetadata');
  }

  public function loadClassMetadata(LoadClassMetadataEventArgs $args) {
    $classMetadata = $args->getClassMetadata();
    if ($classMetadata->isInheritanceTypeSingleTable() && !$classMetadata->isRootEntity()) {
      // if we are in an inheritance hierarchy, only apply this once
      return;
    }

    $classMetadata->setTableName($this->prefix . $classMetadata->getTableName());

    foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
      if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY) {
        if (!empty($classMetadata->associationMappings[$fieldName]['joinTable'])) {
          $mappedTableName = $classMetadata->associationMappings[$fieldName]['joinTable']['name'];
          $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
        }
      }
    }
  }
  
}

// EOF
