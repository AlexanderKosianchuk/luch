<?php

use Phinx\Migration\AbstractMigration;

class UserPrivilege extends AbstractMigration
{
    public function change()
    {
        $usersTable = $this->table('user_personal');
        $hasColumn = $usersTable->hasColumn('privilege');
        if (!$hasColumn) {
            $q = "ALTER TABLE `user_personal`
                DROP `privilege`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }
    }
}
