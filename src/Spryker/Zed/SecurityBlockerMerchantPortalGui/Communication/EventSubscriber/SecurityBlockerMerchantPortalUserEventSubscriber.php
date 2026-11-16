<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\EventSubscriber;

use Generated\Shared\Transfer\SecurityCheckAuthContextTransfer;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Communication\Builder\MessageBuilderInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\Dependency\Client\SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface;
use Spryker\Zed\SecurityBlockerMerchantPortalGui\SecurityBlockerMerchantPortalGuiConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;

class SecurityBlockerMerchantPortalUserEventSubscriber implements EventSubscriberInterface
{
    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm::FORM_NAME
     *
     * @var string
     */
    protected const FORM_NAME = 'security-merchant-portal-gui';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\Communication\Form\MerchantLoginForm::FIELD_USERNAME
     *
     * @var string
     */
    protected const FIELD_USERNAME = 'username';

    /**
     * @var string
     */
    protected const ROUTE_KEY = '_route';

    /**
     * Runs before the CAPTCHA check of the Captcha module (priority 10), so a blocked login is answered before its CAPTCHA is used.
     *
     * @var int
     */
    protected const KERNEL_REQUEST_SUBSCRIBER_PRIORITY = 11;

    /**
     * @var string
     */
    protected const AUTHENTICATION_FAILURE_EVENT = 'onAuthenticationFailure';

    protected RequestStack $requestStack;

    protected SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface $securityBlockerClient;

    protected SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig;

    protected MessageBuilderInterface $messageBuilder;

    public function __construct(
        RequestStack $requestStack,
        SecurityBlockerMerchantPortalGuiToSecurityBlockerClientInterface $securityBlockerClient,
        MessageBuilderInterface $messageBuilder,
        SecurityBlockerMerchantPortalGuiConfig $securityBlockerMerchantPortalGuiConfig
    ) {
        $this->requestStack = $requestStack;
        $this->securityBlockerClient = $securityBlockerClient;
        $this->messageBuilder = $messageBuilder;
        $this->securityBlockerMerchantPortalGuiConfig = $securityBlockerMerchantPortalGuiConfig;
    }

    /**
     * @return array<string, array<int, int|string>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginFailureEvent::class => static::AUTHENTICATION_FAILURE_EVENT,
            KernelEvents::REQUEST => ['onKernelRequest', static::KERNEL_REQUEST_SUBSCRIBER_PRIORITY],
        ];
    }

    public function onAuthenticationFailure(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$this->isLoginAttempt($request)) {
            return;
        }

        $securityCheckAuthContextTransfer = $this->createSecurityCheckAuthContextTransfer($request);
        $this->securityBlockerClient->incrementLoginAttemptCount($securityCheckAuthContextTransfer);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isLoginAttempt($request)) {
            return;
        }

        $securityCheckAuthContextTransfer = $this->createSecurityCheckAuthContextTransfer($request);
        $securityCheckAuthResponseTransfer = $this->securityBlockerClient->isAccountBlocked($securityCheckAuthContextTransfer);

        if (!$securityCheckAuthResponseTransfer->getIsBlocked()) {
            return;
        }

        $exceptionMessage = $this->messageBuilder->getExceptionMessage($securityCheckAuthResponseTransfer);

        throw new HttpException(
            Response::HTTP_TOO_MANY_REQUESTS,
            $exceptionMessage,
            null,
            [],
            Response::HTTP_TOO_MANY_REQUESTS,
        );
    }

    protected function isLoginAttempt(Request $request): bool
    {
        $currentRoute = $request->attributes->get(static::ROUTE_KEY);
        $configuredRoute = $this->securityBlockerMerchantPortalGuiConfig->getMerchantPortalUserLoginCheckUrl();

        return $currentRoute === $configuredRoute
            && $request->getMethod() === Request::METHOD_POST;
    }

    protected function createSecurityCheckAuthContextTransfer(Request $request): SecurityCheckAuthContextTransfer
    {
        return (new SecurityCheckAuthContextTransfer())
            ->setType($this->securityBlockerMerchantPortalGuiConfig->getSecurityBlockerMerchantPortalUserEntityType())
            ->setIp($request->getClientIp())
            ->setLoginIdentifier($this->findSubmittedUsername($request))
            ->setIpCounterKeyStrategy($this->securityBlockerMerchantPortalGuiConfig->getIpCounterKeyStrategy());
    }

    protected function findSubmittedUsername(Request $request): ?string
    {
        $loginForm = $request->request->all()[static::FORM_NAME] ?? null;
        $username = is_array($loginForm) ? ($loginForm[static::FIELD_USERNAME] ?? null) : null;

        return is_string($username) ? $username : null;
    }
}
