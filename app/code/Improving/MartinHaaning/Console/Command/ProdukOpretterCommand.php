<?php

namespace Improving\MartinHaaning\Console\Command;

use Exception;
use Magento\Bundle\Model\OptionFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Console\CommandListInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Improving\MartinHaaning\Helper\Data;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;


class ProdukOpretterCommand extends Command
{
    const NAME ='name';
    const PRICE ='price';
    const CODE ='code';
    const OPTIONS = 'options';

    private $productRepository;
    private $state;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;
    private $helperData;


    /**
     * Configures the current command.
     */
    public function configure()
    {

        $this->setName('improving:martinhaaning:produkOpretter');
        $this->setDescription('improving:martinhaaning:produkOpretter');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Produkt Navn'
        );
        $this->addOption(
            self::PRICE,
            null,
            InputOption::VALUE_REQUIRED,
            'Produktets pris'
        );
        $this->addOption(
            self::CODE,
            null,
            InputOption::VALUE_REQUIRED,
            'Attribute code'
        );
        $this->addOption(
            self::OPTIONS,
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Attribute options'
        );

    }
    public function __construct(State                         $state,
                                data $helperData,
                                ProductInterfaceFactory       $productFactory,
                                ProductRepositoryInterface    $productRepository,
                                OptionFactory $optionFactory)
    {
        parent::__construct();
        $this->state = $state;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->helperData = $helperData;


    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws \Magento\Framework\Exception\LocalizedException
     */


    public function execute(InputInterface $input, OutputInterface $output)
    {
        //https://www.mageplaza.com/devdocs/magento-2-create-product-programmatically.html
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $name = $input->getOption(self::NAME);
        $price = (float)$input->getOption(self::PRICE);
        $code = $input->getOption(self::CODE);
        $options = $input->getOption(self::OPTIONS);

        try {

            $product = $this->productFactory->create();

            $product->setTypeId('simple'); //setTypeId - skal være sat ellers virker det ikke med prisen
            $product->setName($name);
            $product->setSku($name);
            $product->setPrice($price);
            $product->setWebsiteIds([1]);
            $product->setAttributeSetId(4);

            if (!empty($options)) {
                $optionIds = [];
                foreach ($options as $option) {
                    $optionIds[] = $this->helperData->createOrGetId($code, $option);
                    $output->writeln('<info>New Option was created: ' . $option . '</info>');
                }

                //Ved setCustomAttribute er det option ID'er, IKKE label
                $product->setCustomAttribute($code, implode(',',$optionIds));
            }
            $this->productRepository->save($product);

        } catch (Exception $e){
            echo 'failed to save product';
            echo $e->getMessage();
        }
        $output->writeln('<info>Produktets navn er: `'. $name . '` Prisen er: '. $price .'</info>');
        return true;
    }


}

