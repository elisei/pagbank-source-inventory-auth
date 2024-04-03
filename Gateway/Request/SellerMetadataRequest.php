<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Gateway\Request;

use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use PagBank\PaymentMagento\Gateway\Request\MetadataRequest;

/**
 * Class Items Data Request - Item structure for orders.
 */
class SellerMetadataRequest implements BuilderInterface
{
    /**
     * Seller oAuth block name.
     */
    public const SOURCE_CODE = 'source_code';

    /**
     * @var GetSourceItemsBySkuInterface
     */
    protected $sourceItemsBySku;

    /**
     * @param GetSourceItemsBySkuInterface  $sourceItemsBySku
     */
    public function __construct(
        GetSourceItemsBySkuInterface $sourceItemsBySku
    ) {
        $this->sourceItemsBySku = $sourceItemsBySku;
    }

    /**
     * Build.
     *
     * @param array $buildSubject
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function build(array $buildSubject): array
    {
        $result = [];

        /** @var PaymentDataObject $paymentDO * */
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order $order * */
        $order = $paymentDO->getOrder();

        $result[MetadataRequest::METADATA][] = $this->getSellerByInventory($order);

        return $result;
    }

    /**
     * Get Seller By Inventory.
     *
     * @param \Magento\Sales\Model\Order $order
     *
     * @return array
     */
    public function getSellerByInventory(
        $order
    ) {
        $result = [];
        $items = $order->getItems();

        foreach ($items as $item) {

            $sku = $item->getSku();
            $sourceItems = $this->sourceItemsBySku->execute($sku);

            foreach ($sourceItems as $sourceItem) {
                $result[self::SOURCE_CODE][] = $sourceItem->getSourceCode();
            }
        }

        return array_unique($result);
    }
}
