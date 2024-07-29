<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Addons\GDriveAddon\Models;

use DUP_PRO_Google_Drive_Transfer_Mode;
use DUP_PRO_Log;
use DUP_PRO_Package_Upload_Info;
use DUP_PRO_Server;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Models\DynamicGlobalEntity;
use Duplicator\Models\Storages\AbstractStorageEntity;
use Duplicator\Models\Storages\StorageAuthInterface;
use Duplicator\Utils\OAuth\TokenEntity;
use Duplicator\Utils\OAuth\TokenService;
use Exception;

/**
 * @property GDriveAdapter $adapter
 */
class GDriveStorage extends AbstractStorageEntity implements StorageAuthInterface
{
    // These numbers represent clients created in Google Cloud Console
    const GDRIVE_CLIENT_NATIVE  = 1; // Native client 1
    const GDRIVE_CLIENT_WEB0722 = 2; // Web client 07/2022
    const GDRIVE_CLIENT_LATEST  = 2; // Latest out of these above

    const REQUIRED_SCOPES = [
        "openid",
        "https://www.googleapis.com/auth/userinfo.profile",
        "https://www.googleapis.com/auth/userinfo.email",
        // The drive.file scope limits access to just those files created by the plugin
        "https://www.googleapis.com/auth/drive.file",
    ];

    /**
     * Get default config
     *
     * @return array<string,scalar>
     */
    protected static function getDefaultConfig()
    {
        $config = parent::getDefaultConfig();
        $config = array_merge(
            $config,
            [
                'storage_folder_id'      => '',
                'storage_folder_web_url' => '',
                'token_json'             => '',
                'refresh_token'          => '',
                'client_number'          => -1,
                'authorized'             => false,
            ]
        );
        return $config;
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
        parent::__wakeup();

        if ($this->legacyEntity) {
            // Old storage entity
            $this->legacyEntity = false;
            // Make sure the storage type is right from the old entity
            $this->storage_type = $this->getSType();
            $this->config       = [
                'token_json'     => $this->gdrive_access_token_set_json,
                'refresh_token'  => $this->gdrive_refresh_token,
                'storage_folder' => ltrim($this->gdrive_storage_folder, '/\\'),
                'client_number'  => $this->gdrive_client_number,
                'max_packages'   => $this->gdrive_max_files,
                'authorized'     => ($this->gdrive_authorization_state == 1),
            ];
            // reset old values
            $this->gdrive_access_token_set_json = '';
            $this->gdrive_refresh_token         = '';
            $this->gdrive_storage_folder        = '';
            $this->gdrive_client_number         = -1;
            $this->gdrive_max_files             = 10;
            $this->gdrive_authorization_state   = 0;
        }
    }

    /**
     * Return the storage type
     *
     * @return int
     */
    public static function getSType()
    {
        return 3;
    }

    /**
     * Returns the storage type icon.
     *
     * @return string Returns the storage icon
     */
    public static function getStypeIcon()
    {
        $imgUrl = DUPLICATOR_PRO_IMG_URL . '/google-drive.svg';
        return '<img src="' . esc_url($imgUrl) . '" class="dup-storage-icon" alt="' . esc_attr(static::getStypeName()) . '" />';
    }

    /**
     * Returns the storage type name.
     *
     * @return string
     */
    public static function getStypeName()
    {
        return __('Google Drive', 'duplicator-pro');
    }

    /**
     * Get storage location string
     *
     * @return string
     */
    public function getLocationString()
    {
        if ($this->isAuthorized()) {
            return $this->config['storage_folder_web_url'];
        } else {
            return __('Not Authenticated', 'duplicator-pro');
        }
    }

    /**
     * Check if storage is supported
     *
     * @return bool
     */
    public static function isSupported()
    {
        return (SnapUtil::isCurlEnabled() || SnapUtil::isUrlFopenEnabled());
    }

