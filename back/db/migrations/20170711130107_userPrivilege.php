<?php

use Phinx\Migration\AbstractMigration;

class UserPrivilege extends AbstractMigration
{
    public function change()
    {
        $usersTable = $this->table('user_personal');
        $hasColumn = $usersTable->hasColumn('privilege');
        if ($hasColumn) {
            $q = "ALTER TABLE `user_personal`
                DROP `privilege`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $userActivityTable = $this->table('user_activity');
        $hasColumn = $userActivityTable->hasColumn('senderId');
        if ($hasColumn) {
            $q = "ALTER TABLE `user_activity`
                DROP `senderId`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $userActivityTable->hasColumn('senderName');
        if ($hasColumn) {
            $q = "ALTER TABLE `user_activity`
                DROP `senderName`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $userActivityTable->hasColumn('targetId');
        if ($hasColumn) {
            $q = "ALTER TABLE `user_activity`
                CHANGE COLUMN `targetId` `code` INT NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $userActivityTable->hasColumn('targetName');
        if ($hasColumn) {
            $q = 'ALTER TABLE `user_activity` CHANGE `targetName` `message`
                TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;';
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $userActivityTable->hasColumn('userId');
        if ($hasColumn) {
            $q = "ALTER TABLE `user_activity`
                CHANGE COLUMN `userId` `id_user` INT NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }
    }
}
