<?php

namespace Improving\Productimportcsv\Console\Command;
use Improving\productimportcsv\Helper\Helper;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;

use Magento\Eav\Model\AttributeFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Eav\Setup\EavSetupFactory;




class ImporterCommand extends Command
{
    const PATH = "path";
    /**
     * Configures the current command.
     */
    private $helperData;
    private $state;

    public function configure()
    {
        $this->setName('improving:productimportcsv:importer');
        $this->setDescription('improving:productimportcsv:importer');
        $this->addOption(
            self::PATH,
            null,
            InputOption::VALUE_REQUIRED,
            'File Path'
        );
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     */
    public function __construct(State $state,
                                helper $helperData,
                                ProductAttributeRepositoryInterface $attributeRepository,
                                attributeFactory $attributeFactory,
                                ProductInterfaceFactory $productFactory,
                                eavSetupFactory $eavSetupFactory)
    {
        parent::__construct();
        $this->state = $state;
        $this->helperData = $helperData;
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactory = $attributeFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */


    public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $path = $input->getOption(self::PATH);

        $csvAttributes = $this->helperData->getAttributes($path);
        $newAttributes = $this->helperData->createAttributes($csvAttributes);

        foreach($newAttributes as $newAttr) {
            $output->writeln('New attributes created: ' . $newAttr);
        }
        $csvProducts = $this->helperData->getProducts($path);

        $this->helperData->createConfigurable($csvProducts);


        //$this->helperData->createOptions($csvAttributes, $csvProducts);


    }


}

