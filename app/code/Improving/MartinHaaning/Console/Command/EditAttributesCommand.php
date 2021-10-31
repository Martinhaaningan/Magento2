<?php

namespace Improving\MartinHaaning\Console\Command;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\OptionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class EditAttributesCommand extends Command
{
    const CODE = 'code';
    const OPTION = 'option';
    private $state;
    protected $attribute;
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var attributeOptionManagement
     */
    private $attributeOptionManagementInterface;
    /**
     * @var ProductAttributeRepositoryInterface
     */

    /**
     * Configures the current command.
     */
    public function configure()
    {
        $this->setName('improving:martinhaaning:editAttributes');
        $this->setDescription('improving:martinhaaning:editAttributes');
        $this->addOption(
            self::CODE,
            null,
            InputOption::VALUE_REQUIRED,
            'Attribute CODE'
        );
        $this->addOption(
            self::OPTION,
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Option'
        );
    }

    public function __construct(State                               $state,
                                EavSetupFactory                     $eavSetupFactory,
                                StoreManagerInterface               $storeManager,
                                ProductAttributeRepositoryInterface $attributeRepository,
                                OptionFactory                       $optionFactory,
                                attributeOptionManagementInterface  $attributeOptionManagementInterface
    )
    {
        parent::__construct();
        $this->state = $state;
        $this->attributeRepository = $attributeRepository;
        $this->optionFactory = $optionFactory;
        $this->attributeOptionManagementInterface = $attributeOptionManagementInterface;
    }

    /**
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws LocalizedException
     */
    public function execute(InputInterface  $input,
                            OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $code = $input->getOption(self::CODE);
        $options = $input->getOption(self::OPTION);

        $attr = $this->attributeRepository->get($code);
        $oldOptions = $attr->getOptions();

        /* foreach ($oldOptions as $oldOption) {
            echo "<pre>";
            var_dump($oldOption->debug());
            echo "</pre>";
        } */

        foreach ($options as $option) {
            $isOld = false;

            foreach ($oldOptions as $oldOption) {
                if ($oldOption->getLabel() == $option) {
                    $isOld = true;
                }
            }

            $output->writeln('EditAttributesCommand');

            if (!$isOld) {
                $realOption = $this->optionFactory->create();
                $realOption->setData(['label' => $option, 'is_default' => true]);
                $attr->setOptions([$realOption]);
                $this->attributeRepository->save($attr);
            } else {
                echo "This option already exists!";
            }
        }
    }
}

