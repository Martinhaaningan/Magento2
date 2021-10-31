<?php

namespace Improving\Martin\Controller\Index;

class Config extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Improving\Martin\Helper\Data $helperData,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository

    )
    {
        $this->helperData = $helperData;

        return parent::__construct($context);
    }

    public function execute()
    {

        // TODO: Implement execute() method.


        echo $this->helperData->getGeneralConfig('enable');
        echo $this->helperData->getGeneralConfig('select_product');
        exit();

    }
}
