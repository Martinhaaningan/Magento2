<?php

namespace Improving\Martin\Console\Command;

use Improving\Martin\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\TestFramework\Event\Magento;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloworldCommand extends Command
{
    private $helperData;
    private $productRepository;


    public function configure()
    {
        $this->setName('improving:martin:helloworld');
        $this->setDescription('improving:martin:helloworld');
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     */
    private $state;

    public function __construct(State                      $state,
                                Data                       $helperData,
                                ProductRepositoryInterface $productRepository
    ) {
        $this->state = $state;
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);
        $output->writeln('HelloworldCommand');

        $productid = $this->helperData->getGeneralConfig('select_product');

        $product = $this->productRepository->getById($productid);
        echo $product->getName();
    }


}

