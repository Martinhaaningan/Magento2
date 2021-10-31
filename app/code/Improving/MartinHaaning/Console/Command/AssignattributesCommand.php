<?php

namespace Improving\MartinHaaning\Console\Command;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;



class AssignattributesCommand extends Command
{
    private $state;
    const ID ='id';
    const VAL = "value";
    const ATTR = "attribute";
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('improving:martinhaaning:assignattributes');
        $this->setDescription('improving:martinhaaning:assignattributes');
        $this->addOption(
            self::ID,
            null,
            InputOption::VALUE_REQUIRED,
            'Produkt ID'
        );
        $this->addOption(
            self::ATTR,
            null,
            InputOption::VALUE_REQUIRED,
            'Attribute ID'
        );
        $this->addOption(
            self::VAL,
            null,
            InputOption::VALUE_REQUIRED,
            'Attribute value'
        );
    }
    public function __construct(State                      $state,
                                ProductRepositoryInterface $productRepository,
                                ProductAttributeRepositoryInterface $attributeRepository)
    {
        parent::__construct();
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;


    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $id = $input->getOption(self::ID);
        $attr = $input->getOption(self::ATTR);
        $value = $input->getOption(self::VAL);

        $attribute = $this->attributeRepository->get($attr);

        $product = $this->productRepository->getById($id);
        $product->setCustomAttriabute($attr, $value);

        $output->writeln('AssignattributesCommand');
    }


}

