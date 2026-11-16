<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityBlockerMerchantPortalGui\Communication\Plugin;

use Codeception\Test\Unit;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Plugin\EventDispatcher\SecurityBlockerMerchantPortalUserEventDispatcherPlugin;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\SecurityBlockerMerchantPortalGuiCommunicationFactory;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiDependencyProvider;
use SprykerTest\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiCommunicationTester;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityBlockerMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group SecurityBlockerMerchantPortalUserEventDispatcherPluginTest
 * Add your own group annotations below this line
 */
class SecurityBlockerMerchantPortalUserEventDispatcherPluginTest extends Unit
{
    protected const string UNKNOWN_LOGIN_PROTECTION_STRATEGY = 'unknown';

    protected SecurityBlockerMerchantPortalGuiCommunicationTester $tester;

    protected SecurityBlockerMerchantPortalUserEventDispatcherPlugin $securityBlockerMerchantPortalUserEventDispatcherPlugin;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $securityBlockerMerchantPortalGuiDependencyProvider = new SecurityBlockerMerchantPortalGuiDependencyProvider();
        $securityBlockerMerchantPortalGuiDependencyProvider->provideCommunicationLayerDependencies($container);

        $securityBlockerMerchantPortalGuiFactoryMock = $this->createMock(SecurityBlockerMerchantPortalGuiCommunicationFactory::class);
        $securityBlockerMerchantPortalGuiFactoryMock->setContainer($container);

        $this->securityBlockerMerchantPortalUserEventDispatcherPlugin = new SecurityBlockerMerchantPortalUserEventDispatcherPlugin();
        $this->securityBlockerMerchantPortalUserEventDispatcherPlugin->setFactory($securityBlockerMerchantPortalGuiFactoryMock);
    }

    public function testSecurityBlockerMerchantPortalUserEventDispatcherPluginShouldCallAddSubscriberOnEventDispatcher(): void
    {
        // Arrange
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();

        // Assert
        $eventDispatcherMock->expects($this->once())
            ->method('addSubscriber');

        // Act
        $this->securityBlockerMerchantPortalUserEventDispatcherPlugin->extend(
            $eventDispatcherMock,
            $this->createMock(ContainerInterface::class),
        );
    }

    public function testPluginAddsTheIpOnlySubscriberForTheIpLoginProtectionStrategy(): void
    {
        // Arrange
        $eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $securityBlockerMerchantPortalGuiFactoryMock = $this->createFactoryMock(SecurityBlockerMerchantPortalGuiConfig::LOGIN_PROTECTION_STRATEGY_IP);
        $securityBlockerMerchantPortalGuiFactoryMock->method('createSecurityBlockerMerchantPortalUserEventSubscriber')->willReturn($eventSubscriber);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        // Assert
        $securityBlockerMerchantPortalGuiFactoryMock->expects($this->never())->method('createSecurityBlockerMerchantPortalUserLoginEventSubscriber');
        $eventDispatcherMock->expects($this->once())->method('addSubscriber')->with($this->identicalTo($eventSubscriber));

        // Act
        $this->createPlugin($securityBlockerMerchantPortalGuiFactoryMock)->extend($eventDispatcherMock, $this->createMock(ContainerInterface::class));
    }

    public function testPluginAddsTheLoginSubscriberForTheIpAndAccountLoginProtectionStrategy(): void
    {
        // Arrange
        $eventSubscriber = $this->createMock(EventSubscriberInterface::class);
        $securityBlockerMerchantPortalGuiFactoryMock = $this->createFactoryMock(SecurityBlockerMerchantPortalGuiConfig::LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT);
        $securityBlockerMerchantPortalGuiFactoryMock->method('createSecurityBlockerMerchantPortalUserLoginEventSubscriber')->willReturn($eventSubscriber);
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        // Assert
        $securityBlockerMerchantPortalGuiFactoryMock->expects($this->never())->method('createSecurityBlockerMerchantPortalUserEventSubscriber');
        $eventDispatcherMock->expects($this->once())->method('addSubscriber')->with($this->identicalTo($eventSubscriber));

        // Act
        $this->createPlugin($securityBlockerMerchantPortalGuiFactoryMock)->extend($eventDispatcherMock, $this->createMock(ContainerInterface::class));
    }

    public function testPluginFailsForALoginProtectionStrategyWithoutARegisteredSubscriber(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiFactoryMock = $this->createFactoryMock(static::UNKNOWN_LOGIN_PROTECTION_STRATEGY);

        // Expect
        $this->expectException(InvalidArgumentException::class);

        // Act
        $this->createPlugin($securityBlockerMerchantPortalGuiFactoryMock)->extend($this->createMock(EventDispatcherInterface::class), $this->createMock(ContainerInterface::class));
    }

    protected function createFactoryMock(string $loginProtectionStrategy): SecurityBlockerMerchantPortalGuiCommunicationFactory&MockObject
    {
        $securityBlockerMerchantPortalGuiConfigMock = $this->createPartialMock(SecurityBlockerMerchantPortalGuiConfig::class, ['getLoginProtectionStrategy']);
        $securityBlockerMerchantPortalGuiConfigMock->method('getLoginProtectionStrategy')->willReturn($loginProtectionStrategy);

        $securityBlockerMerchantPortalGuiFactoryMock = $this->createPartialMock(SecurityBlockerMerchantPortalGuiCommunicationFactory::class, [
            'createSecurityBlockerMerchantPortalUserEventSubscriber',
            'createSecurityBlockerMerchantPortalUserLoginEventSubscriber',
        ]);
        $securityBlockerMerchantPortalGuiFactoryMock->setConfig($securityBlockerMerchantPortalGuiConfigMock);

        return $securityBlockerMerchantPortalGuiFactoryMock;
    }

    protected function createPlugin(
        SecurityBlockerMerchantPortalGuiCommunicationFactory $securityBlockerMerchantPortalGuiCommunicationFactory
    ): SecurityBlockerMerchantPortalUserEventDispatcherPlugin {
        $securityBlockerMerchantPortalUserEventDispatcherPlugin = new SecurityBlockerMerchantPortalUserEventDispatcherPlugin();
        $securityBlockerMerchantPortalUserEventDispatcherPlugin->setFactory($securityBlockerMerchantPortalGuiCommunicationFactory);

        return $securityBlockerMerchantPortalUserEventDispatcherPlugin;
    }
}
