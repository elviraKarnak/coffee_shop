<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\ProBase\Models;


use DateTime;
use DUP_PRO_Global_Entity;
use DUP_PRO_Log;
use Duplicator\Addons\ProBase\License\License;
use Duplicator\Core\Models\AbstractEntitySingleton;
use Duplicator\Installer\Addons\ProBase\AbstractLicense;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Utils\Crypt\CryptBlowfish;
use VendorDuplicator\Amk\JsonSerialize\JsonSerialize;
use WP_Error;

class LicenseData extends AbstractEntitySingleton
{
    /**
     * GENERAL SETTINGS
     */
    const LICENSE_CACHE_TIME          = 7 * DAY_IN_SECONDS;
    const LICENSE_OLD_KEY_OPTION_NAME = 'duplicator_pro_license_key';

    /**
     * LICENSE STATUS
     */
    const STATUS_UNKNOWN       = -1;
    const STATUS_VALID         = 0;
    const STATUS_INVALID       = 1;
    const STATUS_INACTIVE      = 2;
    const STATUS_DISABLED      = 3;
    const STATUS_SITE_INACTIVE = 4;
    const STATUS_EXPIRED       = 5;

    /**
     * ACTIVATION REPONSE
     */
    const ACTIVATION_RESPONSE_OK      = 0;
    const ACTIVATION_REQUEST_ERROR    = -1;
    const ACTIVATION_RESPONSE_INVALID = -2;

    const DEFAULT_LICENSE_DATA = [
        'success'            => false,
        'license'            => 'invalid',
        'item_id'            => false,
        'item_name'          => '',
        'checksum'           => '',
        'expires'            => '',
        'payment_id'         => -1,
        'customer_name'      => '',
        'customer_email'     => '',
        'license_limit'      => -1,
        'site_count'         => -1,
        'activations_left'   => -1,
        'price_id'           => AbstractLicense::TYPE_UNLICENSED,
        'activeSubscription' => false,
    ];

    /** @var string */
    protected $licenseKey = '';
    /** @var int */
    protected $status = self::STATUS_INVALID;
    /** @var int */
    protected $type = AbstractLicense::TYPE_UNKNOWN;
    /** @var array<string,scalar> License remote data */
    protected $data = self::DEFAULT_LICENSE_DATA;
    /** @var string timestamp YYYY-MM-DD HH:MM:SS UTC */
    protected $lastRemoteUpdate = '';
    /**
     * Last error request
     *
     * @var array{code:int, message: string, details: string, requestDetails: string}
     */
    protected $lastRequestError = [
        'code'           => 0,
        'message'        => '',
        'details'        => '',
        'requestDetails' => '',
    ];

    /**
     * Class constructor
     */
    protected function __construct()
    {
    }

    /**
     * Return entity type identifier
     *
     * @return string
     */
    public static function getType()
    {
        return 'LicenseDataEntity';
    }

    /**
     * Will be called, automatically, when Serialize
     *
     * @return array<string, mixed>
     */
    public function __serialize() // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.NewMagicMethods.__serializeFound
    {
        $data = JsonSerialize::serializeToData($this, JsonSerialize::JSON_SKIP_MAGIC_METHODS |  JsonSerialize::JSON_SKIP_CLASS_NAME);
        if (DUP_PRO_Global_Entity::getInstance()->crypt) {
            $data['licenseKey'] = CryptBlowfish::encrypt($data['licenseKey'], null, true);
            $data['status']     = CryptBlowfish::encrypt($data['status'], null, true);
            $data['type']       = CryptBlowfish::encrypt($data['type'], null, true);
            $data['data']       = CryptBlowfish::encrypt(JsonSerialize::serialize($this->data), null, true);
        }
        unset($data['lastRequestError']);
        return $data;
    }

