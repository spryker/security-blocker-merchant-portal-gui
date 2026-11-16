<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientBridge;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Facade\SecurityBlockerMerchantPortalGuiToGlossaryFacadeBridge;

/**
 * @method \Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig getConfig()
 */
class SecurityBlockerMerchantPortalGuiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_SECURITY_BLOCKER = 'CLIENT_SECURITY_BLOCKER';

    /**
     * @var string
     */
    public const FACADE_GLOSSARY = 'FACADE_GLOSSARY';

    /**
     * @uses \Spryker\Zed\Http\Communication\Plugin\Application\HttpApplicationPlugin::SERVICE_REQUEST_STACK
     *
     * @var string
     */
    public const SERVICE_REQUEST_STACK = 'request_stack';

    public const string FACADE_MESSENGER = 'FACADE_MESSENGER';

    /**
     * @uses \Spryker\Zed\ZedUi\Communication\Plugin\Application\ZedUiApplicationPlugin::SERVICE_ZED_UI_FACTORY
     */
    public const string SERVICE_ZED_UI_FACTORY = 'SERVICE_ZED_UI_FACTORY';

    /**
     * @uses \Spryker\Zed\Locale\Communication\Plugin\Application\LocaleApplicationPlugin::SERVICE_LOCALE
     *
     * @var string
     */
    public const SERVICE_LOCALE = 'locale';

    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addSecurityBlockerClient($container);
        $container = $this->addGlossaryFacade($container);
        $container = $this->addRequestStack($container);
        $container = $this->addMessengerFacade($container);
        $container = $this->addZedUiFactory($container);
        $container = $this->addLocaleService($container);

        return $container;
    }

    protected function addSecurityBlockerClient(Container $container): Container
    {
        $container->set(static::CLIENT_SECURITY_BLOCKER, function (Container $container) {
            return new SecurityBlockerMerchantPortalGuiToSecurityBlockerClientBridge(
                $container->getLocator()->securityBlocker()->client(),
            );
        });

        return $container;
    }

    protected function addGlossaryFacade(Container $container): Container
    {
        $container->set(static::FACADE_GLOSSARY, function (Container $container) {
            return new SecurityBlockerMerchantPortalGuiToGlossaryFacadeBridge(
                $container->getLocator()->glossary()->facade(),
            );
        });

        return $container;
    }

    protected function addRequestStack(Container $container): Container
    {
        $container->set(static::SERVICE_REQUEST_STACK, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_REQUEST_STACK);
        });

        return $container;
    }

    protected function addLocaleService(Container $container): Container
    {
        $container->set(static::SERVICE_LOCALE, function (Container $container) {
            return $container->getApplicationService(static::SERVICE_LOCALE);
        });

        return $container;
    }

    protected function addMessengerFacade(Container $container): Container
    {
        $container->set(static::FACADE_MESSENGER, function (Container $container) {
            return $container->getLocator()->messenger()->facade();
        });

        return $container;
    }

    protected function addZedUiFactory(Container $container): Container
    {
        $container->set(static::SERVICE_ZED_UI_FACTORY, function (Container $container) {
            // The subscriber is also registered in the Back Office application, which provides no ZedUi factory.
            if (!$container->hasApplicationService(static::SERVICE_ZED_UI_FACTORY)) {
                return null;
            }

            return $container->getApplicationService(static::SERVICE_ZED_UI_FACTORY);
        });

        return $container;
    }
}
