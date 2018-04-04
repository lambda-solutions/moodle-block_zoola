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

namespace block_zoola;

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Use root level dependencies by default.
if (file_exists($CFG->dirroot . '/vendor/autoload.php')) {
    require_once($CFG->dirroot . '/vendor/autoload.php');
}
// If our dependencies were not loaded, use built-in.
if (!class_exists('\Segment')) {
    require_once(__DIR__ . '/../vendor/segmentio/analytics-php/lib/Segment.php');
}

require_once($CFG->dirroot . '/blocks/zoola/locallib.php');

/**
 * Wrap around Segment class to simplify usage
 *
 * @author vukas
 */
class segment_wrapper {
    protected static $initialized = false;
    protected static $userId;
    protected static $tenant;

    protected static function init() {
        global $USER;
        if (!self::$initialized) {
            $secret = (strpos(get_config('block_zoola', 'backendurl'), 'https://zoola.io/app') === 0) ? 'IxufwqRacJYZ5tRRZxmZUZUD3vRAz7bw' : 'V1clALqD8xLmFpzyF1Gwv1Y8FcS3PzKd';
            \Segment::init($secret);
            self::$tenant = get_config('block_zoola', 'organization');
            self::$userId = $USER->username . '|' . self::$tenant;
            self::$initialized = true;
        }
    }

    protected static function user_locale() {
        global $SESSION, $USER, $CFG;
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
        return $userLocale;
    }

    protected static $context = array();

    protected static function context() {
        global $PAGE, $CFG, $USER, $SEGMENT_VERSION;
        if (empty(self::$context)) {
            /* @var $url \moodle_url */
            $url = $PAGE->url;
            $title = $PAGE->title;
            $search = $url->get_query_string();
            self::$context = array(
                "library" => array(
                    "name" => "analytics-php",
                    "version" => $SEGMENT_VERSION
                ),
                'timezone' => usertimezone(),
                'page' => array(
                    'path' => $url->get_path(),
                    'title' => $title,
                    'url' => $url->out(),
                ),
                'device' => array(
                    'type' => \core_useragent::get_user_device_type()
                ),
                'ip' => $USER->lastip,
                'locale' => self::user_locale()
            );

            // The get_referer() function is deprecated since Moodle 3.0 MDL-49360.
            if (function_exists('get_local_referer')) {
                $referrer = get_local_referer();
            } else {
                $referrer = get_referer();
            }
            if (!empty($referrer)) {
                self::$context['page']['referrer'] = $referrer;
            }
            if (!empty($search)) {
                self::$context['page']['search'] = '?' . $search;
            }

            if (isset($CFG->totara_release)) {
                self::$context['app'] = array(
                    'name' => 'Totara',
                    'version' => $CFG->totara_release
                );
            } else {
                self::$context['app'] = array(
                    'name' => 'Moodle',
                    'version' => $CFG->release
                );
            }
        }

        return self::$context;
    }

    public static function identify() {
        global $USER, $CFG;
        self::init();
        $message = array(
            'anonymousId' => $USER->id . '@' . $CFG->wwwroot,
            'userId' => self::$userId,
            'traits' => array(
                'name' => $USER->firstname . ' ' . $USER->lastname,
                'username' => $USER->username,
                'email' => $USER->email,
                'tenant' => self::$tenant,
                'zoola_roles' => implode(',', block_zoola_get_zoola_roles($USER)),
                'lmsAdmin' => is_siteadmin(),
                'website' => $CFG->wwwroot
            ),
            'context' => self::context()
        );
        $created_at = intval(get_user_preferences('block_zoola_user_created_at', 0, $USER));
        if ($created_at > 0) {
            $message['traits']['created_at'] = $created_at;
        }

        $plugin = new \stdClass();
        require($CFG->dirroot . '/blocks/zoola/version.php');
        $message['traits']['zoolablock'] = "$plugin->release ($plugin->version)";
        if (file_exists($CFG->dirroot . '/blocks/zoola_reports/version.php')) {
            $plugin = new \stdClass();
            require($CFG->dirroot . '/blocks/zoola_reports/version.php');
            $message['traits']['zoolareportsblock'] = "$plugin->release ($plugin->version)";
        } else {
            $message['traits']['zoolareportsblock'] = "Not installed";
        }

        \Segment::identify($message);
        \Segment::group(array(
            'groupId' => $CFG->wwwroot,
            'userId' => self::$userId
        ));
    }

    public static function page($name, array $properties) {
        self::init();
        $context = self::context();
        $message = array(
            'userId' => self::$userId,
            'category' => 'zoola_reports',
            'name' => $name,
            'properties' => array_merge($properties, $context['page'], array('tenant' => self::$tenant)),
            'context' => $context
        );
        \Segment::page($message);
    }

    public static function track($event, array $properties) {
        self::init();
        $message = array(
            'userId' => self::$userId,
            'event' => $event,
            'properties' => array_merge($properties, array('tenant' => self::$tenant)),
            'context' => self::context()
        );
        \Segment::track($message);
    }

    public static function track_install() {
        global $CFG, $USER;

        $plugin = new \stdClass();
        require($CFG->dirroot . '/blocks/zoola/version.php');
        $message = array(
            'anonymousId' => $USER->id . '@' . $CFG->wwwroot,
            'event' => 'Zoola Block plugin installed',
            'properties' => array(
                'lmsUrl' => $CFG->wwwroot,
                'version' => $plugin->version,
                'release' => $plugin->release
            )
        );
        if ($USER->id) {
            $message['properties']['username'] = $USER->username;
            $message['properties']['name'] = $USER->firstname . ' ' . $USER->lastname;
        } else {
            $message['properties']['name'] = 'System User';
        }
        \Segment::init('IxufwqRacJYZ5tRRZxmZUZUD3vRAz7bw');
        \Segment::track($message);
    }
}
