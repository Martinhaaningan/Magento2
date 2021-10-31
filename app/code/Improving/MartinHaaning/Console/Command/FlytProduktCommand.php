<?php

namespace Improving\MartinHaaning\Console\Command;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

class FlytProduktCommand extends Command
{
    protected $_category;
    protected $_categoryRepository;
    protected $productRepository;
    protected $state;
    const ID ='id';
    const CATS = 'categories';
    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('improving:martinhaaning:flytProdukt');
        $this->setDescription('improving:martinhaaning:flytProdukt');
        $this->addOption(
            self::ID,
            null,
            InputOption::VALUE_REQUIRED,
            'Produkt ID'
        );
        $this->addOption(
            self::CATS,
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Kategori ID'
        );
    }

    public function __construct(State $state,
                                Category $category,
                                CategoryRepository $categoryRepository,
                                ProductRepositoryInterface $productRepository,
        CategoryLinkManagementInterface $categoryLinkManagementInterface
)
    {
        parent::__construct();
        $this->state = $state;
        $this->_category = $category;
        $this->_categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagementInterface;


    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @param $productRepository
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws NoSuchEntityException
     */

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $id = $input->getOption(self::ID);
        $product = $this->productRepository->getById($id);

        $categories = $input->getOption(self::CATS);

        $this->categoryLinkManagement->assignProductToCategories(
            $product->getSku(),
            $categories
        );
        $output->writeln
        ('FlytProduktCommand flytter ' . $product->getName() . 'til kategorierne ');
        foreach ($categories as &$value) {
            $category = $this->_categoryRepository->get($value);
            echo $category->getName() . "\n";
        }
    }



}