    /**
     * Get supported notice, displayed if storage isn't supported
     *
     * @return string html string or empty if storage is supported
     */
    public static function getNotSupportedNotice()
    {
        if (static::isSupported()) {
            return '';
        }

        if (!SnapUtil::isCurlEnabled() && !SnapUtil::isUrlFopenEnabled()) {
            return esc_html__(
                'Google Drive requires either the PHP CURL extension enabled or the allow_url_fopen runtime configuration to be enabled.',
                'duplicator-pro'
            );
        } elseif (!SnapUtil::isCurlEnabled()) {
            return esc_html__('Google Drive requires the PHP CURL extension enabled.', 'duplicator-pro');
        } else {
            return esc_html__('Google Drive requires the allow_url_fopen runtime configuration to be enabled.', 'duplicator-pro');
        }
    }

    /**
     * Get upload chunk size in bytes
     *
     * @return int bytes
     */
    public function getUploadChunkSize()
    {
        $dGlobal     = DynamicGlobalEntity::getInstance();
        $chunkSizeKb = $dGlobal->getVal('gdrive_upload_chunksize_in_kb', 256);

        return $chunkSizeKb * KB_IN_BYTES;
    }

    /**
     * Get upload chunk timeout in seconds
     *
     * @return int timeout in microseconds, 0 unlimited
     */
    public function getUploadChunkTimeout()
    {
        // @todo: fixed to 10 seconds for historical reasons, make it configurable.
        return 10 * 1000000;
    }

    /**
     * Check if storage is valid
     *
     * @return bool Return true if storage is valid and ready to use, false otherwise
     */
    public function isValid()
    {
        return $this->isAuthorized();
    }

    /**
     * Is autorized
     *
     * @return bool
     */
    public function isAuthorized()
    {
        return $this->config['authorized'];
    }

    /**
     * Returns an HTML anchor tag of location
     *
     * @return string Returns an HTML anchor tag with the storage location as a hyperlink.
     */
    public function getHtmlLocationLink()
    {
        if (! $this->isAuthorized() || empty($this->config['storage_folder_web_url'])) {
            return '<span>' . esc_html($this->getStorageFolder()) . '</span>';
        }

        return sprintf("<a href=\"%s\" target=\"_blank\">%s</a>", esc_url($this->config['storage_folder_web_url']), esc_html($this->getStorageFolder()));
    }

    /**
     * Authorized from HTTP request
     *
     * @param string $message Message
     *
     * @return bool True if authorized, false if failed
     */
    public function authorizeFromRequest(&$message = '')
    {
        $tokenPairString = '';
        try {
            if (($refreshToken = SnapUtil::sanitizeTextInput(SnapUtil::INPUT_REQUEST, 'auth_code')) === '') {
                throw new Exception(__('Authorization code is empty', 'duplicator-pro'));
            }

            $this->name                     = SnapUtil::sanitizeTextInput(SnapUtil::INPUT_REQUEST, 'name', '');
            $this->notes                    = SnapUtil::sanitizeDefaultInput(SnapUtil::INPUT_REQUEST, 'notes', '');
            $this->config['max_packages']   = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'max_packages', 10);
            $this->config['storage_folder'] = self::getSanitizedInputFolder('storage_folder', 'remove');

            $this->revokeAuthorization();

            $token = (new TokenEntity(static::getSType(), ['refresh_token' => $refreshToken]));
            if (!$token->refresh(true)) {
                throw new Exception(__('Failed to fetch information from Google Drive. Make sure the token is valid.', 'duplicator-pro'));
            }

            if (empty($token->getScope())) {
                throw new Exception(__("Couldn't connect. Google Drive scopes not found.", 'duplicator-pro'));
            }

            if (! $token->hasScopes(static::REQUIRED_SCOPES)) {
                throw new Exception(
                    __(
                        "Authorization failed. You did not allow all required permissions. Try again and make sure that you checked all checkboxes.",
                        'duplicator-pro'
                    )
                );
            }

            $this->config['refresh_token'] = $token->getRefreshToken();
            $this->config['token_json']    = wp_json_encode([
                'created'       => $token->getCreated(),
                'access_token'  => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires_in'    => $token->getExpiresIn(),
                'scope'         => $token->getScope(),
            ]);
            $this->config['client_number'] = self::GDRIVE_CLIENT_LATEST;

            $this->config['authorized'] = $token->isValid();
        } catch (Exception $e) {
            DUP_PRO_Log::traceException($e, "Problem authorizing Google Drive access token");
            DUP_PRO_Log::traceObject('Token pair string from authorization:', $tokenPairString);
            $message = $e->getMessage();
            return false;
        }
        $this->save();

