<?php

/** set_time_limit(0); */

$installer = $this;
$installer->startSetup();

/** @var $helper Temando_Installer_Helper_Data */
$helper = Mage::helper('temandoinstaller');

//check if Temando is already installed and add a record of the current module if it is
if ($helper->getTemandoVersion()) {
    $versionNumber = (string) Mage::getConfig()->getNode()->modules->Temando_Temando->version;
    $versionName = 'Temando ' . ucfirst($helper->getTemandoVersionName());
    //because there is no record of when it was originally installed/updated we will enter todays date
    $installDate = Mage::getSingleton('core/date')->gmtDate();
    $updateDate = Mage::getSingleton('core/date')->gmtDate(); 
    // @codingStandardsIgnoreStart
    $installer->run(
       "INSERT INTO {$this->getTable('temando_installer')} (`name`, `token`, `version`, `module`, `install_date`, `update_date`, `update_available`, `update_dismissed`, `update_details`, `status`) VALUES 
       ('{$versionName}','Token not set','{$versionNumber}','Temando_Temando','{$installDate}','{$updateDate}',0,0,NULL,1);"
    );
    // @codingStandardsIgnoreEnd
}

$installer->endSetup();
