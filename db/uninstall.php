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

function xmldb_block_zoola_uninstall() {
    /* @var $DB moodle_database */
    global $DB;

    $zoola_roles = array(
        'zoola_administrator',
        'zoola_domain_designer',
        'zoola_user',
        'zoola_dashboards',
        'zoola_reports'
    );
    $roles = $DB->get_records_list('role', 'shortname', $zoola_roles, '', 'id');
    foreach ($roles as $role) {
        delete_role($role->id);
    }

    return true;
}
