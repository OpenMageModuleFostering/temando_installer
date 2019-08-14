<?php
/**
 * Adminhtml Installer
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */
class Temando_Installer_Block_Adminhtml_Installer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'temandoinstaller';
        $this->_controller = 'adminhtml_installer';
        $this->_headerText = $this->__('Manage Temando');
        $this->_addButtonLabel = $this->__('Install Temando');
//        if (Mage::helper('temandoinstaller')->getTemandoVersionNumber()) {
//            $this->_addButton('connectionSettings', array(
//                'label' => $this->__('Test Connection Settings'),
//                'id' => 'connectionSettings',
//                'onclick' => "window.location = '" . $this->getUrl('adminhtml/temandoinstaller_installer/testConnectionSettings') . "'",
//                'value' => '',
//                'class' => 'go',
//            ));
//        }
        parent::__construct();
        if (Mage::helper('temandoinstaller')->getTemandoVersionNumber()) {
            $this->removeButton('add');
        }
    }
}
