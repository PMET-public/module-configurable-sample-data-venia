<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableSampleDataVenia\Setup;


use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Setup configurable product
 */
class Product
{

    /** @var \Magento\Eav\Model\Config  */
    private $eavConfig;

    /**
     * @var \Magento\ImportExport\Model\Import
     */
    private $importModel;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    private $csvSourceFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;


    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\ImportExport\Model\Import $importModel
     * @param \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\ImportExport\Model\Import $importModel,
        \Magento\ImportExport\Model\Import\Source\CsvFactory $csvSourceFactory,
        ReadFactory $readFactory,
        \Magento\Framework\Component\ComponentRegistrar $componentRegistrar
    ) {
        $this->eavConfig = $eavConfig;
        $this->importModel = $importModel;
        $this->csvSourceFactory = $csvSourceFactory;
        $this->readFactory = $readFactory;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @inheritdoc
     */
    public function install($moduleName, $fileName)
    {
        $this->eavConfig->clear();
        $importModel = $this->importModel;
        $importModel->setData(
            [
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'import_images_file_dir' => 'pub/media/catalog/product',
                Import::FIELD_NAME_VALIDATION_STRATEGY =>
                    ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS
            ]
        );

        $source = $this->csvSourceFactory->create(
            [
                'file' => $fileName,
                'directory' => $this->readFactory->create(
                    $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName)
                )
            ]
        );

        $currentPath = getcwd();
        chdir(BP);
        $importModel->validateSource($source);
        $importModel->importSource();

        chdir($currentPath);

        $this->eavConfig->clear();
    }
}
