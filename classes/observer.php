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
 * @author Branko Vukasovic <branko.vukasovic@lambdasolutions.net>
 * @copyright (C) 2017 onwards Lambda Solutions, Inc. (https://www.lambdasolutions.net)
 */

namespace block_zoola;

defined('MOODLE_INTERNAL') || die();

/**
 * Listens for course module changes, and updates block_zoola_modulenames table.
 */
class observer {

    const MODULE_NAME_TABLE = 'block_zoola_module_name';

    /**
     *
     * @global \moodle_database $DB
     * @param \core\event\course_module_created $event
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;
        $modulename = new \stdClass();
        $modulename->coursemoduleid = $event->objectid;
        $modulename->moduletype = $event->other['modulename'];
        $modulename->instanceid = $event->other['instanceid'];
        $modulename->name = $event->other['name'];
        $DB->insert_record(self::MODULE_NAME_TABLE, $modulename);
    }

    /**
     *
     * @global \moodle_database $DB
     * @param \core\event\course_module_updated $event
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;
        $modulename = $DB->get_record(self::MODULE_NAME_TABLE, array('coursemoduleid' => $event->objectid));
        if (!$modulename) {
            // The record should already exist, but just in case it does not we will create a new one.
            $modulename = new \stdClass();
        }
        $modulename->moduletype = $event->other['modulename'];
        $modulename->instanceid = $event->other['instanceid'];
        $modulename->name = $event->other['name'];
        if ($modulename->id) {
            // Update record.
            $DB->update_record(self::MODULE_NAME_TABLE, $modulename);
        } else {
            // The record did not exist, so we need to create it.
            $DB->insert_record(self::MODULE_NAME_TABLE, $modulename);
        }
    }

    /**
     *
     * @global \moodle_database $DB
     * @param \core\event\course_module_deleted $event
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;
        $DB->delete_records(self::MODULE_NAME_TABLE, array('coursemoduleid' => $event->objectid));
    }
}
