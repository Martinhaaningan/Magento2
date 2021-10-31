<?php

namespace Improving\MartinHaaning\Console\Command;


use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;


class OpretKatCommand extends Command
{
    protected $_category;
    protected $_categoryRepository;
    private $state;
    const NAME ='name';

    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('improving:martinhaaning:opretKat');
        $this->setDescription('improving:martinhaaning:opretKat');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Kategori navn'
        );
    }

    public function __construct(State $state,
                                Category $category,
                                CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->state = $state;
        $this->_category = $category;
        $this->_categoryRepository = $categoryRepository;

    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws LocalizedException
     */

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption(self::NAME);

        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $output->writeln('OpretKatCommand opretter nu:'. $name);
        $category = $this->_category;
        $category->setName($name);
        $category->setParentId(2);
        $category->setIsActive(true);
        $this->_categoryRepository->save($category);
    }


}

