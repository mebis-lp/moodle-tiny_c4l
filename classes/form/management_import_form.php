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
 * Class management_import_form
 *
 * @package    tiny_c4l
 * @copyright  2024 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class management_import_form extends base_form {
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement(
            'filepicker',
            'backupfile',
            get_string('file'),
            null,
            ['accepted_types' => 'xml,zip']
        );
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
        if (empty($data['backupfile'])) {
            $errors['backupfile'] = get_string('errorbackupfile', 'tiny_c4l');
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
        $fs = get_file_storage();
        $data = $this->get_data();
        $draftitemid = $data->backupfile;
        file_save_draft_area_files($draftitemid, SYSCONTEXTID, 'tiny_c4l', 'import', $draftitemid);
        $files = $fs->get_directory_files(SYSCONTEXTID, 'tiny_c4l', 'import', $draftitemid, '/', false, false);
        do {
            $file = array_pop($files);
        } while ($file !== null && $file->is_directory());
        if ($file === null) {
            throw new \moodle_exception('errorbackupfile', 'tiny_c4l');
        }
        if ($file->get_mimetype() == 'application/zip') {
            $fp = get_file_packer('application/zip');
            $fp->extract_to_storage($file, SYSCONTEXTID, 'tiny_c4l', 'import', $draftitemid, '/');
            $xmlfile = $fs->get_file(SYSCONTEXTID, 'tiny_c4l', 'import', $draftitemid, '/', 'tiny_c4l_export.xml');
            $xmlcontent = $xmlfile->get_content();
        } else {
            $xmlcontent = $file->get_content();
        }

        $manager = new \tiny_c4l\manager();
        $manager->importxml($xmlcontent);
        $categories = $DB->get_records('tiny_c4l_compcat');
        foreach ($categories as $category) {
            $categoryfiles = $fs->get_directory_files(SYSCONTEXTID, 'tiny_c4l', 'import', $draftitemid, '/' . $category->name . '/', true, false);
            $manager->importfiles($categoryfiles, $category->id, $category->name);
        }

        return [
            'update' => true,
        ];
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
    }
}
