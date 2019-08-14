<?php

class Temando_Installer_Block_Adminhtml_System_Config_Form_Button_Manage extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);
        $html = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setType('button')->setClass('scalable go')
                ->setLabel(Mage::helper('temandoinstaller')->__('Manage Temando Installation'))
                ->setOnClick('setLocation(\'' . $this->getUrl('temandoinstaller/adminhtml_installer') .'\')')
                ->setTitle(Mage::helper('temandoinstaller')->__('Manage Temando Installation'))
                ->toHtml();
        return $html;
    }

}
