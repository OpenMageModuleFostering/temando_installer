<?php
/**
 * Mysql4 Installer Collection
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */

class Temando_Installer_Model_Resource_Installer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('temandoinstaller/installer');
    }
}
