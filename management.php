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

/**
 * TODO describe file management
 *
 * @package    tiny_c4l
 * @copyright  2024 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../../config.php');

require_login();

$url = new moodle_url('/lib/editor/tiny/plugins/c4l/management.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('menuitem_c4l', 'tiny_c4l') . ' ' . get_string('management', 'tiny_c4l'));

require_capability('tiny/c4l:manage', context_system::instance());

echo $OUTPUT->header();

// Get all c4l components.
// Use array_values so mustache can parse it.
$compcats = array_values($DB->get_records('tiny_c4l_compcat'));
$flavor = array_values($DB->get_records('tiny_c4l_flavor'));
$component = array_values($DB->get_records('tiny_c4l_component'));
$variant = array_values($DB->get_records('tiny_c4l_variant'));

$addentry = [];
array_push($compcats, $addentry);
array_push($flavor, $addentry);
array_push($component, $addentry);
array_push($variant, $addentry);

// Add exportlink.
$exportlink = \moodle_url::make_pluginfile_url(SYSCONTEXTID, 'tiny_c4l', 'export', null, '/', 'tiny_c4l_export.xml')->out();
$PAGE->requires->js_call_amd('tiny_c4l/management', 'init');
echo($OUTPUT->render_from_template('tiny_c4l/management', [
    'compcats' => $compcats,
    'flavor' => $flavor,
    'component' => $component,
    'variant' => $variant,
    'exportlink' => $exportlink,
]));
echo $OUTPUT->footer();
