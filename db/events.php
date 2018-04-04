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

$observers = array(
    array(
        'eventname' => '\core\event\course_module_created',
        'callback'  => '\block_zoola\observer::course_module_created',
        'internal'  => false // This means that we get events only after transaction commit.
    ),
    array(
        'eventname' => '\core\event\course_module_updated',
        'callback'  => '\block_zoola\observer::course_module_updated',
        'internal'  => false // This means that we get events only after transaction commit.
    ),
    array(
        'eventname' => '\core\event\course_module_deleted',
        'callback'  => '\block_zoola\observer::course_module_deleted',
        'internal'  => false // This means that we get events only after transaction commit.
    )
);
