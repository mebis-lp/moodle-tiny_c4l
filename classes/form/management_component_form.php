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

/**
 * Class management_component_form
 *
 * @package    tiny_c4l
 * @copyright  2024 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class management_component_form extends base_form {
    public function definition() {
        $mform =& $this->_form;

        // Set this variable to access correct db table.
        $this->formtype = "component";

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'displayname', get_string('displayname', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('displayname', PARAM_TEXT);

        $mform->addElement('text', 'compcat', get_string('compcat', 'tiny_c4l'));
        $mform->setType('compcat', PARAM_INT);

        $mform->addElement('text', 'imageclass', get_string('imageclass', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('imageclass', PARAM_TEXT);

        $mform->addElement('textarea', 'code', get_string('code', 'tiny_c4l'));
        $mform->setType('code', PARAM_TEXT);

        $mform->addElement('textarea', 'text', get_string('text', 'tiny_c4l'));
        $mform->setType('text', PARAM_TEXT);

        $mform->addElement('text', 'variants', get_string('variants', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('variants', PARAM_TEXT);

        $mform->addElement('text', 'flavors', get_string('flavors', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('flavors', PARAM_TEXT);

        $mform->addElement('text', 'displayorder', get_string('displayorder', 'tiny_c4l'));
        $mform->setType('displayorder', PARAM_INT);

        $mform->addElement('textarea', 'css', get_string('css', 'tiny_c4l'));
        $mform->setType('css', PARAM_TEXT);

        $mform->addElement('textarea', 'js', get_string('js', 'tiny_c4l'));
        $mform->setType('js', PARAM_TEXT);
    }
}
