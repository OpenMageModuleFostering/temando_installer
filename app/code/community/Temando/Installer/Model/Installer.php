<?php
/**
 * Installer
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */

class Temando_Installer_Model_Installer extends Mage_Core_Model_Abstract
{
    const TEMANDO_FINANCE_API = 'https://finance.temando.com/api/v1/user/subscriptions';
    
    public function _construct()
    {
        parent::_construct();
        $this->_init('temandoinstaller/installer');
    }
    
    /**
     * Gets the highest available service and returns it
     * @return boolean|object
     */
    public function getCurrentService()
    {
        $request = array();
        $request['uri'] = self::TEMANDO_FINANCE_API;
        $helper = Mage::helper('temandoinstaller');
        /** @var $helper Temando_Installer_Helper_Data */
        $api = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $api Temando_Installer_Model_Api_Rest_Client */
        $versions = $helper->getVersions();
        $response = $api->getService($request);
        if (!isset($response->subscriptions)) {
            return false;
        }
        
        $services = array();
        foreach ($response->subscriptions as $subscription) {
            if (!isset($subscription->links->service->href)) {
                continue;
            }
            
            if (!$subscription->active) {
                continue;
            }
            
            //collect available services
            $services[] = $this->getService($subscription->links->service->href);
        }
        
        $currentService = null;
        foreach ($versions as $version) {
            foreach ($services as $service) {
                if (substr($service->slug, 0, -3) == $version) {
                    $currentService = $service;
                }
            }
        }
        
        return $currentService;
    }
    
    /**
     * Downloads the package
     * @param object $service
     * @return string|boolean
     */
    public function downloadPackage($service)
    {
        if (!isset($service->links->software_latest_release->href)) {
            return false;
        }
        
        $rawUrl = $this->getLatestRelease($service->links->software_latest_release->href)->raw_url;
        if (!isset($rawUrl)) {
            return false;
        }
        
        $package = $this->getPackage($rawUrl);
        if (!$package) {
            return false;
        }
        
        $file = 'Temando_Temando.tgz';
        $fileTemp = Mage::getBaseDir() . DS . "var/" . uniqid() . $file;
        // @codingStandardsIgnoreStart
        if (!file_put_contents($fileTemp, $package)) {
        // @codingStandardsIgnoreEnd
            //there is an error saving the file on disk
            return false;
        } else {
            //everything is awesome
            return $fileTemp;
        }
    }

    /**
     * Gets the package
     * @param string $uri
     */
    public function getPackage($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $packageRequest = array();
        $packageRequest['uri'] = $uri;
        $packageResponse = $serviceApi->getService($packageRequest, false);
        //validate the download here
        return $packageResponse;
    }

    /**
     * Gets the service object
     * @param string $uri
     * @return object
     */
    public function getService($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $serviceRequest = array();
        $serviceRequest['uri'] = $uri;
        $serviceResponse = $serviceApi->getService($serviceRequest);
        if (!isset($serviceResponse->services)) {
            return;
        }
        
        return $serviceResponse->services;
    }
    
    /**
     * Returns the product object
     * @param string $uri
     * @return object
     */
    public function getProduct($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $productRequest = array();
        $productRequest['uri'] = $uri;
        $productResponse = $serviceApi->getService($productRequest);
        if (!isset($productResponse->softwares->software)) {
            return;
        }
        
        return $productResponse->softwares->software;
    }
    
    /**
     * Returns the latest release object
     * @param string $uri
     * @return object
     */
    public function getLatestRelease($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $releaseRequest = array();
        $releaseRequest['uri'] = $uri;
        $releaseResponse = $serviceApi->getService($releaseRequest);
        if (!isset($releaseResponse->release)) {
            return;
        }
        
        return $releaseResponse->release;
    }
    
    /**
     * Returns the service slug name
     * @param string $uri
     * @return string
     */
    public function getServiceSlug($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $serviceRequest = array();
        $serviceRequest['uri'] = $uri;
        $serviceResponse = $serviceApi->getService($serviceRequest);
        if (!isset($serviceResponse->services->slug)) {
            return;
        }
        
        return $serviceResponse->services->slug;
    }
    
    /**
     * Gets the latest version of the service
     * @param string $uri
     * @return string
     */
    public function getServiceLatestVersion($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $serviceRequest = array();
        $serviceRequest['uri'] = $uri;
        $serviceResponse = $serviceApi->getService($serviceRequest);
        if (!isset($serviceResponse->services->links->software_latest_release->version)) {
            return;
        }
        
        return $serviceResponse->services->links->software_latest_release->version;
    }
    
    /**
     * Returns the latest version uri
     * @param string $uri
     * @return string
     */
    public function getServiceLatestVersionUri($uri)
    {
        $serviceApi = Mage::getModel('temandoinstaller/api_rest_client');
        /** @var $serviceApi Temando_Installer_Model_Api_Rest_Client */
        $serviceRequest = array();
        $serviceRequest['uri'] = $uri;
        $serviceResponse = $serviceApi->getService($serviceRequest);
        if (!isset($serviceResponse->services->links->software_latest_release->href)) {
            return;
        }
        
        return $serviceResponse->services->links->software_latest_release->href;
    }
}
