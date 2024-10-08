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

use core\reportbuilder\local\entities\context;
use core_form\dynamic_form;

/**
 * Class base_form
 *
 * @package    tiny_c4l
 * @copyright  2024 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_form extends dynamic_form {
    protected string $formtype;

    abstract public function definition();

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): \context {
        return \context_system::instance();
    }

    /**
     *
     * Checks if current user has sufficient permissions, otherwise throws exception
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('tiny/c4l:manage', \context_system::instance());
    }

    /**
     * Form validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = get_string('errorname', 'tiny_c4l');
        }
        if (empty($data['displayname'])) {
            $errors['displayname'] = get_string('errordisplayname', 'tiny_c4l');
        }
        if (array_key_exists('compcat', $data) && empty($data['compcat'])) {
            $errors['compcat'] = get_string('errorcompcat', 'tiny_c4l');
        }
        return $errors;
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array Returns whether a new source was created.
     */
    public function process_dynamic_submission(): array {
        global $DB;

        $context = $this->get_context_for_dynamic_submission();
        $formdata = $this->get_data();

        if (is_array($formdata->flavors)) {
            $formdata->flavors = implode(',', $formdata->flavors);
        }

        if (is_array($formdata->variants)) {
            $formdata->variants = implode(',', $formdata->variants);
        }

        $formdata->timemodified = time();
        $newrecord = empty($formdata->id);

        $this->postprocess_editors($formdata);

        $table = 'tiny_c4l_' . $this->formtype;
        // Update existing records.
        if ($newrecord) {
            // Insert new record.
            $formdata->timecreated = time();
            $result = $DB->insert_record($table, $formdata);
            $recordid = $result;
        } else {
            $oldrecord = $DB->get_record($table, ['id' => $formdata->id]);
            $result = $DB->update_record($table, $formdata);
            $recordid = $formdata->id;
        }

        // Save files for Compcat form.
        if ($this->formtype === 'compcat') {
            file_save_draft_area_files(
                $formdata->compcatfiles,
                SYSCONTEXTID,
                'tiny_c4l',
                'images',
                $recordid,
                ['subdirs' => 1, 'accepted_types' => ['image']]
            );
        }

        // Purge CSS to show new one.
        if (($newrecord && !empty($formdata->css)) || ($oldrecord->css != $formdata->css)) {
            \tiny_c4l\local\utils::purge_css_cache();
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

        $table = 'tiny_c4l_' . $this->formtype;
        $context = $this->get_context_for_dynamic_submission();

        $id = $this->optional_param('id', null, PARAM_INT);
        $source = $DB->get_record($table, ['id' => $id]);
        if (!$source) {
            $source = new \stdClass();
        }
        // Handle compcat images.
        if ($this->formtype == 'compcat') {
            $draftitemid = file_get_submitted_draft_itemid('compcatfiles');
            file_prepare_draft_area(
                $draftitemid,
                SYSCONTEXTID,
                'tiny_c4l',
                'images',
                $id,
                ['subdirs' => 1, 'accepted_types' => ['web_image']],
            );
            $source->compcatfiles = $draftitemid;
        }

        $this->preprocess_editors($source);

        $this->set_data($source);
    }

    private function preprocess_editors(&$formdata) {
        $formdata->css = [
            'text' => $formdata->css,
            'format' => 90,
        ];
    }

    private function postprocess_editors(&$formdata) {
        $formdata->css = $formdata->css['text'] ?? '';
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
