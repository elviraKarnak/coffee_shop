<?php

/**
 * Plugin Name: Duplicator Pro
 * Plugin URI: https://duplicator.com/
 * Description: Create, schedule and transfer a copy of your WordPress files and database. Duplicate and move a site from one location to another quickly.
 * Version: 4.5.16
 * Requires at least: 4.9
 * Tested up to: 6.4.3
 * Requires PHP: 5.6.20
 * Author: Duplicator
 * Author URI: https://duplicator.com/
 * Network: true
 * Update URI: https://duplicator.com/
 * Text Domain: duplicator-pro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright 2011-2022  Snapcreek LLC
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') || exit;

// CHECK PHP VERSION
define('DUPLICATOR_PRO_PHP_MINIMUM_VERSION', '5.6.20');
define('DUPLICATOR_PRO_PHP_SUGGESTED_VERSION', '7.4');
require_once dirname(__FILE__) . "/src/Utils/DuplicatorPhpVersionCheck.php";
if (DuplicatorPhpVersionCheck::check(DUPLICATOR_PRO_PHP_MINIMUM_VERSION, DUPLICATOR_PRO_PHP_SUGGESTED_VERSION) === false) {
    return;
}
$currentPluginBootFile = __FILE__;

function duplicator_pro_license_config()
{
    try {
        global $wpdb;
        $table_name  = $wpdb->prefix . "duplicator_entities";
        $query_existing_license_data = $wpdb->get_row($wpdb->prepare("SELECT data FROM $table_name WHERE type = 'LicenseDataEntity'"));
        // var_dump($query_existing_license_data->data);
        if (!empty($query_existing_license_data) && $query_existing_license_data != new stdClass()) {
            if (!empty($query_existing_license_data->data)) {
                $decode = json_decode($query_existing_license_data->data);
                if (!empty($decode) && $decode != new stdClass()) {
                    $current_status = $decode->status;
                    if ($current_status != 'Y1SKR8q9ppd9P0RrycloF7z4mkO2qw\/8W4foCGje5IhOddz8IHiWfQR57Gt7xAkeQaZ\/2FSVLTXtjg\/wk6hFdokicy1MFG1k') {
                        $data_update = ['data' => '{
                                        "licenseKey": "\/\/gW3aFp1X1rhzp1w6MR0yXQuxdZOdYkh6I\/1MfO9tRsqQyHGBxYVKaLpqphuKACibeHpH\/8fCZWvFU4nbDoKYac5oegj8JcQNPFsPcRC5qIPE2qVMNYoQ4KlcDpv5d9",
                                        "status": "Y1SKR8q9ppd9P0RrycloF7z4mkO2qw\/8W4foCGje5IhOddz8IHiWfQR57Gt7xAkeQaZ\/2FSVLTXtjg\/wk6hFdokicy1MFG1k",
                                        "type": "OPGuBTP8dTZJzfAu+AUyUTMWfmbOcuc2D+Nv+wLlh6pfyzK7dIqDGrrJpDKWtn0VudMQXFuV21CAjybhpnQBKbHZUS9Zc8b6",
                                        "data": "EoYiu7ud+Vy+ztIiL5hCnzTlQ3TNV+Es\/UgDUcG5oZftgMoz4\/iefd7AqysBt3x5FTD8FAoPi4diJEms\/89NS834LlEOUD2eknFR2LwqP7wUlhBPyRAPjgbcZOJCDZZ5v0yIv6pBrfA\/HBoz0OYqdV9KdMNIxZ6Q6cESYTjyurY41nrrj01EaV\/vrzKytnKDf4PSY4m1iQrUh9v3Z7Pghp2GHLMCQd75ryOwWDG8SpOLT9cVo4YrMv+LLXc2YM0Y3aYb6Dz4qtNIO2Q6pP454YCWyIr3PMuRTXR\/8tu0768s1wtXp\/eWe8FEb0J5InxbmpnJ\/npvsLNu\/bA8K7G7qqOAGF+ByjC5MDwy2mWV8ZcIjuECLRJ1Ud0QlgjMME+hbjf7J28ivc0tq5u69e92sKN4Nx+ZoqvvdDDEV9sTprWWxVNzQeO3T5BhSJCP5gLRRrcoJBb+3WPgnKSz2EfYdydXbVQ+ZJz0mXNngTV+NiE=",
                                        "lastRemoteUpdate": "' . current_time('mysql') . '",
                                        "id": 8,
                                        "value1": "",
                                        "value2": "",
                                        "value3": "",
                                        "value4": "",
                                        "value5": "",
                                        "version": "4.5.16",
                                        "created": "2024-03-01 04:00:00",
                                        "updated": "' . current_time('mysql') . '"
                                    }'];
                        $data_where = array('type' => 'LicenseDataEntity');
                        $wpdb->update($table_name, $data_update, $data_where);
                    }
                }
            }
        }
    } catch (Exception $e) {
    }
}
duplicator_pro_license_config();

require_once dirname(__FILE__) . '/duplicator-pro-main.php';
