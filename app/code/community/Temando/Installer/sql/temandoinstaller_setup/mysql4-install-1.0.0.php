<?php

/** @var $this Mage_Eav_Model_Entity_Setup */
/** @var $installer Mage_Eav_Model_Entity_Setup */

$installer = $this;
$installer->startSetup();

$installer->run(
    "DROP TABLE IF EXISTS {$this->getTable('temando_installer')};
    CREATE TABLE {$this->getTable('temando_installer')} (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` TEXT NOT NULL,
    `token` TEXT NOT NULL,
    `version` TEXT NOT NULL,
    `module` varchar(50) NOT NULL,
    `install_date` DATETIME NULL DEFAULT NULL,
    `update_date` DATETIME NULL DEFAULT NULL,
    `update_available` tinyint(1) NOT NULL DEFAULT '0',
    `update_dismissed` tinyint(1) NOT NULL DEFAULT '0',
    `update_details` TEXT,
    `status` int(2) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE `module` (`module`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);

$installer->endSetup();
