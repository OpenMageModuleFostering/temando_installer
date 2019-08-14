<?php
/**
 * Cron
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 *
 * Cron job currently for checking updates on the main Temando module
 */

class Temando_Installer_Model_Cron extends Mage_Core_Model_Abstract
{
    protected $_helper;
    
    public function _construct()
    {
        parent::_construct();
        $this->initialize();
    }
    
    public function checkReleases()
    {
        $module = Temando_Installer_Helper_Data::TMD_MODULE_NAME;
        $installer = Mage::getModel('temandoinstaller/installer');
        /** @var $installer Temando_Installer_Model_Installer */
        
        //check token
        $currentService = $installer->getCurrentService();
        if (!$currentService) {
            return;
        }
        
        //check the latest release
        if (!isset($currentService->links->software_latest_release->version)) {
            return;
        }
        
        //compare the current version
        if ($this->_helper->getTemandoVersionNumber() >= $currentService->links->software_latest_release->version) {
            //if the release version isn't higher than the current version return as there is no update available
            return;
        }
        
        $versionNumber = $currentService->links->software_latest_release->version;
        $updateDetails = 'Temando v' . $versionNumber . ' is available.';
        $installerModules = Mage::getModel('temandoinstaller/installer')->getCollection();
        $installerModules->addFieldToFilter('module', $module);
        $installerModules->addFieldToFilter('update_available', false);
        $installerModules->addFieldToFilter('update_dismissed', false);
        foreach ($installerModules as $module) {
            $this->_saveInstaller($installer);
        }
    }

    protected function _saveInstaller($installerModel)
    {
        /** @var $installer Temando_Installer_Model_Installer */
        $installerModel
            ->setUpdateAvailable(true)
            ->setUpdateDetails($updateDetails)
            ->save();
    }
    protected function initialize()
    {
        if (!$this->_helper) {
            $this->_helper = Mage::helper('temandoinstaller');
        }
    }
}
