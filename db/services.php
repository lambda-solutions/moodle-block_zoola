<?php
/*
 * This file is part of the block_zoola package.
 *
 * Copyright (c) 2017 Lambda Solutions
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license MIT
 *
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'block_zoola_courses' => array(
        'classname'   => 'block_zoola_external',
        'methodname'  => 'courses', // implement this function into the above class
        'classpath'   => 'blocks/zoola/externallib.php',
        'description' => 'Get all courses with categories',
        'type'        => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
    'block_zoola_users' => array(
        'classname'   => 'block_zoola_external',
        'methodname'  => 'users', // implement this function into the above class
        'classpath'   => 'blocks/zoola/externallib.php',
        'description' => 'Get all users',
        'type'        => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
    'block_zoola_course_roles' => array(
        'classname'   => 'block_zoola_external',
        'methodname'  => 'course_roles', // implement this function into the above class
        'classpath'   => 'blocks/zoola/externallib.php',
        'description' => 'Get courses, users and their roles within courses',
        'type'        => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
    'block_zoola_course_grades' => array(
        'classname'   => 'block_zoola_external',
        'methodname'  => 'course_grades', // implement this function into the above class
        'classpath'   => 'blocks/zoola/externallib.php',
        'description' => 'Get User course grades',
        'type'        => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
    'block_zoola_activity_grades' => array(
        'classname'   => 'block_zoola_external',
        'methodname'  => 'activity_grades', // implement this function into the above class
        'classpath'   => 'blocks/zoola/externallib.php',
        'description' => 'Get User activity grades',
        'type'        => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
);

$services = array(
    'Zoola REST Access' => array(         // the name of the web service
        'functions' => array (            // web service functions of this service
            'block_zoola_courses',
            'block_zoola_users',
            'block_zoola_course_roles',
            'block_zoola_course_grades',
            'block_zoola_activity_grades',
        ),
        'restrictedusers' => 1,           // if enabled, the Moodle administrator must link some user to this service
                                          // into the administration
        'enabled' => 1                    // if enabled, the service can be reachable on a default installation
    )
);
