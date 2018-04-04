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

function block_zoola_roles() {
    return array(
        'zoola_administrator' => array(
            'block/zoola:myaddinstance',
            'block/zoola:addinstance',
            'block/zoola:administrator',
            'block/zoola:public_read'
        ),
        'zoola_domain_designer' => array(
            'block/zoola:user',
            'block/zoola:domain_designer',
            'block/zoola:public_read'
        ),
        'zoola_user' => array(
            'block/zoola:user',
            'block/zoola:public_read'
        ),
        'zoola_dashboards' => array(
            'block/zoola:dashboards',
            'block/zoola:public_read'
        ),
        'zoola_reports' => array(
            'block/zoola:reports',
            'block/zoola:public_read'
        ),
        'zoola_rest_access' => array(
            'webservice/rest:use'
        )
    );
}

/**
 * Set Zoola roles' permissions
 *
 * @global moodle_database $DB
 */
function block_zoola_role_permissions() {
    global $DB;

    $context = context_system::instance();
    $zoola_roles = block_zoola_roles();
    foreach ($zoola_roles as $role => $capabilities) {
        $roleid = $DB->get_field('role', 'id', array('shortname' => $role));
        foreach ($capabilities as $capability) {
            role_change_permission($roleid, $context, $capability, CAP_ALLOW);
        }
    }
}

/**
 * Create or update Zoola roles
 *
 * @global moodle_database $DB
 * @global type $CFG
 */
function block_zoola_role_setup() {
    global $DB, $CFG;

    $zoola_roles = array_keys(block_zoola_roles());
    $zoola_admin_role_id = null;

    $existing_roles = $DB->get_records_select_menu('role', $DB->sql_like('shortname', '?'), array('zoola%'), '', "shortname,id");
    foreach ($zoola_roles as $role) {
        $roleid = false;
        if (!array_key_exists($role, $existing_roles)) {
            $roleid = create_role(
                    get_string($role, 'block_zoola'),
                    $role,
                    get_string($role . '_description', 'block_zoola'));
        } else {
            $roleid = $existing_roles[$role];
            $role_record = new stdClass();
            $role_record->id = $roleid;
            $role_record->name = get_string($role, 'block_zoola');
            $role_record->description = get_string($role . '_description', 'block_zoola');
            $DB->update_record('role', $role_record);
        }
        set_role_contextlevels($roleid, array(CONTEXT_SYSTEM));
        if ($role === 'zoola_administrator') {
            $zoola_admin_role_id = $roleid;
        }
    }

    block_zoola_role_permissions();

    // Assign Zoola Administrator role to site admins.
    $siteadminids = explode(',', $CFG->siteadmins);
    $contextid = context_system::instance();
    $siteadmins = $DB->get_records_list('user', 'id', $siteadminids, '', 'id, deleted');
    foreach ($siteadmins as $admin) {
        if (!$admin->deleted) {
            role_assign($zoola_admin_role_id, $admin->id, $contextid);
        }
    }
}

/**
 * Populate block_zoola_module_name table with existing module names
 *
 * @global moodle_database $DB
 */
function block_zoola_update_module_names() {
    global $DB;

    $DB->delete_records('block_zoola_module_name');

    $modules = $DB->get_records('modules');
    foreach ($modules as $module) {
        $modulenames = $DB->get_records_sql(
                'select cm.id as coursemoduleid, m.name as moduletype, cm.instance as instanceid, t.name as name '
                . 'from {course_modules} cm '
                . 'join {modules} m on cm.module = m.id '
                . 'join {' . $module->name . '} t on cm.instance = t.id '
                . 'where cm.module = ?',
                array($module->id)
        );
        if ($modulenames) {
            $DB->insert_records('block_zoola_module_name', $modulenames);
        }
    }
}