        $message = __('Google Drive is connected successfully and Storage Provider Updated.', 'duplicator-pro');
        return true;
    }

    /**
     * Revokes authorization
     *
     * @param string $message Message
     *
     * @return bool True if authorized, false if failed
     */
    public function revokeAuthorization(&$message = '')
    {
        if (!$this->isAuthorized()) {
            $message = __('Google Drive isn\'t authorized.', 'duplicator-pro');
            return true;
        }

        try {
            $client = $this->getAdapter()->getService()->getClient();

            if (!empty($this->config['refresh_token'])) {
                $client->revokeToken($this->config['refresh_token']);
            }

            $accessTokenObj = json_decode($this->config['token_json']);
            if (is_object($accessTokenObj) && property_exists($accessTokenObj, 'access_token')) {
                $gdrive_access_token = $accessTokenObj->access_token;
            } else {
                $gdrive_access_token = false;
            }

            if (!empty($gdrive_access_token)) {
                $client->revokeToken($gdrive_access_token);
            }

            $this->config['token_json']    = '';
            $this->config['refresh_token'] = '';
            $this->config['client_number'] = -1;
            $this->config['authorized']    = false;
        } catch (Exception $e) {
            DUP_PRO_Log::trace("Problem revoking Google Drive access token msg: " . $e->getMessage());
            $message = $e->getMessage();
            return false;
        }

        $message = __('Google Drive is disconnected successfully.', 'duplicator-pro');
        return true;
    }

    /**
     * Get authorization URL
     *
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return (new TokenService(static::getSType()))->getRedirectUri();
    }

    /**
     * Get storage adapter
     *
     * @return GDriveAdapter
     */
    public function getAdapter()
    {
        if (! $this->adapter) {
            $token = $this->getTokenFromConfig();
            if (! isset($this->config['storage_folder_id']) || empty($this->config['storage_folder_id'])) {
                $this->adapter = new GDriveAdapter($token, $this->config['storage_folder'], '');
                $this->adapter->initialize();
                $storageFolder                          = $this->adapter->getPathInfo('/');
                $this->config['storage_folder_id']      = $storageFolder->id;
                $this->config['storage_folder_web_url'] = $storageFolder->webUrl;
                $this->save();
            } else {
                $this->adapter = new GDriveAdapter($token, $this->config['storage_folder'], $this->config['storage_folder_id']);
                $this->adapter->initialize();
            }
        }

        return $this->adapter;
    }

    /**
     * Render form config fields
     *
     * @param bool $echo Echo or return
     *
     * @return string
     */
    public function renderConfigFields($echo = true)
    {
        $userInfo    = false;
        $quotaString = '';

        if ($this->isAuthorized()) {
            $adapter      = $this->getAdapter();
            $serviceDrive = $adapter->getService();
            $optParams    = array('fields' => '*');
            $about        = $serviceDrive->about->get($optParams);
            $storageQuota = $about->getStorageQuota();
            $quota_total  = max($storageQuota->getLimit(), 1);
            $quota_used   = $storageQuota->getUsage();
            $userInfo     = $about->getUser();

            if (is_numeric($quota_total) && is_numeric($quota_used)) {
                $available_quota = $quota_total - $quota_used;
                $used_perc       = round($quota_used * 100 / $quota_total, 1);
                $quotaString     = sprintf(
                    __('%1$s%% used, %2$s available', 'duplicator-pro'),
                    $used_perc,
                    size_format($available_quota)
                );
            }
        }

        return TplMng::getInstance()->render(
            'gdriveaddon/configs/google_drive',
            [
                'storage'       => $this,
                'storageFolder' => $this->config['storage_folder'],
                'maxPackages'   => $this->config['max_packages'],
                'userInfo'      => $userInfo,
                'quotaString'   => $quotaString,
            ],
            $echo
        );
    }

    /**
     * Update data from http request, this method don't save data, just update object properties
     *
     * @param string $message Message
     *
     * @return bool True if success and all data is valid, false otherwise
     */
    public function updateFromHttpRequest(&$message = '')
    {
        if ((parent::updateFromHttpRequest($message) === false)) {
            return false;
        }

        $previousStorageFolder          = $this->config['storage_folder'];
        $this->config['max_packages']   = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, 'gdrive_max_files', 10);
        $this->config['storage_folder'] = self::getSanitizedInputFolder('_gdrive_storage_folder', 'remove');

        if ($previousStorageFolder !== $this->config['storage_folder']) {
            $this->config['storage_folder_id']      = '';
            $this->config['storage_folder_web_url'] = '';
        }

        $message = sprintf(
            __('Google Drive Storage Updated.', 'duplicator-pro'),
            $this->config['server'],
            $this->getStorageFolder()
        );
        return true;
    }

    /**
     * Set google drive archive offset
     *
     * @param DUP_PRO_Package_Upload_Info $upload_info Upload info
     *
     * @return void
     */
    protected function setArchiveOffset(DUP_PRO_Package_Upload_Info $upload_info)
    {
        $resume_url = $upload_info->upload_id;
        if (is_null($resume_url)) {
            $upload_info->archive_offset = 0;
        } else {
            $args          = array(
                'headers' => array(
                    'Content-Length' => "0",
                    'Content-Range'  => "bytes */*",
                ),
                'method'  => 'PUT',
                'timeout' => 60,
            );
            $response      = wp_remote_request($resume_url, $args);
            $response_code = wp_remote_retrieve_response_code($response);
            DUP_PRO_Log::infoTrace("Google Drive API response code: $response_code");
            // error_log('response code:'.$response_code);
            switch ($response_code) {
                case 308:
                    DUP_PRO_Log::infoTrace("Google Drive transfer is incomplete.");
                    // error_log("Google Drive transfer is incomplete");
                    $range = wp_remote_retrieve_header($response, 'range');
                    if (!empty($range) && preg_match('/bytes=0-(\d+)$/', $range, $matches)) {
                        $upload_info->archive_offset = 1 + (int) $matches[1];
                    } else {
                        $upload_info->archive_offset = 0;
                    }
                    break;
                case 200:
                case 201:
                    DUP_PRO_Log::infoTrace("SUCCESS: archive upload to Google Drive.");
                    $upload_info->copied_archive = true;
                    $this->purgeOldPackages();
                    break;
                case 404:
                default:
                    $upload_info->archive_offset = 0;
                    break;
            }
        }
        // error_log("Setting archive offset to the ".$upload_info->archive_offset);
        DUP_PRO_Log::trace("Setting archive offset to the " . $upload_info->archive_offset);
    }

    /**
     * Get the token entity from config
     *
     * @return TokenEntity
     */
    protected function getTokenFromConfig()
    {
        $token = new TokenEntity(static::getSType(), $this->config['token_json']);
        if ($token->isAboutToExpire()) {
            try {
                $token->refresh(true);
            } catch (Exception $e) {
                DUP_PRO_Log::traceException($e, "Problem refreshing Google Drive access token");
            }
            $this->config['token_json'] = wp_json_encode([
                'created'       => $token->getCreated(),
                'access_token'  => $token->getAccessToken(),
                'refresh_token' => $token->getRefreshToken(),
                'expires_in'    => $token->getExpiresIn(),
                'scope'         => $token->getScope(),
            ]);
            $this->save();
        }
        return $token;
    }

    /**
     * @return void
     */
    public static function registerType()
    {
        parent::registerType();

        add_action('duplicator_update_global_storage_settings', function () {
            $dGlobal = DynamicGlobalEntity::getInstance();

            foreach (static::getDefaultSettings() as $key => $default) {
                $value = SnapUtil::sanitizeIntInput(SnapUtil::INPUT_REQUEST, $key, $default);
                $dGlobal->setVal($key, $value);
            }
        });
    }

    /**
     * Get default settings
     *
     * @return array<string, scalar>
     */
    protected static function getDefaultSettings()
    {
        return [
            'gdrive_upload_chunksize_in_kb' => 1024,
            'gdrive_transfer_mode'          => DUP_PRO_Google_Drive_Transfer_Mode::Auto,
        ];
    }

    /**
     * @return void
     */
    public static function renderGlobalOptions()
    {
        $values  = static::getDefaultSettings();
        $dGlobal = DynamicGlobalEntity::getInstance();
        foreach ($values as $key => $default) {
            $values[$key] = $dGlobal->getVal($key, $default);
        }
        ?>
        <h3 class="title"><?php echo esc_html(static::getStypeName()); ?></h3>
        <hr size="1" />
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Upload Chunk Size", 'duplicator-pro'); ?></label></th>
                <td>
                    <input
                        class="dup-narrow-input text-right"
                        name="gdrive_upload_chunksize_in_kb"
                        id="gdrive_upload_chunksize_in_kb"
                        type="number"
                        min="256"
                        data-parsley-required
                        data-parsley-type="number"
                        data-parsley-errors-container="#gdrive_upload_chunksize_in_kb_error_container"
                        value="<?php echo (int) $values['gdrive_upload_chunksize_in_kb']; ?>"
                    >&nbsp;<b>KB</b>
                    <div id="gdrive_upload_chunksize_in_kb_error_container" class="duplicator-error-container"></div>
                    <p class="description">
                        <?php esc_html_e(
                            'How much should be uploaded to Google Drive per attempt. Higher=faster but less reliable. It should be multiple of 256.',
                            'duplicator-pro'
                        ); ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Transfer Mode", 'duplicator-pro'); ?></label></th>
                <td>
                    <input
                        type="radio"
                        value="<?php echo (int) DUP_PRO_Google_Drive_Transfer_Mode::Auto ?>"
                        name="gdrive_transfer_mode" id="gdrive_transfer_mode_auto"
                        <?php checked($values['gdrive_transfer_mode'], DUP_PRO_Google_Drive_Transfer_Mode::Auto); ?>
                    >
                    <label for="gdrive_transfer_mode_auto"><?php esc_html_e("Auto", 'duplicator-pro'); ?></label> &nbsp;

                    <input
                        type="radio" <?php disabled(!DUP_PRO_Server::isURLFopenEnabled()) ?>
                        value="<?php echo (int) DUP_PRO_Google_Drive_Transfer_Mode::FOpen_URL ?>"
                        name="gdrive_transfer_mode"
                        id="gdrive_transfer_mode_stream"
                        <?php checked($values['gdrive_transfer_mode'], DUP_PRO_Google_Drive_Transfer_Mode::FOpen_URL); ?>
                    >
                    <label for="gdrive_transfer_mode_stream"><?php esc_html_e("FOpen URL", 'duplicator-pro'); ?></label> &nbsp;
                    <?php if (!DUP_PRO_Server::isURLFopenEnabled()) : ?>
                        <i
                            class="fas fa-question-circle fa-sm"
                            data-tooltip-title="<?php esc_attr_e("FOpen URL", 'duplicator-pro'); ?>"
                            data-tooltip="<?php esc_attr_e('Not available because "allow_url_fopen" is turned off in the php.ini', 'duplicator-pro'); ?>">
                        </i>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }
}
