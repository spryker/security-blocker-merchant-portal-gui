<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication;

use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\ZedUi\ZedUiFactoryInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\Messenger\Business\MessengerFacadeInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\LoginRejectionResponseBuilder;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\LoginRejectionResponseBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\MessageBuilder;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\MessageBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\SecurityCheckAuthContextBuilder;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\SecurityCheckAuthContextBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Checker\LoginAttemptCaptchaRequirementChecker;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Checker\LoginAttemptCaptchaRequirementCheckerInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\EventSubscriber\SecurityBlockerMerchantPortalUserEventSubscriber;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\EventSubscriber\SecurityBlockerMerchantPortalUserLoginEventSubscriber;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Facade\SecurityBlockerMerchantPortalGuiToGlossaryFacadeInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiDependencyProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method \Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig getConfig()
 */
class SecurityBlockerMerchantPortalGuiCommunicationFactory extends AbstractCommunicationFactory
{
    public function createLoginProtectionEventSubscriber(): EventSubscriberInterface
    {
        return $this->createLoginProtectionEventSubscriberStrategyResolver()->get($this->getConfig()->getLoginProtectionStrategy());
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<\Symfony\Component\EventDispatcher\EventSubscriberInterface>
     */
    public function createLoginProtectionEventSubscriberStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver([
            SecurityBlockerMerchantPortalGuiConfig::LOGIN_PROTECTION_STRATEGY_IP => fn (): EventSubscriberInterface => $this->createSecurityBlockerMerchantPortalUserEventSubscriber(),
            SecurityBlockerMerchantPortalGuiConfig::LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT => fn (): EventSubscriberInterface => $this->createSecurityBlockerMerchantPortalUserLoginEventSubscriber(),
        ]);
    }

    public function createSecurityBlockerMerchantPortalUserEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerMerchantPortalUserEventSubscriber(
            $this->getRequestStack(),
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
        );
    }

    public function createSecurityBlockerMerchantPortalUserLoginEventSubscriber(): EventSubscriberInterface
    {
        return new SecurityBlockerMerchantPortalUserLoginEventSubscriber(
            $this->getRequestStack(),
            $this->getSecurityBlockerClient(),
            $this->createMessageBuilder(),
            $this->getConfig(),
            $this->createSecurityCheckAuthContextBuilder(),
            $this->createLoginRejectionResponseBuilder(),
        );
    }

    public function createLoginAttemptCaptchaRequirementChecker(): LoginAttemptCaptchaRequirementCheckerInterface
    {
        return new LoginAttemptCaptchaRequirementChecker(
            $this->getSecurityBlockerClient(),
            $this->createSecurityCheckAuthContextBuilder(),
            $this->getConfig(),
        );
    }

    public function createMessageBuilder(): MessageBuilderInterface
    {
        return new MessageBuilder(
            $this->getGlossaryFacade(),
        );
    }

    public function getSecurityBlockerClient(): SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function getGlossaryFacade(): SecurityBlockerMerchantPortalGuiToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::FACADE_GLOSSARY);
    }

    public function getRequestStack(): RequestStack
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::SERVICE_REQUEST_STACK);
    }

    public function getLocale(): string
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::SERVICE_LOCALE);
    }

    public function createSecurityCheckAuthContextBuilder(): SecurityCheckAuthContextBuilderInterface
    {
        return new SecurityCheckAuthContextBuilder($this->getConfig());
    }

    public function createLoginRejectionResponseBuilder(): LoginRejectionResponseBuilderInterface
    {
        return new LoginRejectionResponseBuilder($this->getMessengerFacade(), $this->getZedUiFactory());
    }

    public function getMessengerFacade(): MessengerFacadeInterface
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::FACADE_MESSENGER);
    }

    public function getZedUiFactory(): ?ZedUiFactoryInterface
    {
        return $this->getProvidedDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::SERVICE_ZED_UI_FACTORY);
    }
}
