<?php

namespace PriceCopy;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class PriceCopy extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function install(InstallContext $installContext)
    {
        parent::install($installContext);
    }

    public function uninstall(UninstallContext $uninstallContext)
    {
        parent::uninstall($uninstallContext);
    }

}
