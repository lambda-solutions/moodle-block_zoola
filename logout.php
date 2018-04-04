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

require_once('../../config.php');

if ($PAGE) {
    $PAGE->set_url('/blocks/zoola/logout.php');
    $PAGE->set_context(null);
    $PAGE->set_pagelayout('redirect');  // No header and footer needed.
    $PAGE->set_title(get_string('pageshouldredirect', 'moodle'));
}
echo $OUTPUT->header();
echo $OUTPUT->notification('Logging out from Zoola', 'redirectmessage');
$backendurl = rtrim(get_config('block_zoola', 'backendurl'), '/');
$iframe = '<iframe id="zoola"
        style="display: none;"
        src="' . $backendurl . '/logout.html">
        </iframe>';
echo $iframe;
if (isset($SESSION->zoola_backurl)) {
    $url = $SESSION->zoola_backurl;
} else {
    $url = $CFG->wwwroot;
}
echo '<div class="continuebutton">(<a href="'. $url .'">'. get_string('continue') .'</a>)</div>';
$PAGE->requires->js_function_call('document.location.replace', array($url), true);
echo $OUTPUT->footer();
