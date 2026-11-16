<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use Symfony\Component\HttpFoundation\Request;

class SecurityCheckAuthContextBuilder implements SecurityCheckAuthContextBuilderInterface
{
    public function __construct(
        protected SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig
    ) {
    }

    public function createSecurityCheckAuthContextTransfer(Request $request, ?string $loginIdentifier): SecurityCheckAuthContextTransfer
    {
        return $this->createSecurityCheckAuthContextTransferForIp($request->getClientIp(), $loginIdentifier);
    }

    public function createSecurityCheckAuthContextTransferForIp(?string $ip, ?string $loginIdentifier): SecurityCheckAuthContextTransfer
    {
        return (new SecurityCheckAuthContextTransfer())
            ->setType($this->securityBlockerMerchantPortalGuiConfig->getSecurityBlockerMerchantPortalUserEntityType())
            ->setIp($ip)
            ->setRealm($this->securityBlockerMerchantPortalGuiConfig->getSecurityBlockerLoginRealm())
            ->setLoginIdentifier($loginIdentifier)
            ->setIpCounterKeyStrategy($this->securityBlockerMerchantPortalGuiConfig->getIpCounterKeyStrategy());
    }
}
