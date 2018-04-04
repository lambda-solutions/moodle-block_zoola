<?php
/*
 * This file is part of the block_zoola package.
 *
 * Copyright (c) 2018 Lambda Solutions
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");

class block_zoola_external extends external_api {

    // Helper methods.

    protected static function get_course_details_structure($required) {
        return new external_single_structure(array(
            'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
            'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
            'idnumber' => new external_value(PARAM_TEXT, 'Course ID number'),
            'enablecompletion' => new external_value(PARAM_BOOL, 'Course completion is enabled'),
        ), 'Course details', $required);
    }

    protected static function get_course_details_fields($prefix) {
        return ", $prefix.fullname as course_fullname"
                . ", $prefix.shortname as course_shortname"
                . ", $prefix.idnumber as course_idnumber"
                . ", $prefix.enablecompletion as course_enablecompletion";
    }

    protected static function get_course_details_array($row) {
        return array(
            'fullname' => $row->course_fullname,
            'shortname' => $row->course_shortname,
            'idnumber' => $row->course_idnumber,
            'enablecompletion' => $row->course_enablecompletion
        );
    }

    protected static function get_user_details_structure($required) {
        return new external_single_structure(array(
            'username' => new external_value(PARAM_TEXT, 'User username'),
            'idnumber' => new external_value(PARAM_TEXT, 'User ID number'),
            'firstname' => new external_value(PARAM_TEXT, 'User first name'),
            'lastname' => new external_value(PARAM_TEXT, 'User last name'),
            'email' => new external_value(PARAM_TEXT, 'User email'),
            'institution' => new external_value(PARAM_TEXT, 'User institution'),
            'department' => new external_value(PARAM_TEXT, 'User department'),
        ), 'User details', $required);
    }

    protected static function get_user_details_fields($prefix) {
        return ", $prefix.username as user_username"
                . ", $prefix.idnumber as user_idnumber"
                . ", $prefix.firstname as user_firstname"
                . ", $prefix.lastname as user_lastname"
                . ", $prefix.email as user_email"
                . ", $prefix.institution as user_institution"
                . ", $prefix.department as user_department";
    }

    protected static function get_user_details_array($row) {
        return array(
            'username' => $row->user_username,
            'idnumber' => $row->user_idnumber,
            'firstname' => $row->user_firstname,
            'lastname' => $row->user_lastname,
            'email' => $row->user_email,
            'institution' => $row->user_institution,
            'department' => $row->user_department
        );
    }

    protected static function get_courseids_parameter() {
        return new external_multiple_structure(new external_value(PARAM_INT, 'Course id'),
                'Array of course IDs, leave empty to get all courses', VALUE_DEFAULT, array());
    }

    protected static function get_userids_parameter() {
        return new external_multiple_structure(new external_value(PARAM_INT, 'User id'),
                'Array of user IDs, leave empty to get all users', VALUE_DEFAULT, array());
    }

    protected static function get_coursedetails_parameter() {
        return new external_value(PARAM_BOOL, '1 - get course details, 0 - get just course id', VALUE_DEFAULT, false);
    }

    protected static function get_userdetails_parameter() {
        return new external_value(PARAM_BOOL, '1 - get user details, 0 - get just user id', VALUE_DEFAULT, false);
    }

    /**
     * Adds filter to SQL query.
     *
     * @param string $fieldname Field to which to apply filter
     * @param array $values Filter values
     * @param string $where Existing where clause which will be expanded with appropriate condition
     * @param array $sqlparams Existing SQL parameters
     */
    protected static function add_filter($fieldname, $values, &$where, &$sqlparams) {
        global $DB;
        if (empty($values)) {
            // Empty filter means do not filter at all.
            return;
        }
        list($sqlfragment, $parameters) = $DB->get_in_or_equal($values);
        $where .= " and $fieldname $sqlfragment";
        $sqlparams = array_merge($sqlparams, $parameters);
    }

    /**
     * Adds course details to SQL query
     *
     * @param boolean $shouldadd Should course details be added
     * @param string $alias Course table alias
     * @param string $joinfield Field on which to join course table
     * @param string $select Existing select clause to be expanded
     * @param string $from Existing from clause to add course table
     */
    protected static function add_course_details($shouldadd, $alias, $joinfield, &$select, &$from) {
        if ($shouldadd) {
            $select .= self::get_course_details_fields($alias);
            $from .= " join {course} $alias on $joinfield = $alias.id";
        }
    }

    /**
     * Adds user details to SQL query.
     * User table should already exist in from clause
     *
     * @param boolean $shouldadd Should user details be added
     * @param string $alias User table alias
     * @param type $select Existing select clause to be expanded
     */
    protected static function add_user_details($shouldadd, $alias, &$select) {
        if ($shouldadd) {
            $select .= self::get_user_details_fields($alias);
        }
    }

    // External function definitions.

    public static function courses_parameters() {
        return new external_function_parameters(array(
            'courseids' => self::get_courseids_parameter()
        ));
    }

    /**
     *
     * @global moodle_database $DB
     * @param array $courseids
     * @return array
     */
    public static function courses($courseids) {
        global $DB;

        $params = self::validate_parameters(self::courses_parameters(), array(
            'courseids' => $courseids,
        ));

        $sql = 'select '
                . 'c.id, c.sortorder, c.fullname, c.shortname, c.idnumber, c.startdate, c.enablecompletion, '
                . 'cat.id as catid, cat.name as catname, cat.idnumber as catidnumber, cat.sortorder as catsortorder '
                . 'from {course} c '
                . 'left outer join {course_categories} cat on c.category = cat.id';
        $sqlparams = null;
        if (!empty($params['courseids'])) {
            list($where, $sqlparams) = $DB->get_in_or_equal($params['courseids']);
            $sql .= " where c.id $where";
        }

        $courses = $DB->get_records_sql($sql, $sqlparams);
        $return = array();
        foreach ($courses as $course) {
            $object = array(
                'id' => $course->id,
                'sortorder' => $course->sortorder,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'idnumber' => $course->idnumber,
                'startdate' => $course->startdate,
                'enablecompletion' => $course->enablecompletion == 1,
            );
            if ($course->catid > 0) {
                $object['category'] = array(
                    'id' => $course->catid,
                    'name' => $course->catname,
                    'idnumber' => $course->catidnumber,
                    'sortorder' => $course->catsortorder,
                );
            }
            $return[] = $object;
        }

        return $return;
    }

    /**
     *
     * @return \external_multiple_structure
     */
    public static function courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'Course id'),
                    'category' => new external_single_structure(array(
                        'id' => new external_value(PARAM_INT, 'Course category id'),
                        'name' => new external_value(PARAM_TEXT, 'Course category name'),
                        'idnumber' => new external_value(PARAM_TEXT, 'Course category id number'),
                        'sortorder' => new external_value(PARAM_INT, 'Course category sort order'),
                    ), 'Course category', VALUE_OPTIONAL),
                    'sortorder' => new external_value(PARAM_INT, 'Course sort order'),
                    'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
                    'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                    'idnumber' => new external_value(PARAM_TEXT, 'Course id number'),
                    'startdate' => new external_value(PARAM_INT, 'Course start date'),
                    'enablecompletion' => new external_value(PARAM_BOOL, 'Course completion enabled'),
                ))
        );
    }

    /**
     *
     * @return \external_function_parameters
     */
    public static function users_parameters() {
        return new external_function_parameters(array(
            'userids' => self::get_userids_parameter()
        ));
    }

    /**
     *
     * @global moodle_database $DB
     * @param array $userids
     * @return array
     */
    public static function users($userids) {
        global $DB;

        $params = self::validate_parameters(self::users_parameters(), array(
            'userids' => $userids,
        ));

        $select = 'deleted = 0';
        $sqlparams = null;
        if (!empty($params['userids'])) {
            list($where, $sqlparams) = $DB->get_in_or_equal($params['userids']);
            $select .= " and id $where";
        }
        $fields = 'id, username, idnumber, firstname, lastname, email, institution, department, lastaccess';
        $users = $DB->get_records_select('user', $select, $sqlparams, '', $fields);
        $return = array();
        foreach ($users as $user) {
            $object = (array)$user;
            $return[] = $object;
        }

        return $return;
    }

    /**
     *
     * @return \external_multiple_structure
     */
    public static function users_returns() {
        return new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'User id'),
                    'username' => new external_value(PARAM_TEXT, 'Username'),
                    'idnumber' => new external_value(PARAM_TEXT, 'ID number'),
                    'firstname' => new external_value(PARAM_TEXT, 'First name'),
                    'lastname' => new external_value(PARAM_TEXT, 'Last name'),
                    'email' => new external_value(PARAM_TEXT, 'Email'),
                    'institution' => new external_value(PARAM_TEXT, 'Institution'),
                    'department' => new external_value(PARAM_TEXT, 'Department'),
                    'lastaccess' => new external_value(PARAM_INT, 'lastaccess'),
                ))
        );
    }

    public static function course_roles_parameters() {
        return new external_function_parameters(array(
            'courseids'     => self::get_courseids_parameter(),
            'userids'       => self::get_userids_parameter(),
            'coursedetails' => self::get_coursedetails_parameter(),
            'userdetails'   => self::get_userdetails_parameter()
        ));
    }

    public static function course_roles($courseids, $userids, $coursedetails, $userdetails) {
        global $DB;

        $params = self::validate_parameters(self::course_roles_parameters(), array(
            'courseids' => $courseids,
            'userids' => $userids,
            'coursedetails' => $coursedetails,
            'userdetails' => $userdetails,
        ));

        $select = 'select distinct ctx.instanceid as courseid, ra.userid, r.shortname as role';
        $from = 'from {role} r'
                . ' join {role_assignments} ra on r.id = ra.roleid'
                . ' join {context} ctx on ra.contextid = ctx.id and ctx.contextlevel = 50'
                . ' join {user} u on ra.userid = u.id and u.deleted = 0';
        $where = 'where 1 = 1';
        $sqlparams = array();

        self::add_filter('ctx.instanceid', $params['courseids'], $where, $sqlparams);
        self::add_filter('u.id', $params['userids'], $where, $sqlparams);
        self::add_course_details($params['coursedetails'], 'c', 'ctx.instanceid', $select, $from);
        self::add_user_details($params['userdetails'], 'u', $select);

        $return = array();
        $rs = $DB->get_recordset_sql("$select $from $where", $sqlparams);
        foreach ($rs as $row) {
            $record = array(
                'courseid' => $row->courseid,
                'userid' => $row->userid,
                'role' => $row->role
            );
            if ($params['coursedetails']) {
                $record['course'] = self::get_course_details_array($row);
            }
            if ($params['userdetails']) {
                $record['user'] = self::get_user_details_array($row);
            }
            $return[] = $record;
        }
        $rs->close();

        return $return;
    }

    public static function course_roles_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'userid'   => new external_value(PARAM_INT, 'User id'),
                'courseid' => new external_value(PARAM_INT, 'Course id'),
                'role'     => new external_value(PARAM_TEXT, 'Role name'),
                'course'   => self::get_course_details_structure(VALUE_OPTIONAL),
                'user'     => self::get_user_details_structure(VALUE_OPTIONAL),
            ))
        );
    }

    public static function course_grades_parameters() {
        return new external_function_parameters(array(
            'courseids'     => self::get_courseids_parameter(),
            'userids'       => self::get_userids_parameter(),
            'coursedetails' => self::get_coursedetails_parameter(),
            'userdetails'   => self::get_userdetails_parameter()
        ));
    }

    public static function course_grades($courseids, $userids, $coursedetails, $userdetails) {
        global $DB;

        $params = self::validate_parameters(self::course_roles_parameters(), array(
            'courseids' => $courseids,
            'userids' => $userids,
            'coursedetails' => $coursedetails,
            'userdetails' => $userdetails,
        ));

        $select = 'select gi.courseid, gi.grademin, gi.grademax, gg.userid, gg.finalgrade, gg.timemodified';
        $from = 'from {grade_grades} gg'
                . ' join {grade_items} gi on gg.itemid = gi.id'
                . ' join {user} u on gg.userid = u.id and u.deleted = 0';
        $where = "where gi.itemtype = 'course'";
        $sqlparams = array();

        self::add_filter('gi.courseid', $params['courseids'], $where, $sqlparams);
        self::add_filter('u.id', $params['userids'], $where, $sqlparams);
        self::add_course_details($params['coursedetails'], 'c', 'gi.courseid', $select, $from);
        self::add_user_details($params['userdetails'], 'u', $select);

        $return = array();
        $rs = $DB->get_recordset_sql("$select $from $where", $sqlparams);
        foreach ($rs as $coursegrade) {
            $record = array(
                'courseid' => $coursegrade->courseid,
                'userid' => $coursegrade->userid,
                'grademin' => $coursegrade->grademin,
                'grademax' => $coursegrade->grademax,
                'finalgrade' => $coursegrade->finalgrade,
                'timemodified' => $coursegrade->timemodified,
            );
            if ($params['coursedetails']) {
                $record['course'] = self::get_course_details_array($coursegrade);
            }
            if ($params['userdetails']) {
                $record['user'] = self::get_user_details_array($coursegrade);
            }
            $return[] = $record;
        }
        $rs->close();

        return $return;
    }

    public static function course_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'courseid'     => new external_value(PARAM_INT, 'Course id'),
                'grademin'     => new external_value(PARAM_FLOAT, 'Minimum grade'),
                'grademax'     => new external_value(PARAM_FLOAT, 'Maximum grade'),
                'userid'       => new external_value(PARAM_INT, 'User id'),
                'finalgrade'   => new external_value(PARAM_FLOAT, 'User grade'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'course'       => self::get_course_details_structure(VALUE_OPTIONAL),
                'user'         => self::get_user_details_structure(VALUE_OPTIONAL),
            ))
        );
    }


    public static function activity_grades_parameters() {
        return new external_function_parameters(array(
            'courseids'     => self::get_courseids_parameter(),
            'userids'       => self::get_userids_parameter(),
            'coursedetails' => self::get_coursedetails_parameter(),
            'userdetails'   => self::get_userdetails_parameter()
        ));
    }

    public static function activity_grades($courseids, $userids, $coursedetails, $userdetails) {
        global $DB;

        $params = self::validate_parameters(self::course_roles_parameters(), array(
            'courseids' => $courseids,
            'userids' => $userids,
            'coursedetails' => $coursedetails,
            'userdetails' => $userdetails,
        ));

        $select = 'select gi.courseid, gi.grademin, gi.grademax, gg.userid, gg.finalgrade, gg.timemodified, gi.itemmodule as activitytype, gi.itemname as activityname';
        $from = 'from {grade_grades} gg'
                . ' join {grade_items} gi on gg.itemid = gi.id'
                . ' join {user} u on gg.userid = u.id and u.deleted = 0';
        $where = "where gi.itemtype = 'mod'";
        $sqlparams = array();

        self::add_filter('gi.courseid', $params['courseids'], $where, $sqlparams);
        self::add_filter('u.id', $params['userids'], $where, $sqlparams);
        self::add_course_details($params['coursedetails'], 'c', 'gi.courseid', $select, $from);
        self::add_user_details($params['userdetails'], 'u', $select);

        $return = array();
        $rs = $DB->get_recordset_sql("$select $from $where", $sqlparams);
        foreach ($rs as $activitygrade) {
            $record = array(
                'courseid' => $activitygrade->courseid,
                'userid' => $activitygrade->userid,
                'grademin' => $activitygrade->grademin,
                'grademax' => $activitygrade->grademax,
                'finalgrade' => $activitygrade->finalgrade,
                'timemodified' => $activitygrade->timemodified,
                'activitytype' => $activitygrade->activitytype,
                'activityname' => $activitygrade->activityname,
            );
            if ($params['coursedetails']) {
                $record['course'] = self::get_course_details_array($activitygrade);
            }
            if ($params['userdetails']) {
                $record['user'] = self::get_user_details_array($activitygrade);
            }
            $return[] = $record;
        }
        $rs->close();

        return $return;
    }

    public static function activity_grades_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'courseid'     => new external_value(PARAM_INT, 'Course id'),
                'grademin'     => new external_value(PARAM_FLOAT, 'Minimum grade'),
                'grademax'     => new external_value(PARAM_FLOAT, 'Maximum grade'),
                'userid'       => new external_value(PARAM_INT, 'User id'),
                'finalgrade'   => new external_value(PARAM_FLOAT, 'User grade'),
                'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                'activitytype' => new external_value(PARAM_TEXT, 'Activity type'),
                'activityname' => new external_value(PARAM_TEXT, 'Activity name'),
                'course'       => self::get_course_details_structure(VALUE_OPTIONAL),
                'user'         => self::get_user_details_structure(VALUE_OPTIONAL),
            ))
        );
    }
}
