<?php

$installer = $this;
$installer->startSetup();

/**
 * Remove unused files from the Adminhtml controller directory
 *
 * This is because of Magento security patch SUPEE-6788
 * Supresses errors when trying to delete.
 */
$temandoFiles = array (
    'InstallerController',
);

foreach ($temandoFiles as $temandoFile) {
    $directory = Mage::getModuleDir('controllers', 'Temando_Installer').DS.'Adminhtml';
    $filename = $directory.DS.$temandoFile.'.php';
    // @codingStandardsIgnoreStart
    if (!file_exists($filename)) {
        continue;
    }

    @unlink($filename);
    // @codingStandardsIgnoreEnd
}

$installer->endSetup();
