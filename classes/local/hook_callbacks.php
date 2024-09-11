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

namespace tiny_c4l\local;

use core\hook\output\before_http_headers;

/**
 * Class containing the hook callbacks for tiny_c4l.
 *
 * @package    tiny_c4l
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    public static function add_c4l_stylesheet_to_dom(\core\hook\output\before_http_headers $beforehttpheadershook): void {
        $pluginfileurl = \moodle_url::make_pluginfile_url(SYSCONTEXTID, 'tiny_c4l', '', null, '', 'tiny_c4l_styles.css');
        $beforehttpheadershook->renderer->get_page()->requires->css($pluginfileurl);
    }
}
