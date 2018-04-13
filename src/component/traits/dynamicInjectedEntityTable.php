<?php

namespace ComponentTraits;

trait dynamicInjectedEntityTable {
  public function setEntityTable($schema, $entity, $base)
  {
    $link = $this->connection()->create($schema);
    $table = $entity::getTable($link, $base);
    $this->connection()->destroy($link);

    if ($table === null) { return null; }

    $this->em($schema)
      ->getClassMetadata(get_class($entity))
      ->setTableName(
        /* WORKAROUND AFTER setAutoGenerateProxyClasses */
        /*($schema === 'flights') ? ('`'.$table.'`') : $table*/
        $table
      );

    return $table;
  }
}
