<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ENABLED = 'llmstxt/general/enabled';
    private const XML_PATH_MANUAL_CONTENT = 'llmstxt/general/manual_content';
    private const XML_PATH_USE_MANUAL_CONTENT = 'llmstxt/general/use_manual_content';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getManualContent(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_MANUAL_CONTENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function useManualContent(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_USE_MANUAL_CONTENT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
