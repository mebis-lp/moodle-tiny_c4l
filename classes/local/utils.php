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

/**
 * Utility class for tiny_c4l.
 *
 * @package    tiny_c4l
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    public const TINY_C4L_CACHE_AREA = 'tiny_c4l_css';

    public const TINY_C4L_CSS_CACHE_KEY = 'css';

    public const TINY_C4L_CSS_CACHE_REV= 'cssrev';

    public static function get_all_components(): array {
        global $DB;
        $componentrecords = $DB->get_records('tiny_c4l_component');
        $components = [];
        foreach ($componentrecords as $record) {
            // TODO export which flavors are allowed
            $components[] = [
                    'id' => $record->id,
                    'name' => $record->name,
                    'displayname' => $record->displayname,
                    'compcat' => $record->compcat,
                    'imageclass' => $record->imageclass,
                    'code' => $record->code,
                    'text' => $record->text,
                    'variants' => [
                        // TODO Get variants.
                    ]
            ];

        }
        return $components;
    }

    public static function get_all_compcats(): array {
        global $DB;
        $categories = $DB->get_records('tiny_c4l_compcat');
        return array_values($categories);
    }

    public static function get_all_flavors(): array {
        global $DB;
        $flavors = $DB->get_records('tiny_c4l_flavor');
        return array_values($flavors);
    }

    /**
     * Rebuild the css cache.
     *
     * @return int the new revision for the cache
     */
    public static function rebuild_css_cache(): int {
        global $DB;
        $cache = \cache::make('tiny_c4l', self::TINY_C4L_CACHE_AREA);
        $componentcssentries = $DB->get_fieldset('tiny_c4l_component', 'css');
        $categorycssentries = $DB->get_fieldset('tiny_c4l_compcat', 'css');
        $flavorcssentries = $DB->get_fieldset('tiny_c4l_flavor', 'css');
        $cssentries = array_merge($categorycssentries, $componentcssentries, $flavorcssentries);
        $css = array_reduce($cssentries, fn($current, $add) => $current . PHP_EOL . $add,
                '/* This file contains the stylesheet for the tiny_c4l plugin.*/');
        $clock = \core\di::get(\core\clock::class);
        $rev = $clock->time();
        $cache->set(self::TINY_C4L_CSS_CACHE_KEY, $css);
        $cache->set(self::TINY_C4L_CSS_CACHE_REV, $rev);
        return $rev;
    }

    /**
     * Purge the tiny_c4l css cache.
     */
    public static function purge_css_cache(): void {
        $cache = \cache::make('tiny_c4l', self::TINY_C4L_CACHE_AREA);
        $cache->purge();
    }

    /**
     * Helper function to retrieve the currently cached tiny_c4l css.
     *
     * @return string|false the css code as string, false if no cache entry found
     */
    public static function get_css_from_cache(): string|false {
        $cache = \cache::make('tiny_c4l', utils::TINY_C4L_CACHE_AREA);
        return $cache->get(utils::TINY_C4L_CSS_CACHE_KEY);
    }
}
