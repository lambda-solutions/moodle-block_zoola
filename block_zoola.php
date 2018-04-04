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

class block_zoola extends block_base {


    public function init() {
        $this->title = get_string('pluginname', 'block_zoola');
    }

    public function get_content() {
        global $CFG;

        $zoola_capabilities = array(
            'block/zoola:administrator',
            'block/zoola:user',
            'block/zoola:dashboards',
            'block/zoola:reports'
        );

        if (!has_any_capability($zoola_capabilities, $this->context)) {
            $this->content = new stdClass();
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $button_url = $CFG->wwwroot.'/blocks/zoola/view.php';
        $text_description = get_string('text_desc', 'block_zoola');

        $output = '';
        $output .= html_writer::start_tag('form', array('id' => 'form', 'action' => $button_url, 'method' => 'post'));
        $attributes = array(
            'type' => 'submit',
            'value' => get_string('zoolabutton', 'block_zoola')
        );
        $output .= html_writer::tag('fieldset', html_writer::empty_tag('input', $attributes));
        $output .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'courseid',
            'value' => $this->page->course->id
        ));
        $output .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => 'backurl',
            'value' => $this->page->url
        ));
        $output .= html_writer::tag('div', $text_description, array('id' => 'zoola_text_desc_id', 'class' => 'zoola_text_desc'));
        $output .= html_writer::end_tag('form');

        $this->content->text = '';
        $this->content->text = $output;

        return $this->content;
    }

    /**
     * Are you going to allow multiple instances of each block?
     * If yes, then it is assumed that the block WILL USE per-instance configuration
     * @return boolean
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Subclasses should override this and return true if the
     * subclass block has a settings.php file.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

}
