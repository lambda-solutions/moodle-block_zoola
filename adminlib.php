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

function block_zoola_settings_update_callback($setting) {
    block_zoola_settings_notifier::setting_saved();
}

class block_zoola_settings_notifier {

    /**
     *
     * @var block_zoola_settings_notifier
     */
    protected static $singleton = null;

    public static function setting_saved() {
        if (!self::$singleton) {
            self::$singleton = new block_zoola_settings_notifier();
        }
    }

    public function __destruct() {
        $zoola_config = (array) get_config('block_zoola');
        block_zoola\segment_wrapper::track('Zoola block configured', $zoola_config);
    }
}
