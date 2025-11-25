<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use SR\LlmsTxt\Block\Data;

class Generator
{
    public function __construct(
        private readonly Data $blockData
    ) {
    }

    public function generate(?int $storeId = null): string
    {
        $this->blockData->setData('adminhtml_store_id', $storeId);
        return $this->blockData->toHtml();
    }
}
