<?php

use Phinx\Migration\AbstractMigration;

class Init extends AbstractMigration
{
    public function change()
    {
        if (!$this->hasTable('flight_comments')) {
            $q = "CREATE TABLE `flight_comments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `comment` text NOT NULL,
                `commander-admitted` tinyint(1) NOT NULL,
                `aircraft-allowed` tinyint(1) NOT NULL,
                `general-admission` tinyint(1) NOT NULL,
                `id_flight` int(11) NOT NULL,
                `id_user` int(11) NOT NULL,
                `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `id_flight` (`id_flight`),
                KEY `id_user` (`id_user`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if (!$this->hasTable('event_settlements')) {
            $q = "CREATE TABLE `event_settlements` (
              `id` int(11) NOT NULL,
              `id_event` int(11) NOT NULL,
              `text` varchar(255) NOT NULL,
              `alg` text NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('user_avaliability')) {
            $q = "DELETE FROM `user_avaliability`
                WHERE `type` != 'brutype';";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "RENAME TABLE `user_avaliability` TO `fdr_to_user`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `fdr_to_user`
                DROP `type`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `fdr_to_user`
                CHANGE COLUMN `userId` `id_user` INT NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `fdr_to_user`
                CHANGE COLUMN `targetId` `id_fdr` INT NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "UPDATE `fdr_to_user` SET `allowedBy`='2017-01-01 00:00:00' WHERE 1;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `fdr_to_user`
                CHANGE COLUMN `allowedBy` `dt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('flightsinfolders')) {
            $q = "RENAME TABLE `flightsinfolders` TO `flight_to_folder`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('brutypes')) {
            $q = "ALTER TABLE `brutypes`
                ADD `code` VARCHAR(255) NOT NULL AFTER `bruType`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "UPDATE `brutypes`
                 SET `code`= REPLACE(`gradiApTableName`, '_ap', '') WHERE 1;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "RENAME TABLE brutypes TO fdrs;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `fdrs`
                CHANGE `bruType` `name` VARCHAR(255)
                CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if (!$this->hasTable('calibrations')) {
            $q = "CREATE TABLE `calibrations` ( `id` INT NOT NULL AUTO_INCREMENT ,
                `name` VARCHAR(255) NOT NULL ,
                `id_fdr` INT NOT NULL ,
                `id_user` INT NOT NULL ,
                `dt_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                `dt_updated` DATETIME NOT NULL ,
                PRIMARY KEY (`id`), INDEX (`id_fdr`),
                INDEX (`id_user`)
            ) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if (!$this->hasTable('events')) {
            $q = "CREATE TABLE `events` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(255) NOT NULL,
                `status` varchar(3) NOT NULL,
                `text` varchar(255) NOT NULL,
                `ref_param` varchar(255) NOT NULL,
                `min_length` mediumint(12) NOT NULL,
                `alg` text NOT NULL,
                `comment` text NOT NULL,
                `alg_text` varchar(255) NOT NULL,
                `visualization` varchar(10) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if (!$this->hasTable('event_to_fdr')) {
            $q = "CREATE TABLE `event_to_fdr` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `id_event` INT NOT NULL ,
                `id_fdr` INT NOT NULL ,
                `id_user` INT NOT NULL ,
                `param_substitution`  varchar(255) NULL,
                PRIMARY KEY (`id`),
                INDEX (`id_event`),
                INDEX (`id_fdr`),
                INDEX (`id_user`)
            ) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_general_ci;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if (!$this->hasTable('event_settlement')) {
            $q = "CREATE TABLE `event_settlement` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `id_event` INT NOT NULL,
                `text` varchar(255) NOT NULL,
                `alg` text NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`id_event`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $flightsTable = $this->table('flights');
        $hasColumn = $flightsTable->hasColumn('id_fdr');

        if (!$hasColumn) {
            $q = "ALTER TABLE `flights`
                ADD `id_fdr` INT NOT NULL AFTER `bruType`, ADD INDEX (`id_fdr`);";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "UPDATE `flights`
                LEFT JOIN `fdrs` ON `flights`.`bruType`= `fdrs`.`name` COLLATE utf8_unicode_ci
                SET `flights`.`id_fdr`  = `fdrs`.`id`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $flightsTable->hasColumn('guid');
        if (!$hasColumn) {
            $q = "ALTER TABLE `flights`
                ADD `guid` VARCHAR(20) NOT NULL AFTER `fileName`;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "UPDATE `flights`
                SET `guid`=REPLACE(`apTableName`, '_ap', '') WHERE 1;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        $hasColumn = $flightsTable->hasColumn('id_user');
        if (!$hasColumn) {
            $q = "UPDATE `user_personal`
                SET `author` = '';";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;

            $q = "ALTER TABLE `user_personal`
                CHANGE `author` `id_user` INT NULL;";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('enginediscrep')) {
            $q = "DROP TABLE enginediscrep";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('slicetypes')) {
            $q = "DROP TABLE slicetypes";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('slicesummer')) {
            $q = "DROP TABLE slicesummer";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('slices')) {
            $q = "DROP TABLE slices";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }

        if ($this->hasTable('sliceetalonparams')) {
            $q = "DROP TABLE sliceetalonparams";
            $this->execute($q);
            echo $q . PHP_EOL . PHP_EOL;
        }
    }
}