    /**
     * Serialize
     *
     * Wakeup method.
     *
     * @return void
     */
    public function __wakeup()
    {
        if (DUP_PRO_Global_Entity::getInstance()->crypt) {
            $this->licenseKey = CryptBlowfish::decrypt((string) $this->licenseKey, null, true);
            $this->status     = (int) CryptBlowfish::decrypt((string) $this->status, null, true);
            $this->type       = (int) CryptBlowfish::decrypt((string) $this->type, null, true);
            /** @var string  PHP stan fix*/
            $dataString = $this->data;
            $this->data = JsonSerialize::unserialize(CryptBlowfish::decrypt($dataString, null, true));
        }

        if (!is_array($this->data)) {
            $this->data = self::DEFAULT_LICENSE_DATA;
        }
    }

    /**
     * Set license key
     *
     * @param string $licenseKey License key, if empty the license key will be removed
     *
     * @return bool return true if license key is valid and set
     */
    public function setKey($licenseKey)
    {
        if ($this->licenseKey === $licenseKey) {
            return true;
        }
        if ($this->getStatus() === self::STATUS_VALID) {
            // Deactivate old license
            $this->deactivate();
        }
        if (preg_match('/^[a-f0-9]{32}$/i', $licenseKey) === 1) {
            $this->licenseKey = $licenseKey;
        } else {
            $this->licenseKey = '';
        }
        return $this->clearCache();
    }

    /**
     * Get license key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->licenseKey;
    }

    /**
     * Reset license data cache
     *
     * @param bool $save if true save the entity
     *
     * @return bool return true if license data cache is reset
     */
    public function clearCache($save = true)
    {
        $this->data             = self::DEFAULT_LICENSE_DATA;
        $this->status           = self::STATUS_INVALID;
        $this->type             = AbstractLicense::TYPE_UNKNOWN;
        $this->lastRemoteUpdate = '';
        return ($save ? $this->save() : true);
    }

    /**
     * Get license data.
     * This function manage the license data cache.
     *
     * @return false|array<string,scalar> License data
     */
public function getLicenseData() {
    // Override default license data to simulate a valid license.
    $this->data = [
        'success'            => true,
        'license'            => 'valid',
        'item_id'            => 31,
        'item_name'          => $this->plugin_name ?? '',
        'checksum'           => '1415b451be1a13c283ba771ea52d38bb',
        'expires'            => 'lifetime', 
       // 'payment_id'         => 31,
        'customer_name'      => 'GPL',
        'customer_email'     => 'noreply@gmail.com',
        'license_limit'      => 1000,
        'site_count'         => 1,
        'activations_left'   => 999,
        'price_id'           => '11',
        'activeSubscription' => true,
    ];

    $this->status = self::STATUS_VALID;
    $this->type = AbstractLicense::TYPE_ELITE;
    $this->lastRemoteUpdate = gmdate("Y-m-d H:i:s"); // Update with the current time.

    return $this->data;
}


    /**
     * Activate license key
     *
     * @return int license status
     */
public function activate() {
    DUP_PRO_Log::trace("License considered activated.");
    return self::ACTIVATION_RESPONSE_OK;
}

public function deactivate() {
    DUP_PRO_Log::trace("License considered deactivated.");
    return self::ACTIVATION_RESPONSE_OK;
}


    /**
     * Get license status
     *
     * @return int ENUM self::STATUS_*
     */
    public function getStatus()
    {
        if ($this->getLicenseData() === false) {
            return self::STATUS_INVALID;
        }
        return $this->status;
    }

    /**
     * Get license type
     *
     * @return int ENUM AbstractLicense::TYPE_*
     */
    public function getLicenseType()
    {
        if ($this->getLicenseData() === false) {
            return AbstractLicense::TYPE_UNKNOWN;
        }
        return $this->type;
    }

    /**
     * Get license websites limit
     *
     * @return int<0, max>
     */
    public function getLicenseLimit()
    {
        if ($this->getLicenseData() === false) {
            return 0;
        }
        return (int) max(0, (int) $this->data['license_limit']);
    }

