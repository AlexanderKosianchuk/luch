<?php

use Phinx\Migration\AbstractMigration;

class ExpandedFolder extends AbstractMigration
{
    public function change()
    {
        $foldersTable = $this->table('folders');
        $hasColumn = $foldersTable->hasColumn('is_expanded');
        if (!$hasColumn) {
            $q = "ALTER TABLE `folders`
                ADD `is_expanded` TINYINT NOT NULL AFTER `path`";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $foldersTable->hasColumn('userId');
        if ($hasColumn) {
            $q = "ALTER TABLE `folders`
                CHANGE COLUMN `userId` `id_user` INT NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }
    }
}
