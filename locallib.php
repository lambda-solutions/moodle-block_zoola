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

function block_zoola_token_signature($token) {
    $token_expiration = intval(get_config('block_zoola', 'tokenexpiration'));
    if ($token_expiration == 0) {
        $token_expiration = 30;  // Set to 30 seconds by default.
    }
    $plain_token = $token . "|exp=" . date('YmdHisO', time() + $token_expiration);

    $hash = base64_encode(md5($plain_token, true));

    $key = get_config('block_zoola', 'apikey');
    $method = "aes-128-ecb";
    $options = OPENSSL_RAW_DATA;
    $encrypted = openssl_encrypt($hash . $plain_token, $method, $key, $options);

    return base64_encode($encrypted);
}

function block_zoola_sign_token($zoola_token) {
    $organization = get_config('block_zoola', 'organization');
    $signed_token = "o={$organization}|sig=" . block_zoola_token_signature($zoola_token);
    return $signed_token;
}

function block_zoola_list_to_string(array $list, $length = 2000) {
    // Truncate the list to at most $length characters.
    $s = implode(',', $list);
    if (strlen($s) > $length) {
        $lastcommapos = strrpos($s, ',', $length - strlen($s));
        if ($lastcommapos) {
            $s = substr($s, 0, $lastcommapos);
        } else {
            // No comma there, or it is the first character? Just truncate the string.
            $s = substr($s, 0, $length);
        }
    }
    return $s;
}

function block_zoola_totara_attributes($user) {
    global $CFG;

    require($CFG->dirroot . '/version.php');
    if (!isset($TOTARA)) {
        return '';
    }

    require_once($CFG->dirroot . '/totara/hierarchy/lib.php');
    $orgframework = hierarchy::load_hierarchy('organisation');
    hierarchy::load_hierarchy('position');

    $organisationid = null;
    $positionid = null;
    $managerid = null;
    if (floatval($TOTARA->version) >= 9) {
        $job_assignment = totara_job\job_assignment::get_first($user->id, false);
        if ($job_assignment) {
            $organisationid = $job_assignment->organisationid;
            $positionid = $job_assignment->positionid;
            $managerid = $job_assignment->managerid;
        }
    } else {
        $curr_position = pos_get_current_position_data($user->id, POSITION_TYPE_PRIMARY);
        $organisationid = $curr_position['organisationid'];
        $positionid = $curr_position['positionid'];
        $manager = totara_get_manager($user->id, POSITION_TYPE_PRIMARY, true, false);
        if ($manager) {
            $managerid = $manager->id;
        }
    }

    $token = '';
    if ($organisationid) {
        $sub_orgs = $orgframework->get_item_descendants($organisationid);
        $orgids = array_keys($sub_orgs);
        // This list can be rather long, so we need to truncate it to at most 2000 chars.
        $orgidlist = block_zoola_list_to_string($orgids);
        $token .= "|org=" . $orgidlist; // orgids
    }
    if ($positionid) {
        $token .= "|pos=" . $positionid; // primary_positionid
    }
    if ($managerid) {
        $token .= "|mgr=" . $managerid; // managerid
    }

    return $token;
}

function block_zoola_get_common_token_attributes($user) {
    /* @var $DB moodle_database */
    /* @var $PAGE moodle_page */
    global $DB, $PAGE;

    $zoola_token = "u={$user->username}"; // username
    $zoola_token .= "|uid={$user->id}"; // userid
    $zoola_token .= "|fullname={$user->firstname} {$user->lastname}";
    $zoola_token .= "|email={$user->email}";
    $zoola_token .= "|lu=blocks/zoola/logout.php"; // logout_url
    $zoola_token .= '|la=' . (is_siteadmin() ? 'true' : 'false'); // lms_admin
    if (!empty($user->department)) {
        $zoola_token .= "|dep={$user->department}"; // department
    }
    if (!empty($user->institution)) {
        $zoola_token .= "|ins={$user->institution}"; // institution
    }

    // Select courses where user has roles other than student (teacher, editingteacher, manager, assessor, ...)
    $zoola_courses = $DB->get_records_sql(
            "select distinct c.instanceid
               from {role_assignments} ra
               join {role} r on ra.roleid = r.id
               join {context} c on ra.contextid = c.id
              where r.shortname != 'student'
                and c.contextlevel = 50
                and ra.userid = :userid", array('userid' => $user->id));
    if (!empty($zoola_courses)) {
        $zoola_token .= '|c=' . block_zoola_list_to_string(array_keys($zoola_courses)); // enrolled_courseids
    }

    $user_groups = $DB->get_records('groups_members', array('userid' => $user->id), '', 'groupid');
    if (!empty($user_groups)) {
        $zoola_token .= '|grp=' . block_zoola_list_to_string(array_keys($user_groups)); // groupids
    }

    $user_cohorts = $DB->get_records('cohort_members', array('userid' => $user->id), '', 'cohortid');
    if (!empty($user_cohorts)) {
        $zoola_token .= '|coh=' . block_zoola_list_to_string(array_keys($user_cohorts)); // cohortids
    }

    if (intval(get_user_preferences('block_zoola_user_created_at', 0, $user)) === 0) {
        // Token created for the first time - update user's preference.
        set_user_preference('block_zoola_user_created_at', time(), $user);
    }

    if ($PAGE->course->id > 1) {
        $zoola_token .= '|ccid=' . $PAGE->course->id; // current_courseid
    }

    $zoola_token .= block_zoola_totara_attributes($user);

    return $zoola_token;
}

function block_zoola_capabilities() {
    return array(
        'block/zoola:administrator'   => 'zoola_administrator',
        'block/zoola:user'            => 'zoola_user',
        'block/zoola:dashboards'      => 'zoola_dashboards',
        'block/zoola:reports'         => 'zoola_reports',
        'block/zoola:domain_designer' => 'zoola_domain_designer'
    );
}

function block_zoola_get_zoola_roles($user) {
    /* @var $PAGE moodle_page */
    global $PAGE;

    $capabilities = block_zoola_capabilities();
    $zoola_roles = array();
    foreach ($capabilities as $capability => $zoola_role) {
        if (has_capability($capability, $PAGE->context, $user, false)) {
            $zoola_roles[] = $zoola_role;
        }
    }

    if (empty($zoola_roles)) {
        // User needs at least this role to run embeded report.
        $zoola_roles[] = 'zoola_reports';
    }

    if (has_capability('block/zoola:public_read', $PAGE->context, $user)) {
        $zoola_roles[] = 'zoola_public_read';
    } else {
        $zoola_roles[] = 'zoola_public_exec';
    }

    return $zoola_roles;
}

function block_zoola_get_token($user) {
    if (!get_config('block_zoola', 'apikey')) {
        return false;
    }

    $zoola_token = block_zoola_get_common_token_attributes($user);
    $zoola_token .= '|r=' . implode(',', block_zoola_get_zoola_roles($user)); // roles
    $token = block_zoola_sign_token($zoola_token);

    return $token;
}

function block_zoola_get_report_token() {
    global $USER;
    $token = block_zoola_get_token($USER);

    return $token;
}
