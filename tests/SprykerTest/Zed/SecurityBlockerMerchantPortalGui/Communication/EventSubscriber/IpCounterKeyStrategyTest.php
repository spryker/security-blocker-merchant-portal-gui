<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityBlockerMerchantPortalGui\Communication\EventSubscriber;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthResponseTransfer;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\MessageBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\EventSubscriber\SecurityBlockerMerchantPortalUserEventSubscriber;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\SecurityBlockerMerchantPortalGuiCommunicationFactory;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use SprykerTest\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiCommunicationTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityBlockerMerchantPortalGui
 * @group Communication
 * @group EventSubscriber
 * @group IpCounterKeyStrategyTest
 * Add your own group annotations below this line
 */
class IpCounterKeyStrategyTest extends Unit
{
    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm::FORM_NAME
     */
    protected const string FORM_NAME = 'security-merchant-portal-gui';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm::FIELD_USERNAME
     */
    protected const string FIELD_USERNAME = 'username';

    protected const string IP = '10.0.0.1';

    protected const string USERNAME = 'admin@spryker.com';

    protected const string OTHER_USERNAME = 'other@spryker.com';

    protected SecurityBlockerMerchantPortalGuiCommunicationTester $tester;

    public function testDefaultStrategyKeepsTheReleasedPerIpKeyWithoutTheUsername(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiConfig = new SecurityBlockerMerchantPortalGuiConfig();

        // Act
        $securityCheckAuthContextTransfers = $this->dispatchLoginAttempts($securityBlockerMerchantPortalGuiConfig, [static::USERNAME]);

        // Assert
        $this->assertSame(SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP, $securityCheckAuthContextTransfers[0]->getIpCounterKeyStrategy());
        $this->assertNull($securityCheckAuthContextTransfers[0]->getAccount());
        $this->assertSame(static::IP, $securityCheckAuthContextTransfers[0]->getIp());
    }

    public function testIpAndUsernameStrategyPassesTheStrategyAndTheSubmittedUsernameToTheSecurityBlocker(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiConfig = $this->createIpAndUsernameConfig();

        // Act
        $securityCheckAuthContextTransfers = $this->dispatchLoginAttempts($securityBlockerMerchantPortalGuiConfig, [static::USERNAME]);

        // Assert
        $this->assertSame(SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME, $securityCheckAuthContextTransfers[0]->getIpCounterKeyStrategy());
        $this->assertSame(static::USERNAME, $securityCheckAuthContextTransfers[0]->getLoginIdentifier());
    }

    public function testIpAndUsernameStrategyAppliesToTheAccountLoginProtectionContext(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiCommunicationFactory = $this->createFactory($this->createIpAndUsernameConfig());

        // Act
        $securityCheckAuthContextTransfer = $securityBlockerMerchantPortalGuiCommunicationFactory
            ->createSecurityCheckAuthContextBuilder()
            ->createSecurityCheckAuthContextTransfer($this->createLoginRequest(static::USERNAME), static::USERNAME);

        // Assert
        $this->assertSame(SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME, $securityCheckAuthContextTransfer->getIpCounterKeyStrategy());
        $this->assertSame(static::USERNAME, $securityCheckAuthContextTransfer->getLoginIdentifier());
    }

    public function testDefaultStrategyLeavesTheAccountLoginProtectionContextWithoutAnAccount(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiCommunicationFactory = $this->createFactory(new SecurityBlockerMerchantPortalGuiConfig());

        // Act
        $securityCheckAuthContextTransfer = $securityBlockerMerchantPortalGuiCommunicationFactory
            ->createSecurityCheckAuthContextBuilder()
            ->createSecurityCheckAuthContextTransfer($this->createLoginRequest(static::USERNAME), static::USERNAME);

        // Assert
        $this->assertSame(SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP, $securityCheckAuthContextTransfer->getIpCounterKeyStrategy());
        $this->assertNull($securityCheckAuthContextTransfer->getAccount());
    }

    public function testIpAndUsernameStrategyPassesNoUsernameWhenTheUsernameIsNotAString(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiConfig = $this->createIpAndUsernameConfig();
        $request = $this->createLoginRequestWithLoginForm([static::FIELD_USERNAME => [static::USERNAME]]);

        // Act
        $securityCheckAuthContextTransfers = $this->dispatchLoginRequests($securityBlockerMerchantPortalGuiConfig, [$request]);

        // Assert
        $this->assertNull($securityCheckAuthContextTransfers[0]->getLoginIdentifier());
        $this->assertNull($securityCheckAuthContextTransfers[0]->getAccount());
    }

    public function testIpAndUsernameStrategyCountsAFailedLoginOnTheKeyTheLoginIsCheckedOn(): void
    {
        // Arrange
        $securityBlockerMerchantPortalGuiConfig = $this->createIpAndUsernameConfig();
        $checkedSecurityCheckAuthContextTransfers = $this->dispatchLoginAttempts($securityBlockerMerchantPortalGuiConfig, [static::USERNAME]);

        // Act
        $countedSecurityCheckAuthContextTransfers = $this->dispatchLoginFailure($securityBlockerMerchantPortalGuiConfig, $this->createLoginRequest(static::USERNAME));

        // Assert
        $this->assertCount(1, $countedSecurityCheckAuthContextTransfers);
        $this->assertSame(SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME, $countedSecurityCheckAuthContextTransfers[0]->getIpCounterKeyStrategy());
        $this->assertSame($this->getIpCounterKeyParts($checkedSecurityCheckAuthContextTransfers[0]), $this->getIpCounterKeyParts($countedSecurityCheckAuthContextTransfers[0]));
    }

