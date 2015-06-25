<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

class Index extends \Magento\Checkout\Controller\Onepage
{
    /**
     * Checkout page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $checkoutHelper = $this->_objectManager->get('Magento\Checkout\Helper\Data');
        if (!$checkoutHelper->canOnepageCheckout()) {
            $this->messageManager->addError(__('The onepage checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $quote = $this->getOnepage()->getQuote();

        if (!$this->_customerSession->isLoggedIn() && !$checkoutHelper->isAllowedGuestCheckout($quote)) {
            $this->messageManager->addError(__('Guest checkout is disabled.'));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $this->_customerSession->regenerateId();
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $currentUrl = $this->_url->getUrl('*/*/*', ['_secure' => true]);
        $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($currentUrl);
        $this->getOnepage()->initCheckout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Checkout'));
        return $resultPage;
    }
}
