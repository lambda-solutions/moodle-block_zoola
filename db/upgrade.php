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

require_once('upgradelib.php');

/**
 *
 * @global moodle_database $DB
 * @param int|string $oldversion
 * @return boolean
 */
function xmldb_block_zoola_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017121500) {

        // Define table block_zoola_module_name to be created.
        $table = new xmldb_table('block_zoola_module_name');

        // Adding fields to table block_zoola_module_name.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursemoduleid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('moduletype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_zoola_module_name.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('course_modules', XMLDB_KEY_FOREIGN_UNIQUE, array('coursemoduleid'), 'course_modules', array('id'));

        // Adding indexes to table block_zoola_module_name.
        $table->add_index('moduleinstance', XMLDB_INDEX_UNIQUE, array('moduletype', 'instanceid'));

        // Conditionally launch create table for block_zoola_module_name.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        block_zoola_update_module_names();

        // Zoola savepoint reached.
        upgrade_block_savepoint(true, 2017121500, 'zoola');
    }

    return true;
}
