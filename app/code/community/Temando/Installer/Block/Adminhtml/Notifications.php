<?php

class Temando_Installer_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    public function getModuleNotifications()
    {
        $installerModules = Mage::getModel('temandoinstaller/installer')->getCollection();
        $installerModules->addFieldToFilter('update_dismissed', false);
        $installerModules->addFieldToFilter('update_available', true);
        return $installerModules;
    }
}


