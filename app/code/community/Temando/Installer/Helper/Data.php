<?php
/**
 * Helper Data
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */

class Temando_Installer_Helper_Data extends Mage_Core_Helper_Abstract
{

    const TMD_VERSION_STARTER       = '1.0.0';
    const TMD_VERSION_PROFFESIONAL  = '2.0.0';
    const TMD_VERSION_BUSINESS      = '3.0.0';
    const TMD_VERSION_ENTERPRISE    = '4.0.0';
    const TEMANDO_VERSION_STARTER = 'magento-starter';
    const TEMANDO_VERSION_PROFESSIONAL = 'magento-professional';
    const TEMANDO_VERSION_BUSINESS = 'magento-business';
    const TEMANDO_VERSION_ENTERPRISE = 'magento-enterprise';
    const TMD_MODULE_NAME = 'Temando_Temando';
    
    protected $_tmdVersion = null;
    
    /**
     * Gets version of main Temando_Temando extension
     * 
     * @return string
     */
    public function getTemandoVersion()
    {
        if (!$this->_tmdVersion) {
            $version = (string) Mage::getConfig()->getNode()->modules->Temando_Temando->version;
            if (version_compare($version, self::TMD_VERSION_PROFFESIONAL, '<')) {
                //anything less than 2.0.0
                $this->_tmdVersion = self::TMD_VERSION_STARTER;
            }
            
            if (version_compare($version, self::TMD_VERSION_PROFFESIONAL, '>=')
                && version_compare($version, self::TMD_VERSION_BUSINESS, '<')) {
                //equals or greater than 2.0.0 but less then 3.0.0
                $this->_tmdVersion = self::TMD_VERSION_PROFFESIONAL;
            }
            
            if (version_compare($version, self::TMD_VERSION_BUSINESS, '>=')
                && version_compare($version, self::TMD_VERSION_ENTERPRISE, '<')) {
            //equals or greater than 2.0.0 but less then 3.0.0
                $this->_tmdVersion = self::TMD_VERSION_BUSINESS;
            }
            
            if (version_compare($version, self::TMD_VERSION_ENTERPRISE, '>=')) {
                $this->_tmdVersion = self::TMD_VERSION_ENTERPRISE;
            }
            
            if (!$version) {
                $this->_tmdVersion = null;
            }
        }
        
        return $this->_tmdVersion;
    }
    
    /**
     * Retrieves an element from the module configuration data.
     *
     * @param string $field
     */
    public function getConfigData($field)
    {
        $path = 'temandoinstaller/' . $field;
        return Mage::getStoreConfig($path);
    }
    
    public function getTemandoVersionName()
    {
        $versionName = null;
        switch ($this->getTemandoVersion()) {
            case self::TMD_VERSION_STARTER:
                $versionName = 'starter';
                break;
            case self::TMD_VERSION_PROFFESIONAL:
                $versionName = 'professional';
                break;
            case self::TMD_VERSION_BUSINESS:
                $versionName = 'business';
                break;
            case self::TMD_VERSION_ENTERPRISE:
                $versionName = 'enterprise';
                break;
        }
        
        return $versionName;
    }
    
    public function getVersions()
    {
        return array(
            self::TEMANDO_VERSION_STARTER,
            self::TEMANDO_VERSION_PROFESSIONAL,
            self::TEMANDO_VERSION_BUSINESS,
            self::TEMANDO_VERSION_ENTERPRISE);
    }
    
    public function getVersionNameFromSlug($slug)
    {
        $strippedSlug = substr($slug, 0, -3);
        $names = array(
            self::TEMANDO_VERSION_STARTER => 'Temando Starter',
            self::TEMANDO_VERSION_PROFESSIONAL => 'Temando Professional',
            self::TEMANDO_VERSION_BUSINESS => 'Temando Business',
            self::TEMANDO_VERSION_ENTERPRISE => 'Temando Enterprise'
        );
        return $names[$strippedSlug];
    }
    
    public function getTemandoVersionNumber()
    {
        $version = (string) Mage::getConfig()->getNode()->modules->Temando_Temando->version;
        return $version;
    }
    