    /**
     * @param list<string> $usernames
     *
     * @return list<\Generated\Shared\Transfer\SecurityCheckAuthContextTransfer>
     */
    protected function dispatchLoginAttempts(SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig, array $usernames): array
    {
        return $this->dispatchLoginRequests($securityBlockerMerchantPortalGuiConfig, array_map(fn (string $username): Request => $this->createLoginRequest($username), $usernames));
    }

    /**
     * @param list<\Symfony\Component\HttpFoundation\Request> $requests
     *
     * @return list<\Generated\Shared\Transfer\SecurityCheckAuthContextTransfer>
     */
    protected function dispatchLoginRequests(SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig, array $requests): array
    {
        $securityCheckAuthContextTransfers = [];
        $securityBlockerClientMock = $this->createMock(SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('isAccountBlocked')->willReturnCallback(
            function (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer) use (&$securityCheckAuthContextTransfers): SecurityCheckAuthResponseTransfer {
                $securityCheckAuthContextTransfers[] = $securityCheckAuthContextTransfer;

                return (new SecurityCheckAuthResponseTransfer())->setIsBlocked(false);
            },
        );
        $securityBlockerMerchantPortalUserEventSubscriber = $this->createSubscriber($securityBlockerMerchantPortalGuiConfig, $securityBlockerClientMock, new RequestStack());

        foreach ($requests as $request) {
            $securityBlockerMerchantPortalUserEventSubscriber->onKernelRequest(new RequestEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST));
        }

        return $securityCheckAuthContextTransfers;
    }

    /**
     * @return list<\Generated\Shared\Transfer\SecurityCheckAuthContextTransfer>
     */
    protected function dispatchLoginFailure(SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig, Request $request): array
    {
        $securityCheckAuthContextTransfers = [];
        $securityBlockerClientMock = $this->createMock(SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('incrementLoginAttemptCount')->willReturnCallback(
            function (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer) use (&$securityCheckAuthContextTransfers): SecurityCheckAuthResponseTransfer {
                $securityCheckAuthContextTransfers[] = $securityCheckAuthContextTransfer;

                return new SecurityCheckAuthResponseTransfer();
            },
        );
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->createSubscriber($securityBlockerMerchantPortalGuiConfig, $securityBlockerClientMock, $requestStack)->onAuthenticationFailure();

        return $securityCheckAuthContextTransfers;
    }

    protected function createSubscriber(
        SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig,
        SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface $securityBlockerClient,
        RequestStack $requestStack
    ): SecurityBlockerMerchantPortalUserEventSubscriber {
        return new SecurityBlockerMerchantPortalUserEventSubscriber(
            $requestStack,
            $securityBlockerClient,
            $this->createMock(MessageBuilderInterface::class),
            $securityBlockerMerchantPortalGuiConfig,
        );
    }

    /**
     * @return array<string, string|null>
     */
    protected function getIpCounterKeyParts(SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): array
    {
        return [
            SecurityCheckAuthContextTransfer::TYPE => $securityCheckAuthContextTransfer->getType(),
            SecurityCheckAuthContextTransfer::IP => $securityCheckAuthContextTransfer->getIp(),
            SecurityCheckAuthContextTransfer::ACCOUNT => $securityCheckAuthContextTransfer->getAccount(),
            SecurityCheckAuthContextTransfer::LOGIN_IDENTIFIER => $securityCheckAuthContextTransfer->getLoginIdentifier(),
            SecurityCheckAuthContextTransfer::IP_COUNTER_KEY_STRATEGY => $securityCheckAuthContextTransfer->getIpCounterKeyStrategy(),
        ];
    }

    protected function createIpAndUsernameConfig(): SecurityBlockerMerchantPortalGuiConfig
    {
        $securityBlockerMerchantPortalGuiConfig = new SecurityBlockerMerchantPortalGuiConfig();

        return $this->createConfiguredMock(SecurityBlockerMerchantPortalGuiConfig::class, [
            'getIpCounterKeyStrategy' => SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME,
            'getMerchantPortalUserLoginCheckUrl' => $securityBlockerMerchantPortalGuiConfig->getMerchantPortalUserLoginCheckUrl(),
            'getSecurityBlockerMerchantPortalUserEntityType' => $securityBlockerMerchantPortalGuiConfig->getSecurityBlockerMerchantPortalUserEntityType(),
            'getSecurityBlockerLoginRealm' => $securityBlockerMerchantPortalGuiConfig->getSecurityBlockerLoginRealm(),
        ]);
    }

    protected function createFactory(
        SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig
    ): SecurityBlockerMerchantPortalGuiCommunicationFactory {
        $securityBlockerMerchantPortalGuiCommunicationFactory = new SecurityBlockerMerchantPortalGuiCommunicationFactory();
        $securityBlockerMerchantPortalGuiCommunicationFactory->setConfig($securityBlockerMerchantPortalGuiConfig);

        return $securityBlockerMerchantPortalGuiCommunicationFactory;
    }

    protected function createLoginRequest(string $username): Request
    {
        return $this->createLoginRequestWithLoginForm([static::FIELD_USERNAME => $username]);
    }

    /**
     * @param array<string, mixed> $loginForm
     */
    protected function createLoginRequestWithLoginForm(array $loginForm): Request
    {
        $request = Request::create(
            '/login_check',
            Request::METHOD_POST,
            [static::FORM_NAME => $loginForm],
            [],
            [],
            ['REMOTE_ADDR' => static::IP],
        );
        $request->attributes->set('_route', (new SecurityBlockerMerchantPortalGuiConfig())->getMerchantPortalUserLoginCheckUrl());

        return $request;
    }
}
