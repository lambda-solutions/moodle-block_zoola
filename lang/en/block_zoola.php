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

$string['pluginname'] = 'Zoola';
$string['text_desc'] = 'Reporting solution providing rich data visualizations and executive dashboards.';

$string['zoola:addinstance'] = 'Add a new Zoola block';
$string['zoola:myaddinstance'] = 'Add a new Zoola block to My home';
$string['zoola:administrator'] = 'Administer resources, permissions and other users in Zoola™';
$string['zoola:user'] = 'Create and modify Reports, Dashboards, and Ad Hoc Views in Zoola™';
$string['zoola:dashboards'] = 'View and run Dashboards in Zoola™';
$string['zoola:domain_designer'] = 'Define, design, and edit Domains in Zoola™';
$string['zoola:reports'] = 'View and run Reports in Zoola™';
$string['zoola:public_read'] = 'View and access contents in the Public folder in Zoola™';

$string['eventzoolalaunched'] = 'User launched Zoola';
$string['zoolabutton'] = 'Launch Zoola';
$string['generalsettings'] = 'General settings';
$string['upgradeusers'] = 'Upgrade Zoola users';

$string['noapikey_error_message'] = 'Zoola API Key is not set. Please, contact administrators.';
$string['notoken_error_message'] = "Sorry, you don't have permissions to access this page. Please, contact administrators.";
$string['rolemissing_error_message'] = "Sorry, but you don't have sufficient privileges to access Zoola. Please, contact your Administrator.";
$string['need_upgrade_message'] = "Block Zoola needs to upgrade users. Please, contact administrators.";
$string['need_upgrade_admin_message'] = "Block Zoola needs to upgrade users. Please, go to Block Zoola settings page.";
$string['need_upgrade'] = "Block Zoola needs to upgrade Zoola users.";

$string['apikey'] = 'Backend API KEY';
$string['apikey_desc'] = 'API KEY used for authentication token encryption';

$string['backendurl'] = 'Backend URL';
$string['backendurl_desc'] = 'Backend URL';

$string['organization'] = 'Organization ID';
$string['organization_desc'] = 'Organization ID';

$string['tokenexpiration'] = 'Token expiration';
$string['tokenexpiration_desc'] = 'Time in seconds after which Zoola token expires';

// Default Zoola roles.
$string['zoola_dashboards'] = 'Zoola Dashboards';
$string['zoola_dashboards_description'] = 'This role allows the assigned user permission to view and run Dashboards in Zoola™ - however, they cannot create or modify Dashboards.';

$string['zoola_domain_designer'] = 'Zoola Data Source Designer';
$string['zoola_domain_designer_description'] = 'This role allows the assigned user to define, design, and edit Data Sources in Zoola™. This role also allows assigned users to manage permissions associated to Data Sources.';

$string['zoola_reports'] = 'Zoola Reports';
$string['zoola_reports_description'] = 'This role allows the assigned user permission to view and run Reports in Zoola™ - however, they cannot create or modify Reports or Ad Hoc Views.';

$string['zoola_user'] = 'Zoola User';
$string['zoola_user_description'] = 'This role allows the assigned user to create and modify Reports, Dashboards, and Ad Hoc Views in Zoola™.';

$string['zoola_administrator'] = 'Zoola Administrator';
$string['zoola_administrator_description']
        = 'This role has relative master access and all available permissions in Zoola™. '
        . 'In addition to creating and running Dashboards, Reports, Ad Hoc Views, and Domains, users assigned this role can also manage the permissions and access of other users and roles.';

$string['zoola_rest_access'] = 'Zoola REST Access';
$string['zoola_rest_access_description']
        = 'Set of capabilities required for Zoola™ to be able to access Moodle web services.';