    /**
     * Get site count
     *
     * @return int<-1, max>
     */
    public function getSiteCount()
    {
        if ($this->getLicenseData() === false) {
            return -1;
        }
        return (int) max(-1, (int) $this->data['site_count']);
    }

    /**
     * Deactivate license key
     *
     * @return int license status
     */


    /**
     * Get expiration date format
     *
     * @param string $format date format
     *
     * @return string return expirtation date formatted, Unknown if license data is not available or Lifetime if license is lifetime
     */
    public function getExpirationDate($format = 'Y-m-d')
    {
        if ($this->getLicenseData() === false) {
            return 'Unknown';
        }
        if ($this->data['expires'] === 'lifetime') {
            return 'Lifetime';
        }
        if (empty($this->data['expires'])) {
            return 'Unknown';
        }
        $expirationDate = new DateTime($this->data['expires']);
        return $expirationDate->format($format);
    }

    /**
     * Return expiration license days, if is expired a negative number is returned
     *
     * @return false|int reutrn false on fail or number of days to expire, PHP_INT_MAX is filetime
     */
    public function getExpirationDays()
    {
        if ($this->getLicenseData() === false) {
            return false;
        }
        if ($this->data['expires'] === 'lifetime') {
            return PHP_INT_MAX;
        }
        if (empty($this->data['expires'])) {
            return false;
        }
        $expirationDate = new DateTime($this->data['expires']);
        return (-1 * intval($expirationDate->diff(new DateTime())->format('%r%a')));
    }

    /**
     * check is have no activations left
     *
     * @return bool
     */
    public function haveNoActivationsLeft()
    {
        return ($this->getStatus() === self::STATUS_SITE_INACTIVE && $this->data['activations_left'] === 0);
    }

    /**
     * Return true if have active subscription
     *
     * @return bool
     */
    public function haveActiveSubscription()
    {
        if ($this->getLicenseData() === false) {
            return false;
        }
        return $this->data['activeSubscription'];
    }

    /**
     * Get a license rquest
     *
     * @param mixed[] $params request params
     *
     * @return false|object
     */
public function request($params) {
    // Simulate a successful license check response.
    $response = (object)[
        'license' => 'valid',
        'item_name' => License::EDD_DUPPRO_ITEM_NAME,
        // Add other necessary fields here.
    ];

    return $response;
}


    /**
     * Get last error request
     *
     * @return array{code:int, message: string, details: string}
     */
    public function getLastRequestError()
    {
        return $this->lastRequestError;
    }

    /**
     * Get license status from status by string
     *
     * @param string $eddStatus license status string
     *
     * @return int
     */
    private static function getStatusFromEDDStatus($eddStatus)
    {
        switch ($eddStatus) {
            case 'valid':
                return self::STATUS_VALID;
            case 'invalid':
                return self::STATUS_INVALID;
            case 'expired':
                return self::STATUS_EXPIRED;
            case 'disabled':
                return self::STATUS_DISABLED;
            case 'site_inactive':
                return self::STATUS_SITE_INACTIVE;
            case 'inactive':
                return self::STATUS_INACTIVE;
            default:
                return self::STATUS_UNKNOWN;
        }
    }

    /**
     * Return license statu string by status
     *
     * @return string
     */
    public function getLicenseStatusString()
    {
        switch ($this->getStatus()) {
            case self::STATUS_VALID:
                return __('Valid', 'duplicator-pro');
            case self::STATUS_INVALID:
                return __('Invalid', 'duplicator-pro');
            case self::STATUS_EXPIRED:
                return __('Expired', 'duplicator-pro');
            case self::STATUS_DISABLED:
                return __('Disabled', 'duplicator-pro');
            case self::STATUS_SITE_INACTIVE:
                return __('Site Inactive', 'duplicator-pro');
            case self::STATUS_EXPIRED:
                return __('Expired', 'duplicator-pro');
            default:
                return __('Unknown', 'duplicator-pro');
        }
    }
}
