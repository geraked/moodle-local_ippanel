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
 * Lib file
 *
 * @package    local_ippanel
 * @copyright  2021 Geraked
 * @author     Rabist
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_ippanel_before_footer()
{
    global $CFG, $USER;

    $acl = array_filter(array_map('trim', preg_split('/\r\n|[\r\n]/', get_config('local_ippanel', 'acl'))));

    if (!is_siteadmin($USER) && !in_array($USER->id, $acl)) {
        return;
    }

    $v = [
        [
            'name' => get_string('sendmessage', 'local_ippanel'),
            'url' => $CFG->wwwroot . '/local/ippanel/send.php',
        ],
        [
            'name' => get_string('sendreports', 'local_ippanel'),
            'url' => $CFG->wwwroot . '/local/ippanel/report.php',
        ],
    ];

    $r = '
        <script>
            (() => {
                var v = ' . json_encode($v) . ';
                ' . file_get_contents($CFG->dirroot . '/local/ippanel/js/lib.js') . '
            })();            
        </script>
    ';

    echo $r;
}