    public function getTemandoModuleName()
    {
        return self::TMD_MODULE_NAME;
    }
    
    public function getTemandoToken()
    {
        return $this->getConfigData('general/token');
    }
    
    public function getTemandoWarehouses()
    {
        return Mage::getResourceModel('temando/warehouse_collection');
    }
    
    public function getTemandoRules()
    {
        return Mage::getResourceModel('temando/rule_collection');
    }
    
    public function testAccountDetails()
    {
        try {
            $api = $this->connect();
            $result = $api->getLocations(array('clientId' => Mage::helper('temando')->getClientId()));
            if (!$result) {
                return 'Could not connect to the api';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        
        return false;
    }
    
    /**
     * @return Temando_Temando_Model_Api_Client
     */
    public function connect()
    {
        $api = Mage::getModel('temando/api_client');
        switch ($this->getTemandoVersion()) {
            case self::TMD_VERSION_BUSINESS:
                $api = $api->connect($this->getTemandoProfile());
                break;
            default:
                $api = $api->connect(
                    Mage::helper('temando')->getConfigData('general/username'),
                    Mage::helper('temando')->getConfigData('general/password'),
                    Mage::helper('temando')->getConfigData('general/sandbox')
                );
                break;
        }
        
        return $api;
    }
    
    /**
     * Returns cheapest available quote for dummy order
     * 
     * @return null|Temando_Temando_Model_Quote
     */
    public function loadCheapestQuote()
    {
        //get first origin location
        $origins = $this->getTemandoWarehouses();
        foreach ($origins as $origin) {
            continue;
        }

        if (!$origin) {
            return null;
        }
        
        /** @var $origin Temando_Temando_Model_Warehouse */
        $allowedCarriers = explode(',', Mage::getStoreConfig('carriers/temando/allowed_methods'));
        $request = Mage::getModel('temando/api_request');
        /** @var $request Temando_Temando_Model_Api_Request */
        
        switch ($this->getTemandoVersion()) {
            case self::TMD_VERSION_BUSINESS:
                $request->setConnectionParams($origin->getTemandoProfile());
                break;
            default:
                $request
                    ->setUsername(Mage::helper('temando')->getConfigData('general/username'))
                    ->setPassword(Mage::helper('temando')->getConfigData('general/password'))
                    ->setSandbox(Mage::helper('temando')->getConfigData('general/sandbox'));
                break;
        }
                
        $request
            ->setMagentoQuoteId(100000000 + mt_rand(0, 100000))
            ->setDestination(
                'AU',
                '2000',
                'SYDNEY',
                '123 Pitt Street'
            )

            ->setOrigin($origin->getName())
            ->setItems($this->getTestBox())
            ->setReady()
            ->setAllowedCarriers($allowedCarriers);

        $quotes = $request->getQuotes()->getItems();
        return Mage::helper('temando/functions')->getCheapestQuote($quotes);
    }
    
    /**
     * Creates a test box
     * @return array
     */
    public function getTestBox()
    {
        $box = Mage::getModel('temando/box');
        /** @var $box Temando_Temando_Model_Box */
        $box
            ->setComment('My Package')
            ->setQty('1')
            ->setValue('10')
            ->setLength('10')
            ->setWidth('10')
            ->setHeight('10')
            ->setMeasureUnit(Temando_Temando_Model_System_Config_Source_Unit_Measure::CENTIMETRES)
            ->setWeight('100')
            ->setWeightUnit(Temando_Temando_Model_System_Config_Source_Unit_Weight::GRAMS)
            ->setPackaging(Temando_Temando_Model_System_Config_Source_Shipment_Packaging::BOX);
            /** ->setFragile($package['fragile'])
            ->setArticles($package['articles']); */
        return array($box);
    }
    
    protected function getTemandoProfile()
    {
        return array(
            'sandbox'   => Mage::helper('temando')->getConfigData('general/sandbox'),
            'clientid'  => Mage::helper('temando')->getConfigData('general/client'),
            'username'  => Mage::helper('temando')->getConfigData('general/username'),
            'password'  => Mage::helper('temando')->getConfigData('general/password'),
            'payment'   => Mage::helper('temando')->getConfigData('general/payment_type'),
        );
    }
}
