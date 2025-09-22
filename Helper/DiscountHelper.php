<?php

namespace Adeelq\AbandonedCartReminder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\Coupon\Massgenerator;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Quote\Model\Quote;
use Adeelq\CoreModule\Logger\Logger;

class DiscountHelper extends AbstractHelper
{
    /**
     * @var RuleFactory
     */
    private RuleFactory $ruleFactory;

    /**
     * @var CouponFactory
     */
    private CouponFactory $couponFactory;

    /**
     * @var Massgenerator
     */
    private Massgenerator $massGenerator;

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param CouponFactory $couponFactory
     * @param Massgenerator $massGenerator
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        CouponFactory $couponFactory,
        Massgenerator $massGenerator,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->ruleFactory = $ruleFactory;
        $this->couponFactory = $couponFactory;
        $this->massGenerator = $massGenerator;
        $this->logger = $logger;
    }

    /**
     * Check if discount code feature is enabled
     *
     * @param StoreInterface $store
     * @return bool
     */
    public function isDiscountEnabled(StoreInterface $store): bool
    {
        return (bool) $store->getConfig('adeelq_abandoned_configuration/discount_code/enabled');
    }

    /**
     * Generate a unique discount code for abandoned cart
     *
     * @param Quote $cart
     * @param StoreInterface $store
     * @return string|null
     * @throws LocalizedException
     */
    public function generateDiscountCode(Quote $cart, StoreInterface $store): ?string
    {
        if (!$this->isDiscountEnabled($store)) {
            return null;
        }

        try {
            $percentage = (float) $store->getConfig('adeelq_abandoned_configuration/discount_code/percentage') ?: 10;
            $expirationHours = (int) $store->getConfig('adeelq_abandoned_configuration/discount_code/expiration_time') ?: 24;
            $prefix = $store->getConfig('adeelq_abandoned_configuration/discount_code/prefix') ?: 'CART';

            // Validate input values
            $percentage = max(0, min(100, $percentage)); // Ensure 0-100 range
            $expirationHours = max(1, min(8760, $expirationHours)); // Max 1 year
            $prefix = preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($prefix, 0, 10))); // Sanitize prefix

            // Create sales rule
            $rule = $this->ruleFactory->create();
            $rule->setName('Abandoned Cart Discount - Cart #' . $cart->getId())
                ->setDescription('Auto-generated discount for abandoned cart #' . $cart->getId())
                ->setIsActive(1)
                ->setCustomerGroupIds([0, 1, 2, 3]) // General, Not Logged In, Wholesale, Retailer
                ->setWebsiteIds([$store->getWebsiteId()])
                ->setCouponType(\Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
                ->setUseAutoGeneration(1)
                ->setUsesPerCoupon(1)
                ->setUsesPerCustomer(1)
                ->setFromDate(date('Y-m-d'))
                ->setToDate(date('Y-m-d H:i:s', strtotime('+' . $expirationHours . ' hours')))
                ->setSimpleAction(\Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION)
                ->setDiscountAmount($percentage)
                ->setDiscountStep(0)
                ->setApplyToShipping(0)
                ->setTimesUsed(0)
                ->setIsRss(0)
                ->setStopRulesProcessing(0);

            // Set conditions to apply only to current customer's cart
            if ($cart->getCustomerId()) {
                $rule->getConditions()->loadArray([
                    'type' => \Magento\SalesRule\Model\Rule\Condition\Combine::class,
                    'attribute' => null,
                    'operator' => null,
                    'value' => '1',
                    'is_value_processed' => null,
                    'aggregator' => 'all'
                ]);
            }

            $rule->save();

            // Generate coupon code
            $couponCode = $this->generateUniqueCouponCode($prefix, $cart->getId());
            
            $coupon = $this->couponFactory->create();
            $coupon->setRuleId($rule->getId())
                ->setCode($couponCode)
                ->setUsageLimit(1)
                ->setUsagePerCustomer(1)
                ->setTimesUsed(0)
                ->setExpirationDate(date('Y-m-d H:i:s', strtotime('+' . $expirationHours . ' hours')))
                ->setCreatedAt(date('Y-m-d H:i:s'))
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC);

            $coupon->save();

            return $couponCode;

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate discount code for cart ' . $cart->getId() . ': ' . $e->getMessage());
            throw new LocalizedException(__('Unable to generate discount code.'));
        }
    }

    /**
     * Generate unique coupon code
     *
     * @param string $prefix
     * @param int $cartId
     * @return string
     */
    private function generateUniqueCouponCode(string $prefix, int $cartId): string
    {
        $timestamp = substr(time(), -6);
        $randomString = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $code = $prefix . $cartId . $timestamp . $randomString;
        
        // Ensure code is not longer than 255 characters (Magento limit)
        return substr($code, 0, 255);
    }

    /**
     * Get discount percentage from configuration
     *
     * @param StoreInterface $store
     * @return float
     */
    public function getDiscountPercentage(StoreInterface $store): float
    {
        return (float) $store->getConfig('adeelq_abandoned_configuration/discount_code/percentage') ?: 10;
    }

    /**
     * Get expiration hours from configuration
     *
     * @param StoreInterface $store
     * @return int
     */
    public function getExpirationHours(StoreInterface $store): int
    {
        return (int) $store->getConfig('adeelq_abandoned_configuration/discount_code/expiration_time') ?: 24;
    }
}