<?php
// This file is part of Zoola Analytics block plugin for Moodle.
//
// Zoola Analytics block plugin for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Zoola Analytics block plugin for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Zoola Analytics block plugin for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package block_zoola
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2017 onwards Lambda Solutions, Inc. (https://www.lambdasolutions.net)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/zoola/adminlib.php');

$setting = new admin_setting_configtext('block_zoola/apikey', get_string('apikey', 'block_zoola'),
        get_string('apikey_desc', 'block_zoola'), '', PARAM_TEXT);
$setting->set_updatedcallback('block_zoola_settings_update_callback');
$settings->add($setting);

$setting = new admin_setting_configtext('block_zoola/backendurl', get_string('backendurl', 'block_zoola'),
        get_string('backendurl_desc', 'block_zoola'), 'https://zoola.io/app', PARAM_URL);
$setting->set_updatedcallback('block_zoola_settings_update_callback');
$settings->add($setting);

$setting = new admin_setting_configtext('block_zoola/organization', get_string('organization', 'block_zoola'),
        get_string('organization_desc', 'block_zoola'), '', PARAM_TEXT);
$setting->set_updatedcallback('block_zoola_settings_update_callback');
$settings->add($setting);

$setting = new admin_setting_configtext('block_zoola/tokenexpiration', get_string('tokenexpiration', 'block_zoola'),
        get_string('tokenexpiration_desc', 'block_zoola'), 30, PARAM_INT);
$setting->set_updatedcallback('block_zoola_settings_update_callback');
$settings->add($setting);
