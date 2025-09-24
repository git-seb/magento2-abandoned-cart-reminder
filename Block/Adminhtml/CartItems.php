<?php

namespace Adeelq\AbandonedCartReminder\Block\Adminhtml;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Tax\Helper\Data as TaxHelper;
use Throwable;

class CartItems extends AbstractItems
{
    /**
     * @var QuoteRepository
     */
    private QuoteRepository $cartRepository;

    /**
     * @var Escaper
     */
    public Escaper $escaper;

    /**
     * @var CurrencyFactory
     */
    protected CurrencyFactory $currencyFactory;

    /**
     * @var TaxHelper
     */
    protected TaxHelper $taxHelper;

    /**
     * @param Context $context
     * @param QuoteRepository $cartRepository
     * @param Escaper $escaper
     * @param CurrencyFactory $currencyFactory
     * @param TaxHelper $taxHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        QuoteRepository $cartRepository,
        Escaper $escaper,
        CurrencyFactory $currencyFactory,
        TaxHelper $taxHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cartRepository = $cartRepository;
        $this->escaper = $escaper;
        $this->currencyFactory = $currencyFactory;
        $this->taxHelper = $taxHelper;
    }

    /**
     * @return CartInterface|null
     */
    public function getCart(): ?CartInterface
    {
        try {
            return $this->cartRepository->get((int) $this->getData('cart_id'));
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param Item $item
     *
     * @return string
     */
    public function getItemPrice(Item $item): string
    {
        $cart = $this->getCart();
        if (!$cart) {
            return '';
        }

        try {
            // Get the price including tax based on store configuration
            $priceIncludesTax = $this->taxHelper->priceIncludesTax($cart->getStore());
            $displayPriceInclTax = $this->taxHelper->displayPriceIncludingTax();

            if ($displayPriceInclTax || $priceIncludesTax) {
                // Use row total including tax
                $price = $item->getRowTotalInclTax() ?: $item->getRowTotal();
            } else {
                // Use row total excluding tax
                $price = $item->getRowTotal();
            }

            return $this->currencyFactory
                ->create()
                ->load($cart->getCurrency()->getQuoteCurrencyCode())
                ->formatPrecision($price, 2);
        } catch (Throwable $e) {
            // Fallback to basic row total if tax calculation fails
            return $this->currencyFactory
                ->create()
                ->load($cart->getCurrency()->getQuoteCurrencyCode())
                ->formatPrecision($item->getRowTotal(), 2);
        }
    }
}
