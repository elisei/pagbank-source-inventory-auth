<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Plugin\InventorySource;

use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;
use Magento\InventoryApi\Api\Data\SourceExtensionFactory;
use Magento\InventoryApi\Api\Data\SourceExtensionInterfaceFactory;

class AddExtensionAttributes
{
    /**
     * @var SourceExtensionInterfaceFactory
     */
    protected $extensionFactory;

    /**
     * @param SourceExtensionInterfaceFactory $extensionFactory
     */
    public function __construct(SourceExtensionInterfaceFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * Before save.
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface           $source
     *
     * @return array
     */
    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ) {
        $extensionAttributes = $source->getExtensionAttributes();

        $oAuth = $extensionAttributes->getOauth();
        $refreshOauth = $extensionAttributes->getRefreshOauth();
        $publicKey = $extensionAttributes->getPublicKey();

        $source->setOauth($oAuth);
        $source->setRefreshOauth($refreshOauth);
        $source->setPublicKey($publicKey);

        $source->save();
        return [$source];
    }

    /**
     * After save.
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface           $source
     *
     * @return SourceInterface
     */
    public function afterGet(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ) {
        $oAuth = $source->getData('oauth');
        $refreshOauth = $source->getData('refresh_oauth');
        $publicKey = $source->getData('public_key');

        $extensionAttributes = $source->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        
        $extensionAttributes->setOauth($oAuth);
        $extensionAttributes->setRefreshOauth($refreshOauth);
        $extensionAttributes->setPublicKey($publicKey);

        $source->setExtensionAttributes($extensionAttributes);

        return $source;
    }

    /**
     * After Get List.
     *
     * @param SourceRepositoryInterface     $subject
     * @param SourceSearchResultsInterface  $result
     *
     * @return SourceSearchResultsInterface
     */
    public function afterGetList(
        SourceRepositoryInterface $subject,
        SourceSearchResultsInterface $result
    ) {
        $products = [];
        $sources = $result->getItems();

        foreach ($sources as $source) {
            $oAuth = $source->getData('oauth');
            $refreshOauth = $source->getData('refresh_oauth');
            $publicKey = $source->getData('public_key');

            $extensionAttributes = $source->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            
            $extensionAttributes->setOauth($oAuth);
            $extensionAttributes->setRefreshOauth($refreshOauth);
            $extensionAttributes->setPublicKey($publicKey);

            $source->setExtensionAttributes($extensionAttributes);
            $products[] = $source;
        }
        $result->setItems($products);
        return $result;
    }
}
