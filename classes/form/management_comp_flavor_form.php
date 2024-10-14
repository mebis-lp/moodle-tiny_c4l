<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tiny_c4l\form;

use core_form\dynamic_form;
use context;

/**
 * Class management_comp_flavor
 *
 * @package    tiny_c4l
 * @copyright 2024 ISB Bayern
 * @author     Stefan Hanauska
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class management_comp_flavor_form extends dynamic_form {
    /**
     * Form definition
     */
    public function definition() {
        global $DB;
        $count = $DB->count_records('tiny_c4l_comp_flavor');
        $mform =& $this->_form;

        $group = [];
        $group[] = $mform->createElement('hidden', 'id');
        $group[] = $mform->createElement('text', 'componentname', get_string('component', 'tiny_c4l'));
        $group[] = $mform->createElement('text', 'flavorname', get_string('flavor', 'tiny_c4l'));
        $group[] = $mform->createElement('url', 'iconurl', get_string('iconurl', 'tiny_c4l'));

        $options = [
            'id' => [
                'type' => PARAM_INT,
            ],
            'componentname' => [
                'type' => PARAM_TEXT,
                'disabledif' => [
                    'id', 'neq', 0
                ],
            ],
            'flavorname' => [
                'type' => PARAM_TEXT,
                'disabledif' => [
                    'id', 'neq', 0
                ],
            ],
            'iconurl' => [
                'type' => PARAM_URL,
            ],
        ];

        $this->repeat_elements($group, $count, $options, 'itemcount', null, 0);
    }

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     *
     * Checks if current user has sufficient permissions, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('tiny/c4l:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Form processing.
     *
     * @return array
     */
    public function process_dynamic_submission(): array {
        global $DB;

        $formdata = $this->get_data();

        $result = true;

        foreach($formdata->id as $key => $id) {
            $record = new \stdClass();
            $record->id = $id;
            $record->iconurl = $formdata->iconurl[$key];
            $result &= $DB->update_record('tiny_c4l_comp_flavor', $record);
        }

        return [
            'update' => $result,
        ];
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        global $DB;

        $compflavor = $DB->get_records('tiny_c4l_comp_flavor');

        $data = [];
        foreach($compflavor as $item) {
            $data['id'][] = $item->id;
            $data['componentname'][] = $item->componentname;
            $data['flavorname'][] = $item->flavorname;
            $data['iconurl'][] = $item->iconurl;
        }

        $data['itemcount'] = count($compflavor);

        $this->set_data($data);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/lib/editor/tiny/plugins/c4l/management.php');
    }
}
