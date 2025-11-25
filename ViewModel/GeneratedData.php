<?php
/*
 * Copyright © 2025 Studio Raz. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SR\LlmsTxt\ViewModel;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use SR\LlmsTxt\Model\StoreDataCollector;

class GeneratedData implements ArgumentInterface
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly StoreDataCollector $storeDataCollector
    ) {
    }

    public function getCollectedData()
    {
        return $this->storeDataCollector->collect((int)$this->storeManager->getStore()->getId());
    }
}
