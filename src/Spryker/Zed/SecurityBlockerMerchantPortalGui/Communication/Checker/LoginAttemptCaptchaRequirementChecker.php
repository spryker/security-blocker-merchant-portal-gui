<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Checker;

use Generated\Shared\Transfer\CaptchaRequirementRequestTransfer;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\SecurityCheckAuthContextBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;

class LoginAttemptCaptchaRequirementChecker implements LoginAttemptCaptchaRequirementCheckerInterface
{
    public function __construct(
        protected SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface $securityBlockerClient,
        protected SecurityCheckAuthContextBuilderInterface $securityCheckAuthContextBuilder,
        protected SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig
    ) {
    }

    public function isCaptchaRequired(CaptchaRequirementRequestTransfer $captchaRequirementRequestTransfer): bool
    {
        if ($captchaRequirementRequestTransfer->getContextOrFail() !== $this->securityBlockerMerchantPortalGuiConfig->getCaptchaContext()) {
            return false;
        }

        return $this->securityBlockerClient->isLoginChallengeRequired(
            $this->securityCheckAuthContextBuilder->createSecurityCheckAuthContextTransferForIp(
                $captchaRequirementRequestTransfer->getIp(),
                $captchaRequirementRequestTransfer->getLoginIdentifier(),
            ),
        );
    }
}
