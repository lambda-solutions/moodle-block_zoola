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

require_once('../../config.php');
require_once($CFG->dirroot . "/blocks/zoola/locallib.php");

$courseid = optional_param('courseid', SITEID, PARAM_INT);
$SESSION->zoola_backurl = optional_param('backurl', null, PARAM_URL);

/* @var $PAGE moodle_page */
$PAGE->set_url($CFG->wwwroot.'/blocks/zoola/view.php');
require_login($courseid);
$PAGE->set_title("Zoola");
$PAGE->set_heading("Zoola Dashboard");

if (get_config("block_zoola", "need_upgrade")) {
    if (has_capability("moodle/site:config", context_system::instance())) {
        redirect($CFG->wwwroot . "/blocks/zoola/upgradeusers.php", get_string('need_upgrade_admin_message', 'block_zoola'), 5);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->box(get_string('need_upgrade_message', 'block_zoola'));
        echo $OUTPUT->footer();
        exit();
    }
}

$block_zoola_capabilities = array_keys(block_zoola_capabilities());
if (!has_any_capability($block_zoola_capabilities, $PAGE->context)) {
    if (isset($SESSION->zoola_backurl)) {
        $continueurl = $SESSION->zoola_backurl;
    } else {
        $continueurl = $CFG->wwwroot;
    }
    notice(get_string('rolemissing_error_message', 'block_zoola'), $continueurl);
}

if (!get_config('block_zoola', 'apikey')) {
    redirect($CFG->wwwroot, get_string('noapikey_error_message', 'block_zoola'), 5);
}

$zoola_token = block_zoola_get_token($USER);

if ($zoola_token) {
    $event = \block_zoola\event\zoola_launched::create(array(
        'context' => $PAGE->context
    ));
    $event->trigger();

    \block_zoola\segment_wrapper::identify();
    \block_zoola\segment_wrapper::page('Launch Zoola page', array());
    \block_zoola\segment_wrapper::track('Zoola launched from LMS', array());

    $userTimezone = get_user_timezone();
    if (!empty($SESSION->lang)) {
        // Session language can override other settings.
        $userLocale = $SESSION->lang;
    } else if (!empty($USER->lang)) {
        $userLocale = $USER->lang;
    } else if (isset($CFG->lang)) {
        $userLocale = $CFG->lang;
    } else {
        $userLocale = 'en';
    }
    // Just in case '_utf8' slipped in from somewhere by accident.
    $userLocale = locale_canonicalize(str_replace('_utf8', '', $userLocale));

    $backendurl = rtrim(get_config('block_zoola', 'backendurl'), '/');
    $url = new moodle_url($backendurl . '/', array(
        'pp' => $zoola_token,
        'userLocale' => $userLocale,
        'userTimezone' => $userTimezone
    ));
    redirect($url);
} else {
    redirect($CFG->wwwroot, get_string('notoken_error_message', 'block_zoola'), 5);
}
