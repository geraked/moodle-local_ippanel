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
 * Report file
 *
 * @package    local_ippanel
 * @copyright  2021 Geraked
 * @author     Rabist
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("../../cohort/lib.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once('report_table.php');

if (is_siteadmin($USER)) {
    admin_externalpage_setup('local_ippanel_report');
}

$download = optional_param('download', '', PARAM_ALPHA);

$acl = array_filter(array_map('trim', preg_split('/\r\n|[\r\n]/', get_config('local_ippanel', 'acl'))));

if (!is_siteadmin($USER) && !in_array($USER->id, $acl)) {
    print_error('nopermissions', 'error', '', 'view ippanel');
}

$context = context_system::instance();
$url = new moodle_url('/local/ippanel/report.php');

$PAGE->set_context($context);
$PAGE->set_url($url);
$now = userdate(time(), '%Y%m%d%H%M%S');

$table = new report_table('ippanel');
$table->is_downloading($download, "ippanel-$now");
$table->sort_default_column = 'time';
$table->sort_default_order = SORT_DESC;

if (!$table->is_downloading()) {
    $PAGE->set_title(get_string('sendreports', 'local_ippanel'));
    $PAGE->set_heading(get_string('sendreports', 'local_ippanel'));
    echo $OUTPUT->header();
}

$table->set_sql('*', "{local_ippanel}", '1=1');
$table->define_baseurl($url);
$table->out(10, true);

$v = [
    'apiurl' => $CFG->wwwroot . '/local/ippanel/api.php',
    'usrurl' => $CFG->wwwroot . '/user/profile.php?id=',
];
?>

<?php if (!$table->is_downloading()) : ?>

    <style>
        table .c2 {
            max-width: 130px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        td.c0,
        td.c1 {
            direction: ltr;
        }
    </style>

    <div class="modal fade" id="report-modal" tabindex="-1" aria-labelledby="report-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="report-modal-label">New message</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-msg" style="white-space: pre-line"></p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="cursor: default;">
                            <thead class="text-center">
                                <tr>
                                    <th scope="col"><?php echo get_string('phone2'); ?></th>
                                    <th scope="col"><?php echo get_string('status'); ?></th>
                                    <th scope="col"><?php echo get_string('user'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="modal-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button id="next-btn" type="button" class="btn btn-secondary"><?php echo get_string('nextpage', 'local_ippanel'); ?></button>
                    <button id="prev-btn" type="button" class="btn btn-secondary"><?php echo get_string('prevpage', 'local_ippanel'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            var v = <?php echo json_encode($v); ?>;
            <?php echo file_get_contents($CFG->dirroot . '/local/ippanel/js/report.js'); ?>
        })();
    </script>

    <?php echo $OUTPUT->footer(); ?>

<?php endif; ?>