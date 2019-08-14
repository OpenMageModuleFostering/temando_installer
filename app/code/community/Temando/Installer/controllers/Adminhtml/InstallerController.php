<?php


class Temando_Installer_Adminhtml_InstallerController extends Mage_Adminhtml_Controller_Action {
    
    const ERR_NO_SOAP = 'SOAP is not enabled on this server.  Please enable SOAP to use the Temando plugin.';
    const NOTICE_NO_COMMUNITY = 'The community channel cannot be found.  Please install the community channel for Magento Connect.';
    const NOTICE_UPGRADE = 'Note: if you have any customisations relating to your Temando extension, upgrading your Temando extension will remove these. Contact your Temando representative for guidance.';
    const NOTICE_ATTRIBUTES = 'Note: if upgrading from the Starter Extension to a Business Extension, please re-index your products.';

    public function indexAction()
    {
        $communityChannel = Mage::getModel('temandoinstaller/connect')->getSingleConfig()->isChannel('community');
        if (!$communityChannel) {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('temandoinstaller')->__(self::NOTICE_NO_COMMUNITY));
        }
        if ($this->checkSoap()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__(self::ERR_NO_SOAP));
            return $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
        } else {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('temandoinstaller')->__(self::NOTICE_UPGRADE));
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('temandoinstaller')->__(self::NOTICE_ATTRIBUTES));
            $this->loadLayout()->renderLayout();  
        }
    }
    
    public function newAction()
    {
	$this->_forward('install');
    }
        
    public function installAction()
    {
            
        $helper = Mage::helper('temandoinstaller');
        /* @var $helper Temando_Installer_Helper_Data */
        
        $installer = Mage::getModel('temandoinstaller/installer');
        /* @var $installer Temando_Installer_Model_Installer */
        
        
        if ($helper->getTemandoVersion()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Temando is already installed.'));
            $this->_redirect('*/*/');
            return;
        }

        //check token
        $currentService = $installer->getCurrentService();
        if(!$currentService) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Could not find valid subscription.'));
            $this->_redirect('*/*/');
            return;
        }
        //check the latest release
        if (!isset($currentService->links->software_latest_release->version)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Could not find latest release.'));
	    $this->_redirect('*/*/');
            return;
        }

        //check file
        $file = $installer->downloadPackage($currentService);
        
        if(!$file) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Error downloading file.'));
            $this->_redirect('*/*/');
            return;
        }

        $command = Mage::getModel('temandoinstaller/connect');
        if($command->install($file)) {
            
            //clean the cache
            Mage::app()->cleanCache(array('CONFIG'));
            
            $installer
                ->setName('Temando')
                ->setToken($helper->getTemandoToken())
                ->setVersion($currentService->links->software_latest_release->version)
                ->setModule(Temando_Installer_Helper_Data::TMD_MODULE_NAME)
                ->setInstallDate(date('Y-m-d H:i:s'))
                ->setUpdateDate(date('Y-m-d H:i:s'))
                ->setUpdateAvailable(false)
                ->setUpdateDismissed(false)
                ->setUpdateDetails(NULL)
                ->setStatus(1)
                ->save();
        }
        
        //delete file
        @unlink($file);
        
        if(Mage::registry('temandoinstaller_errors')) {
            foreach (Mage::registry('temandoinstaller_errors') as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temandoinstaller')->__('Package successfully installed.'));
        }
        
        $this->_redirect('*/*/');
        return;
    }
    
    public function updateAction()
    {
        $helper = Mage::helper('temandoinstaller');
        /* @var $helper Temando_Installer_Helper_Data */
        
        $installer = Mage::getModel('temandoinstaller/installer');
        /* @var $installer Temando_Installer_Model_Installer */
        
        $params = $this->getRequest()->getParams();
	if (!isset($params['massaction']) || !is_array($params['massaction']) || empty($params['massaction'])) {
	    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('No modules selected for update.'));
	    $this->_redirect('*/*/');
            return;
	}

        //check token
        $currentService = $installer->getCurrentService();
        if (!$currentService) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Could not find valid service.'));
            $this->_redirect('*/*/');
            return;
        }
        //check the latest release
        if (!isset($currentService->links->software_latest_release->version)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Could not find latest release.'));
	    $this->_redirect('*/*/');
            return;
        }
        //compare the current version
        if ($helper->getTemandoVersionNumber() >= $currentService->links->software_latest_release->version) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Current version is greater than or equal to the latest release.'));
	    $this->_redirect('*/*/');
            return;
        }
        
        $installerIds = $params['massaction'];
        
        //at the moment only one product will be on the grid - the mass action has been added for future use
        foreach ($installerIds as $id) {
            //check file
            $file = $installer->downloadPackage($currentService);

            if(!$file) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Error downloading file.'));
                $this->_redirect('*/*/');
                return;
            }
            
            $installer->load($id);
            $command = Mage::getModel('temandoinstaller/connect');
            if(!$command->uninstall($installer->getModule())) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('There was an error uninstalling the current module.'));
                $this->_redirect('*/*/');
                return;
            }
            //clean cache before installing new module
            Mage::app()->cleanCache(array('CONFIG'));
            if($command->install($file)) {
                //clean the cache after installing new module
                Mage::app()->cleanCache(array('CONFIG'));
                $installer
                    ->setName('Temando')
                    ->setToken($helper->getTemandoToken())
                    ->setVersion($currentService->links->software_latest_release->version)
                    ->setModule(Temando_Installer_Helper_Data::TMD_MODULE_NAME)
                    ->setUpdateDate(date('Y-m-d H:i:s'))
                    ->setUpdateAvailable(false)
                    ->setUpdateDismissed(false)
                    ->setUpdateDetails(NULL)
                    ->setStatus(1)
                    ->save();
            }
        
            //delete file
            @unlink($file);
        }
                
        if(Mage::registry('temandoinstaller_errors')) {
            foreach (Mage::registry('temandoinstaller_errors') as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temandoinstaller')->__('Package successfully updated.'));
        }
        
        $this->_redirect('*/*/');
    }
    
    public function uninstallAction()
    {
        $helper = Mage::helper('temandoinstaller');
        /* @var $helper Temando_Installer_Helper_Data */
                
        $params = $this->getRequest()->getParams();
	if (!isset($params['massaction']) || !is_array($params['massaction']) || empty($params['massaction'])) {
	    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('No modules selected for removal.'));
	    $this->_redirect('*/*/');
	}

        //uninstall does not require token checks
        
        $installerIds = $params['massaction'];
        
        foreach ($installerIds as $id) {
            $installer = Mage::getModel('temandoinstaller/installer')->load($id);
            $command = Mage::getModel('temandoinstaller/connect');
            if($command->uninstall($installer->getModule())) {
                $installer->delete();
            }
        }
                
        if(Mage::registry('temandoinstaller_errors')) {
            foreach (Mage::registry('temandoinstaller_errors') as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temandoinstaller')->__('Package successfully uninstalled.'));
        }
        
        Mage::app()->cleanCache(array('CONFIG'));
        $this->_redirect('*/*/');
        return;
    }
    
    public function testConnectionSettingsAction()
    {
        Mage::getModel('temandoinstaller/tester')->testSettings();
        return $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
    
    public function dismissUpdateAction()
    {
        $module_id = $this->getRequest()->getParam('id');
	$module = Mage::getModel('temandoinstaller/installer')->load($module_id);
	/* @var $module Temando_Installer_Model_Installer */
        
        if (!$module->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('temandoinstaller')->__('Modules does not exist.'));
        } else {
            $module
                ->setUpdateDismissed(true)
                ->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('temandoinstaller')->__('Temando update has been dismissed.'));
        }
        return $this->getResponse()->setRedirect($this->getRequest()->getServer('HTTP_REFERER'));
    }
    
    /**
     * Checks to see if the SOAP extension is loaded
     * @return boolean
     */
    public function checkSoap() {
        if (!extension_loaded('soap')) {
            return true;
        } else {
            return false;
        }
    }

}

