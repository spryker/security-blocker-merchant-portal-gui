<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\SecurityBlockerMerchantPortalGui\Communication\Plugin;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CaptchaRequirementRequestTransfer;
use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Plugin\Captcha\SecurityBlockerMerchantPortalUserCaptchaRequirementPlugin;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\SecurityBlockerMerchantPortalGuiCommunicationFactory;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiDependencyProvider;
use SprykerTest\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group SecurityBlockerMerchantPortalGui
 * @group Communication
 * @group Plugin
 * @group SecurityBlockerMerchantPortalUserCaptchaRequirementPluginTest
 * Add your own group annotations below this line
 */
class SecurityBlockerMerchantPortalUserCaptchaRequirementPluginTest extends Unit
{
    /**
     * @uses \Spryker\Shared\Captcha\CaptchaConfig::CONTEXT_BACKOFFICE_USER_LOGIN
     */
    protected const string OTHER_CAPTCHA_CONTEXT = 'backoffice_user_login';

    protected const string IP = '10.0.0.1';

    protected const string LOGIN_IDENTIFIER = 'admin@spryker.com';

    protected SecurityBlockerMerchantPortalGuiCommunicationTester $tester;

    public function testCaptchaIsRequiredWhileTheSecurityBlockerRequiresAChallenge(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(true);

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer());

        // Assert
        $this->assertTrue($isCaptchaRequired);
    }

    public function testCaptchaIsNotRequiredWithoutAChallenge(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createSecurityBlockerClientMock(false);

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer());

        // Assert
        $this->assertFalse($isCaptchaRequired);
    }

    public function testPluginDoesNotServeAnotherContext(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->expects($this->never())->method('isLoginChallengeRequired');

        // Act
        $isCaptchaRequired = $this->createPlugin($securityBlockerClientMock)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer()->setContext(static::OTHER_CAPTCHA_CONTEXT));

        // Assert
        $this->assertFalse($isCaptchaRequired);
    }

    public function testChallengeIsReadFromThePerIpCounterTheFailedLoginsAreCountedOn(): void
    {
        // Arrange
        $securityBlockerClientMock = $this->createMock(SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface::class);

        // Assert
        $securityBlockerClientMock->expects($this->once())
            ->method('isLoginChallengeRequired')
            ->with($this->callback(fn (SecurityCheckAuthContextTransfer $securityCheckAuthContextTransfer): bool => $securityCheckAuthContextTransfer->getIp() === static::IP
                && $securityCheckAuthContextTransfer->getLoginIdentifier() === static::LOGIN_IDENTIFIER
                && $securityCheckAuthContextTransfer->getIpCounterKeyStrategy() === SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME))
            ->willReturn(false);

        // Act
        $this->createPlugin($securityBlockerClientMock, SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME)
            ->isCaptchaRequired($this->createCaptchaRequirementRequestTransfer());
    }

    protected function createPlugin(
        SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface $securityBlockerClient,
        string $ipCounterKeyStrategy = SecurityBlockerMerchantPortalGuiConfig::IP_COUNTER_KEY_STRATEGY_IP
    ): SecurityBlockerMerchantPortalUserCaptchaRequirementPlugin {
        $this->tester->setDependency(SecurityBlockerMerchantPortalGuiDependencyProvider::CLIENT_SECURITY_BLOCKER, $securityBlockerClient);

        $securityBlockerMerchantPortalGuiConfig = new SecurityBlockerMerchantPortalGuiConfig();
        $securityBlockerMerchantPortalGuiCommunicationFactory = new SecurityBlockerMerchantPortalGuiCommunicationFactory();
        $securityBlockerMerchantPortalGuiCommunicationFactory->setConfig($this->createConfiguredMock(SecurityBlockerMerchantPortalGuiConfig::class, [
            'getCaptchaContext' => $securityBlockerMerchantPortalGuiConfig->getCaptchaContext(),
            'getIpCounterKeyStrategy' => $ipCounterKeyStrategy,
            'getSecurityBlockerMerchantPortalUserEntityType' => $securityBlockerMerchantPortalGuiConfig->getSecurityBlockerMerchantPortalUserEntityType(),
            'getSecurityBlockerLoginRealm' => $securityBlockerMerchantPortalGuiConfig->getSecurityBlockerLoginRealm(),
        ]));

        $securityBlockerBackofficeUserCaptchaRequirementPlugin = new SecurityBlockerMerchantPortalUserCaptchaRequirementPlugin();
        $securityBlockerBackofficeUserCaptchaRequirementPlugin->setFactory($securityBlockerMerchantPortalGuiCommunicationFactory);

        return $securityBlockerBackofficeUserCaptchaRequirementPlugin;
    }

    protected function createSecurityBlockerClientMock(bool $isLoginChallengeRequired): SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface
    {
        $securityBlockerClientMock = $this->createMock(SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface::class);
        $securityBlockerClientMock->method('isLoginChallengeRequired')->willReturn($isLoginChallengeRequired);

        return $securityBlockerClientMock;
    }

    protected function createCaptchaRequirementRequestTransfer(): CaptchaRequirementRequestTransfer
    {
        return (new CaptchaRequirementRequestTransfer())
            ->setContext((new SecurityBlockerMerchantPortalGuiConfig())->getCaptchaContext())
            ->setIp(static::IP)
            ->setLoginIdentifier(static::LOGIN_IDENTIFIER);
    }
}
