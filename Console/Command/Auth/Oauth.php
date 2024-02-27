<?php
/**
 * O2TI PagBank Source Inventory Auth.
 *
 * Copyright © 2023 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\PagBankSourceInventoryAuth\Console\Command\Auth;

use Magento\Framework\App\State;
use O2TI\PagBankSourceInventoryAuth\Model\Console\Command\Auth\Create;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Oauth extends Command
{
    /**
     * Code.
     */
    public const CODE = 'code';

    /**
     * Code Verifier.
     */
    public const CODE_VERIFIER = 'code_verifier';

    /**
     * Source Code.
     */
    public const SOURCE_CODE = 'source_code';

    /**
     * @var Create
     */
    protected $create;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param State  $state
     * @param Create $create
     */
    public function __construct(
        State $state,
        Create $create
    ) {
        $this->state = $state;
        $this->create = $create;
        parent::__construct();
    }

    /**
     * Execute.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $this->create->setOutput($output);

        $code = $input->getArgument(self::CODE);

        $codeVerifier = $input->getArgument(self::CODE_VERIFIER);

        $sourceCode = $input->getArgument(self::SOURCE_CODE);

        return $this->create->createNewData($code, $codeVerifier, $sourceCode);
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('o2ti:credential:oauth');
        $this->setDescription('Set create data for Source inventory');
        $this->setDefinition(
            [
                new InputArgument(self::CODE, InputArgument::REQUIRED, 'Code'),
                new InputArgument(self::CODE_VERIFIER, InputArgument::REQUIRED, 'Code Verifier'),
                new InputArgument(self::SOURCE_CODE, InputArgument::REQUIRED, 'Source Code')
            ]
        );
        parent::configure();
    }
}
