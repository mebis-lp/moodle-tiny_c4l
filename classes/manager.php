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

namespace tiny_c4l;

use memory_xml_output;
use moodle_exception;
use stored_file;
use xml_writer;

defined('MOODLE_INTERNAL') || die();

global $CFG;
// require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/backup/util/xml/xml_writer.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/xml_output.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/memory_xml_output.class.php');

/**
 * Class manager
 *
 * @package    tiny_c4l
 * @copyright  2024 Tobias Garske
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /** @var array All tables to export data from. **/
    protected static $tables = [
        'compcat' => 'tiny_c4l_compcat',
        'component' => 'tiny_c4l_component',
        'flavor' => 'tiny_c4l_flavor',
        'variant' => 'tiny_c4l_variant',
    ];

    /** @var string Item. **/
    protected static $item = 'row';

    public function export($compcatid = 0): stored_file {
        global $DB;
        $fs = get_file_storage();
        $fp = get_file_packer('application/zip');
        $compcats = $DB->get_records('tiny_c4l_compcat');
        // It is necessary to get the files for each compcat separately to avoid mixing up files from
        // different categories.
        foreach ($compcats as $compcat) {
            $files = $fs->get_area_files(SYSCONTEXTID, 'tiny_c4l', 'images', $compcat->id);
            foreach ($files as $file) {
                $exportfiles[$compcat->name . '/' . $file->get_filepath() . $file->get_filename()] = $file;
            }
        }
        $filerecord = [
            'contextid' => SYSCONTEXTID,
            'component' => 'tiny_c4l',
            'filearea' => 'export',
            'itemid' => time(),
            'filepath' => '/',
            'filename' => 'tiny_c4l_export.xml',
        ];
        $exportxmlfile = $fs->create_file_from_string($filerecord, $this->exportxml());
        $exportfiles['tiny_c4l_export.xml'] = $exportxmlfile;
        $filename = 'tiny_c4l_export_' . time() . '.zip';
        $exportfile = $fp->archive_to_storage($exportfiles, SYSCONTEXTID, 'tiny_c4l', 'export', 0, '/', $filename);
        if (!$exportfile) {
            throw new moodle_exception(get_string('error_export', 'tiny_c4l'));
        }
        return $exportfile;
    }

    public function exportxml(): string {
        global $DB;

        // Start.
        $xmloutput = new memory_xml_output();
        $xmlwriter = new xml_writer($xmloutput);
        $xmlwriter->start();
        $xmlwriter->begin_tag('c4l');

        // Tiny_c4l_compcat.
        foreach (static::$tables as $shortname => $table) {
            // Get columns.
            $columns = $DB->get_columns($table);

            // Get data.
            $data = $DB->get_records($table);

            $xmlwriter->begin_tag($table);
            foreach ($data as $value) {
                $xmlwriter->begin_tag(static::$item);
                foreach ($columns as $column) {
                    $name = $column->name;
                    $xmlwriter->full_tag($name, $value->$name);
                }
                $xmlwriter->end_tag(static::$item);
            }
            $xmlwriter->end_tag($table);
        }

        // End.
        $xmlwriter->end_tag('c4l');
        $xmlwriter->stop();
        $xmlstr = $xmloutput->get_allcontents();

        return $xmlstr;
    }

    public function import(string $xmlcontent): bool {
        global $DB;

        try {
            $xml = simplexml_load_string($xmlcontent);
        } catch (\Exception $exception) {
            $xml = false;
        }
        if (!$xml) {
            return false;
        }

        // Create mapping array for tiny_c4l_component table.
        $componentmap = [];

        foreach ($xml as $table => $rows) {
            foreach ($rows as $row) {
                $obj = new \stdClass();
                // Check if item already exists.
                $update = $DB->get_record_select($table, 'name = ?', [(string) $row->name]);
                foreach ($row as $column => $value) {
                    // Skip id for inserts, but remember old id for tiny_c4l_component table.
                    if ($column === 'id') {
                        if ($update) {
                            $oldid = $update->id;
                            $obj->id = $update->id;
                        } else {
                            $oldid = (string) $value;
                        }
                        continue;
                    }
                    // Set value for update / insert.
                    $obj->$column = (string) $value;
                }
                // Use mapping to update with new id from tiny_c4l_compcat.
                if ($table === 'tiny_c4l_component') {
                    $obj->compcat = $componentmap[$obj->compcat];
                }
                // Insert record.
                if ($update) {
                    $DB->update_record($table, $obj);
                } else {
                    $newid = $DB->insert_record($table, $obj);
                }
                // Create mapping with returned id for tiny_c4l_component table.
                if ($table === 'tiny_c4l_compcat') {
                    $componentmap[$oldid] = $newid;
                }
            }
        }

        return true;
    }
}
