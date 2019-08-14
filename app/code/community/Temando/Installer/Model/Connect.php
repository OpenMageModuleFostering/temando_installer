<?php
/**
 * Connect
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */

class Temando_Installer_Model_Connect extends Mage_Core_Model_Abstract
{
    protected $_config;
    protected $_sconfig;
    protected $_frontend;
    
    const DEFAULT_DOWNLOADER_PATH = "downloader";
    
    /**
     * Default single config filename
     *
     * 'cache.cfg'
     */
    const DEFAULT_SCONFIG_FILENAME = 'cache.cfg';
    
    public function _construct()
    {
        parent::_construct();
        $this->initialize();

    }
    
    public function install($file)
    {
        try {
            Mage_Connect_Command::getCommands();
            $cmd = Mage_Connect_Command::getInstance('install-file');
            $cmd->setSconfig($this->_sconfig);
            $cmd->setConfigObject($this->_config);
            $cmd->setFrontendObject($this->_frontend);
            $params = array($file);
            $cmd->run('install-file', array(), $params);
        } catch (Exception $ex) {
            Mage::register('temandoinstaller_errors', array('There was an error installing'));
            return false;
        }
        
        if ($cmd->ui()->hasErrors()) {
            $errors = array();
            foreach ($cmd->ui()->getErrors() as $error) {
                $errors[] = $error[1];
            }
            
            Mage::register('temandoinstaller_errors', $errors);
            return false;
        }
        
        return true;
    }
    
    public function uninstall($module)
    {
        try {
            Mage_Connect_Command::getCommands();
            $cmd = Mage_Connect_Command::getInstance('uninstall');
            $cmd->setSconfig($this->_sconfig);
            $cmd->setConfigObject($this->_config);
            $cmd->setFrontendObject($this->_frontend);
            $package = $this->_sconfig->getPackageObject('community', $module);
            $contents = $package->getContents();
            $params = array('community', $module);
            $cmd->run('uninstall', array(), $params);
        } catch (Exception $ex) {
            Mage::register('temandoinstaller_errors', array('There was an error uninstalling'));
            return false;
        }
                
        if ($cmd->ui()->hasErrors()) {
            $errors = array();
            foreach ($cmd->ui()->getErrors() as $error) {
                $errors[] = $error[1];
            }
            
            Mage::register('temandoinstaller_errors', $errors);
            return false;
        }
        
        //clean the directories
        $targetPath = rtrim($this->_config->magento_root, "\\/");
        // @codingStandardsIgnoreStart
        foreach ($contents as $file) {
            $fileName = basename($file);
            $filePath = dirname($file);
            $dest = $targetPath . DIRECTORY_SEPARATOR . $filePath . DIRECTORY_SEPARATOR . $fileName;
            $this->removeEmptyDirectory(dirname($dest));
        }
        // @codingStandardsIgnoreEnd
        return true;
    }
    
    /**
     * Return the single config class
     * @return Mage_Connect_Singleconfig
     */
    public function getSingleConfig()
    {
        return $this->_sconfig;
    }
    
    /**
     * Remove empty directories recursively up
     *
     * @param string $dir
     * @param Mage_Connect_Ftp $ftp
     */
    protected function removeEmptyDirectory($dir)
    {
        try {
            // @codingStandardsIgnoreStart
            if (@rmdir($dir)) {
                $this->removeEmptyDirectory(dirname($dir));
            }// @codingStandardsIgnoreEnd   
        } catch (Exception $ex) {
            return false;
        }
    }
    
    protected function initialize()
    {
        if (!$this->_config) {
            // @codingStandardsIgnoreStart
            $this->_config = new Mage_Connect_Config();
            // @codingStandardsIgnoreEnd
            $this->_config->magento_root =  Mage::getBaseDir() .
            DIRECTORY_SEPARATOR.self::DEFAULT_DOWNLOADER_PATH . DIRECTORY_SEPARATOR.'..';
        }
        
        if (!$this->_sconfig) {
            // @codingStandardsIgnoreStart
            $this->_sconfig = new Mage_Connect_Singleconfig(
                Mage::getBaseDir() . DIRECTORY_SEPARATOR .
                self::DEFAULT_DOWNLOADER_PATH . DIRECTORY_SEPARATOR .
                self::DEFAULT_SCONFIG_FILENAME
            );
        }
        
        if (!$this->_frontend) {
            $this->_frontend = new Mage_Connect_Frontend();
        }
        // @codingStandardsIgnoreEnd
    }
}
