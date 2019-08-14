<?php

/**
 *
 */
class Temando_Installer_Model_Tester extends Mage_Core_Model_Abstract
{
    
    protected $_helper;
    
    public function _construct()
    {
        parent::_construct();
        $this->initialize();

    }
    
    public function testSettings()
    {
        $tables = $this->checkTemandoSchema();
        $details = $this->testClientDetails();

        if(!$tables || !$details) {
            Mage::getSingleton('adminhtml/session')->addError('Could not complete get quotes test.');
        } else {
            $quotes = $this->testGetQuotes();
        }
        return;
    }
    
    protected function testGetQuotes() {
        try {
           $result = $this->_helper->loadCheapestQuote();
            if($result) {
               Mage::getSingleton('adminhtml/session')->addSuccess('Cheapest quote: ' . $result->getDescription());
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError('Error connecting to the api: ' . $e->getMessage());
        }
        return;
    }
    
    public function testClientDetails() {
        $result = $this->_helper->testAccountDetails();
        if(!$result) {
             Mage::getSingleton('adminhtml/session')->addSuccess('API Connection successful!');
            return true;
        } else {
            Mage::getSingleton('adminhtml/session')->addError('Error connecting to the api: ' . $result);
            return false;
        }
        
    }

    public function checkTemandoSchema()
    {
        $tableError = 0;
        if(!$this->checkTemandoWarehouse()) {
            Mage::getSingleton('adminhtml/session')->addNotice('Please add a warehouse to locations.');
            $tableError++;
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess('Warehouses: ' . $this->checkTemandoWarehouse());
        }
        if(!$this->checkTemandoRule()) {
            Mage::getSingleton('adminhtml/session')->addNotice('Please add a rule to the rule engine.');
            $tableError++;
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess('Rules: ' . $this->checkTemandoRule());
        }
        if($tableError == 0) {
            return true;
        } else { 
            return false;
        }
    }
    
    public function checkTemandoWarehouse()
    {
        $warehouses = 0;
        $warehouseCollection = $this->_helper->getTemandoWarehouses();
        foreach ($warehouseCollection as $warehouse) {
            $warehouses++;
        }
        return $warehouses;
    }
    
    public function checkTemandoRule()
    {
        $rules = 0;
        $ruleCollection = $this->_helper->getTemandoRules();
        foreach ($ruleCollection as $rule) {
            $rules++;
        }
        return $rules;
    }

    protected function initialize()
    {
        if(!$this->_helper) {
            $this->_helper = Mage::helper('temandoinstaller');
        }
    }
    
}
