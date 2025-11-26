<?php

declare(strict_types=1);

namespace SR\LlmsTxt\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use SR\LlmsTxt\Model\Config;

class Index extends Action implements HttpGetActionInterface
{
    public function __construct(
        Context $context,
        private readonly PageFactory $manualResultPageFactory,
        private readonly PageFactory $generatedResultPageFactory,
        private readonly Config $config
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        $pageFactory = $this->manualResultPageFactory;
        if ($this->config->isEnabled() && !$this->config->useManualContent()) {
            $pageFactory = $this->generatedResultPageFactory;
        }
        /** @var Page $resultPage */
        $resultPage = $pageFactory->create(true);
        $resultPage->addHandle('llmstxt_index_index');
        $resultPage->setHeader('Content-Type', 'text/plain; charset=utf-8');

        return $resultPage;
    }
}
