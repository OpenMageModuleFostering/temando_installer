<?php
/**
 * Api Rest Client
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 * @method StdClass getLastResponse()
 */
class Temando_Installer_Model_Api_Rest_Client
    extends Mage_Core_Model_Abstract {
    
    /**
     * Auth token
     * @var string 
     */
    protected $_token = null;
    
    /**
     * The HTTP Client
     * @var Varien_Http_Client
     */
    protected $_client = null;
    
    /**
     * Service Uri
     * @var string
     */
    protected $_uri = null;


    public function _construct()
    {
        parent::_construct();
        $this->_prepareClient();
    }
    
    
    public function getService($request, $jsonDecode = true)
    {
        if (!$this->_validate($request)) {
            return false;
        }
        try {
            if ($jsonDecode) {
                $this->_client
                    ->setHeaders(array('Authorization' => 'Bearer ' . $this->_token))
                    ->setUri($this->_uri);
            } else {
                $this->_client
                    ->setUri($this->_uri . '?access_token=' . $this->_token);
            }
            
            /**
            * Getting response via raw body and decoding coz of a bug in Zend yielding
            * exception when ->getBody() is used to decode and return response
            */
            
            $rawBody = $this->_client->request(Varien_Http_Client::GET)->getRawBody();
        
            if ($jsonDecode) {
                $response = Mage::helper('core')->jsonDecode($rawBody, Zend_Json::TYPE_OBJECT);
            } else {
                $response = $rawBody;
            }

            if ($this->_client->getLastResponse()->getStatus() == 200 && count($response)) {
                //all good
            } else {
                Mage::logException(new Exception($this->_client->getLastResponse()));
                Mage::log($this->_client->getLastRequest(), null, 'tmd-http-request.log', true);
                Mage::log($this->_client->getLastResponse(), null, 'tmd-http-response.log', true);
            }
            return $response;
        
        } catch (Exception $e) {
            Mage::logException($e);
            if (!$this->_sandbox) {
                Mage::log($this->_client->getLastRequest(), null, 'tmd-http-request.log', true);
                Mage::log($this->_client->getLastResponse(), null, 'tmd-http-response.log', true);
            }
            return false;
        }
    }
      
    /**
     * Validate data before sending request
     * 
     * @return boolean
     */
    protected function _validate($request)
    {
        if (!$this->_uri) {
            $this->_uri = $request['uri'];
        }
        if (!$this->_uri) {
            Mage::logException(new Exception(__CLASS__.': missing request url.'));
            return false;
        }
        if (!$this->_token) {
            Mage::logException(new Exception(__CLASS__.': missing authorisation token.'));
            return false;
        }
        return true;
    }
    
    /**
     * Initializes http client to communicate with Temando REST Api
     * 
     * @return \Temando_Installer_Model_Api_Rest_Client 
     */
    protected function _prepareClient()
    {
        if (!$this->_client) {
            $this->_client = new Varien_Http_Client();
        }
        if (!$this->_token) {
            $this->_token = Mage::helper('temandoinstaller')->getTemandoToken();
        }
        return $this;
    }
}
