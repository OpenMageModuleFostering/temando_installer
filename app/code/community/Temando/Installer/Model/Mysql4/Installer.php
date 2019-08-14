<?php

class Temando_Installer_Model_Mysql4_Installer extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('temandoinstaller/installer', 'id');
    }
    
}
