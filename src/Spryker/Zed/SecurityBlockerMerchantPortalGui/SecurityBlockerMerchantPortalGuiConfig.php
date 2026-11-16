<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\SecurityBlockerMerchantPortalGui;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class SecurityBlockerMerchantPortalGuiConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Login protection strategy that counts and blocks the failed Merchant Portal logins per source IP only.
     *
     * @api
     */
    public const string LOGIN_PROTECTION_STRATEGY_IP = 'ip';

    /**
     * Specification:
     * - Login protection strategy with two separate counters of failed Merchant Portal logins: one per source IP and one per account.
     * - Blocks the login when either counter reaches its limit. The account counter ignores the source IP,
     *   so changing the IP does not unlock the account.
     *
     * @api
     */
    public const string LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT = 'ip-and-account';

    protected const string LOGIN_PROTECTION_STRATEGY = self::LOGIN_PROTECTION_STRATEGY_IP;

    /**
     * Specification:
     * - Per-IP counter key strategy that counts the failed Merchant Portal logins of one source IP together, whatever the username.
     *
     * @api
     *
     * @uses \Spryker\Client\SecurityBlocker\SecurityBlockerConfig::IP_COUNTER_KEY_STRATEGY_IP
     */
    public const string IP_COUNTER_KEY_STRATEGY_IP = 'ip';

    /**
     * Specification:
     * - Per-IP counter key strategy that counts the failed Merchant Portal logins per source IP and submitted username.
     * - Keeps every user able to log in when all requests share one source IP, such as a load balancer address.
     *
     * @api
     *
     * @uses \Spryker\Client\SecurityBlocker\SecurityBlockerConfig::IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME
     */
    public const string IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME = 'ip-and-username';

    protected const string IP_COUNTER_KEY_STRATEGY = self::IP_COUNTER_KEY_STRATEGY_IP;

    /**
     * @uses \Spryker\Shared\SecurityBlockerBackoffice\SecurityBlockerBackofficeConfig::LOGIN_REALM_USER
     */
    protected const string SECURITY_BLOCKER_LOGIN_REALM = 'user';

    /**
     * @uses \Spryker\Client\SecurityBlockerMerchantPortal\SecurityBlockerMerchantPortalConfig::MERCHANT_PORTAL_USER_ENTITY_TYPE
     *
     * @var string
     */
    protected const SECURITY_BLOCKER_MERCHANT_PORTAL_USER_ENTITY_TYPE = 'merchant-portal-user';

    /**
     * @see \Spryker\Zed\SecurityMerchantPortalGui\Communication\Plugin\Security\MerchantUserSecurityPlugin::addFirewall().
     *
     * @var string
     */
    protected const MERCHANT_PORTAL_LOGIN_CHECK_URL = 'security-merchant-portal-gui_login_check';

    /**
     * @uses \Spryker\Zed\SecurityMerchantPortalGui\SecurityMerchantPortalGuiConfig::LOGIN_URL
     */
    protected const string MERCHANT_PORTAL_LOGIN_URL = '/security-merchant-portal-gui/login';

    /**
     * @uses \Spryker\Shared\Captcha\CaptchaConfig::CONTEXT_MERCHANT_PORTAL_USER_LOGIN
     */
    protected const string CAPTCHA_CONTEXT = 'merchant_portal_user_login';

    /**
     * Specification:
     * - Returns security blocker merchant portal user entity type.
     *
     * @api
     */
    public function getSecurityBlockerMerchantPortalUserEntityType(): string
    {
        return static::SECURITY_BLOCKER_MERCHANT_PORTAL_USER_ENTITY_TYPE;
    }

    /**
     * Specification:
     * - Returns the login realm that counts the Merchant Portal user login attempts per account.
     * - Must be a realm a `\Spryker\Client\SecurityBlockerExtension\Dependency\Plugin\SecurityBlockerLoginRealmConfigurationExpanderPluginInterface`
     *   plugin configures; a realm no plugin configures gets the default login realm thresholds.
     *
     * @api
     */
    public function getSecurityBlockerLoginRealm(): string
    {
        return static::SECURITY_BLOCKER_LOGIN_REALM;
    }

    /**
     * Specification:
     * - Returns login check URL for merchant portal user.
     *
     * @api
     */
    public function getMerchantPortalUserLoginCheckUrl(): string
    {
        return static::MERCHANT_PORTAL_LOGIN_CHECK_URL;
    }

    /**
     * Specification:
     * - Returns the login form URL a blocked merchant user login sent as a JSON request is redirected to, with the block message.
     *
     * @api
     */
    public function getMerchantPortalUserLoginUrl(): string
    {
        return static::MERCHANT_PORTAL_LOGIN_URL;
    }

    /**
     * Specification:
     * - Returns the key of the login protection strategy of the Merchant Portal login form.
     * - {@link LOGIN_PROTECTION_STRATEGY_IP} (default): failed logins are counted and blocked per source IP only.
     * - {@link LOGIN_PROTECTION_STRATEGY_IP_AND_ACCOUNT}: failed logins also lock the account regardless of the source IP.
     * - A project strategy adds its key to `SecurityBlockerMerchantPortalGuiCommunicationFactory::createLoginProtectionEventSubscriberStrategyResolver()`.
     * - A key no strategy resolver knows fails with an exception.
     *
     * @api
     */
    public function getLoginProtectionStrategy(): string
    {
        return static::LOGIN_PROTECTION_STRATEGY;
    }

    /**
     * Specification:
     * - Returns the key of the per-IP counter key strategy of the Merchant Portal login form, used by every login protection strategy.
     * - {@link IP_COUNTER_KEY_STRATEGY_IP} (default): the per-IP counter key holds the source IP only, so the failed logins of all users
     *   behind one source IP share one counter.
     * - {@link IP_COUNTER_KEY_STRATEGY_IP_AND_USERNAME}: the per-IP counter key also holds a hash of the submitted username, normalized like
     *   the account counter (trimmed, case-folded, accents stripped), so one source IP gets a separate counter per username. A login without a
     *   username keeps the source IP only key.
     * - Passes the key as `SecurityCheckAuthContextTransfer.ipCounterKeyStrategy`; the SecurityBlocker client builds the counter key from it.
     *   A project strategy adds its key to `\Spryker\Client\SecurityBlocker\SecurityBlockerFactory::createIpCounterKeyAccountResolverStrategyResolver()`.
     * - A key the SecurityBlocker client does not know fails the login check with an exception; the CAPTCHA requirement check then asks for a
     *   CAPTCHA and logs an error.
     *
     * @api
     */
    public function getIpCounterKeyStrategy(): string
    {
        return static::IP_COUNTER_KEY_STRATEGY;
    }

    /**
     * Specification:
     * - Returns the CAPTCHA context of the Merchant Portal login form, the only context `SecurityBlockerMerchantPortalUserCaptchaRequirementPlugin` serves.
     *
     * @api
     */
    public function getCaptchaContext(): string
    {
        return static::CAPTCHA_CONTEXT;
    }
}
