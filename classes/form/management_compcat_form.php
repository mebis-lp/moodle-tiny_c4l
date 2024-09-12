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
 * Class management_compcat_form
 *
 * @package    tiny_c4l
 * @copyright  2024 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class management_compcat_form extends base_form {
    private string $formtype;

    final public function __construct(
        ?string $action = null,
        ?array $customdata = null,
        string $method = 'post',
        string $target = '',
        ?array $attributes = [],
        bool $editable = true,
        ?array $ajaxformdata = null,
        bool $isajaxsubmission = false
    ) {
        $this->formtype = "compcat";
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'displayname', get_string('displayname', 'tiny_c4l'), ['size' => '255']);
        $mform->setType('displayname', PARAM_TEXT);

        $mform->addElement('text', 'displayorder', get_string('displayorder', 'tiny_c4l'));
        $mform->setType('displayorder', PARAM_INT);

        $mform->addElement('textarea', 'css', get_string('css', 'tiny_c4l'));
        $mform->setType('css', PARAM_TEXT);

        $context = $this->get_context_for_dynamic_submission();
    }
}
