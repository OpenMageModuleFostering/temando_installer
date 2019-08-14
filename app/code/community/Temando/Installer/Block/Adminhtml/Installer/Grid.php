<?php
/**
 * Adminhtml Installer Grid
 *
 * @package     Temando_Installer
 * @author      Temando Magento Team <marketing@temando.com>
 */
class Temando_Installer_Block_Adminhtml_Installer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('temandoinstaller/installer')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => Mage::helper('temandoinstaller')->__('Name'),
            'index'  => 'name',
        ));
        
            $this->addColumn('version', array(
            'header' => Mage::helper('temandoinstaller')->__('Version'),
            'index'  => 'version',
        ));
        $this->addColumn('token', array(
            'header' => Mage::helper('temandoinstaller')->__('Token'),
            'index'  => 'token',
        ));
        
        $this->addColumn('install_date', array(
            'header' => Mage::helper('temandoinstaller')->__('Install date'),
            'type' => 'datetime',
            'index'  => 'install_date',
        ));
        
        $this->addColumn('update_date', array(
            'header' => Mage::helper('temandoinstaller')->__('Update date'),
            'type' => 'datetime',
            'index'  => 'update_date',
        ));
            
            $this->addColumn('update_details', array(
            'header' => Mage::helper('temandoinstaller')->__('Update available'),
            'index'  => 'update_details',
            'filter' => false,
        ));
            
        parent::_prepareColumns();
    }
    
    public function _prepareMassaction()
    {
        parent::_prepareMassaction();
        $this->getMassactionBlock()->addItem('uninstall', array(
            'label'=> Mage::helper('temandoinstaller')->__('Uninstall'),
            'url'  => $this->getUrl('*/*/uninstall'),
            'confirm' => Mage::helper('temandoinstaller')->__('Are you sure you want to uninstall the Temando module?'),
        ));

        $this->getMassactionBlock()->addItem('update', array(
            'label' => Mage::helper('temandoinstaller')->__('Update'),
            'url'   => $this->getUrl('*/*/update'),
            'confirm' => Mage::helper('temandoinstaller')->__('Are you sure you want to update the Temando module?'),
        ));

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setUseSelectAll(false);
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return;
    }
}
