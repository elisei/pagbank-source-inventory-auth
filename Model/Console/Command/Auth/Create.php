<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Model\Console\Command\Auth;

use Magento\Framework\App\State;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use O2TI\PagBankSourceInventoryAuth\Model\Api\Credential;
use O2TI\PagBankSourceInventoryAuth\Model\Console\Command\AbstractModel;

/**
 * Class Create - Manually apply.
 */
class Create extends AbstractModel
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Credential
     */
    protected $credential;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param State $state
     * @param Credential $credential
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        State $state,
        Credential $credential,
        Json $json,
        StoreManagerInterface $storeManager
    ) {
        $this->state = $state;
        $this->credential = $credential;
        $this->json = $json;
        $this->storeManager = $storeManager;
    }

    /**
     * Command Preference.
     *
     * @param string $code
     * @param string $codeVerifier
     * @param string $sourceCode
     *
     * @return int
     */
    public function createNewData($code, $codeVerifier, $sourceCode)
    {
        
        $this->writeln(__('Criando dados do PagBank'));
        
        $store = $this->storeManager->getStore('admin');
        $adminStoreId = $store->getId();
        
        $this->storeManager->setCurrentStore($adminStoreId);

        $oAuthResponse = $this->credential->getAuthorize(
            0,
            $code,
            $codeVerifier,
            $sourceCode
        );
        $oAuthResponse = $this->json->unserialize($oAuthResponse);

        if (!isset($oAuthResponse['error_messages'])
            && isset($oAuthResponse['access_token'])
        ) {
            $this->writeln(
                '<info>'.
                __('Geração de oAuth com sucesso: %1', $oAuthResponse['access_token'])
                .'</info>'
            );

            if (isset($oAuthResponse['access_token'])) {
                $oAuth = $oAuthResponse['access_token'];
                $configs = [
                    'oauth'  => $oAuth,
                    'refresh_oauth' => $oAuthResponse['refresh_token'],
                ];
    
                $this->credential->setNewConfigs($configs, $sourceCode);
    
                if ($oAuth) {
                    $publicKey = $this->credential->getPublicKey($oAuth, 1);
                    $publicKey = $this->json->unserialize($publicKey);

                    if (isset($publicKey['public_key'])
                    ) {
                        $this->writeln('<info>'.__('Geração de Public Key com sucesso').'</info>');
                    }

                    $this->credential->setNewConfigs(
                        ['public_key' => $publicKey['public_key']],
                        $sourceCode
                    );
                }
            }
        }

        if (isset($oAuthResponse['error_messages'])) {
            foreach ($oAuthResponse['error_messages'] as $error) {
                $this->writeln('<error>'.
                    __(
                        'Code: %1, Description: %2, Paramenter: %3',
                        $error['code'],
                        $error['description'],
                        $error['parameter_name']
                    ) .'</error>');
            }
        }

        return 1;
    }
}
