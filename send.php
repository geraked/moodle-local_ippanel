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
 * Send file
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

admin_externalpage_setup('local_ippanel_send');

$acl = array_filter(array_map('trim', preg_split('/\r\n|[\r\n]/', get_config('local_ippanel', 'acl'))));

if (!is_siteadmin($USER) && !in_array($USER->id, $acl)) {
    print_error('nopermissions', 'error', '', 'view ippanel');
}

$cohorts = cohort_get_all_cohorts(0, 1000)['cohorts'];

$context = context_system::instance();
$url = new moodle_url('/local/ippanel/send.php');
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(get_string('sendmessage', 'local_ippanel'));
$PAGE->set_heading(get_string('sendmessage', 'local_ippanel'));
echo $OUTPUT->header();

$v = [
    'apiurl' => $CFG->wwwroot . '/local/ippanel/api.php',
];

$s = [
    'sending' => get_string('sending', 'local_ippanel'),
    'submit' => get_string('submit'),
    'success' => get_string('success'),
    'id' => get_string('id', 'local_ippanel'),
    'status' => get_string('status'),
    'cost' => get_string('cost', 'local_ippanel'),
    'payback' => get_string('payback', 'local_ippanel'),
    'rcnt' => get_string('rcnt', 'local_ippanel'),
    'pcnt' => get_string('pcnt', 'local_ippanel'),
    'error' => get_string('error'),
];
?>

<h3 class="text-center" style="cursor: default;">
    <span class="badge badge-pill badge-primary"><?php echo get_string('credit_avl', 'local_ippanel'); ?>: <span id="credit-avl" style="display: inline-block; direction: ltr;">0</span> <?php echo get_string('rial', 'local_ippanel'); ?></span>
</h3>

<div class="form-group">
    <label for="msg-txt"><?php echo get_string('message_context', 'local_ippanel'); ?></label>
    <textarea class="form-control" id="msg-txt" rows="4"></textarea>
</div>

<div class="form-group">
    <ul class="list-group list-group-horizontal" style="cursor: default;">
        <li class="list-group-item"><?php echo get_string('char_cnt', 'local_ippanel'); ?>: <span id="char-cnt">0</span></li>
        <li class="list-group-item"><?php echo get_string('num_cnt', 'local_ippanel'); ?>: <span id="num-cnt">0</span></li>
    </ul>
</div>

<div class="form-group">
    <label for="phone-select"><?php echo get_string('phone_type', 'local_ippanel'); ?></label>
    <select class="form-control" id="phone-select">
        <option value="2" selected><?php echo get_config('local_ippanel', 'phone2_title'); ?></option>
        <option value="1"><?php echo get_config('local_ippanel', 'phone1_title'); ?></option>
    </select>
</div>

<div class="form-group">
    <label for="cohort-select"><?php echo get_string('cohorts_select', 'local_ippanel'); ?></label>
    <select class="form-control" id="cohort-select">
        <option value="0" selected><?php echo get_string('choose'); ?></option>
        <option value="-1"><?php echo get_string('all'); ?></option>
        <?php foreach ($cohorts as $c) : ?>
            <option value="<?php echo $c->id; ?>"><?php echo $c->name; ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="table-responsive mb-3 d-none" style="max-height: 30vh;">
    <table class="table table-sm table-bordered table-hover" style="cursor: default;">
        <thead class="text-center">
            <tr>
                <th scope="col"><input type="checkbox" id="usr-chk-all" checked></th>
                <th scope="col"><?php echo get_string('name'); ?></th>
                <th scope="col"><?php echo get_string('phone2'); ?></th>
            </tr>
        </thead>
        <tbody id="table-rows">

        </tbody>
    </table>
</div>

<div class="form-group">
    <label for="numbers-txt"><?php echo get_string('custom_numbers', 'local_ippanel'); ?></label>
    <textarea class="form-control" id="numbers-txt" rows="4" dir="ltr" placeholder="<?php echo get_string('enter_numbers', 'local_ippanel'); ?>"></textarea>
</div>

<div class="form-group" id='msg-resp'>
</div>

<div class="text-center">
    <button id="send-btn" class="btn btn-primary"><?php echo get_string('submit'); ?></button>
</div>

<script>
    (() => {
        var v = <?php echo json_encode($v); ?>;
        var s = <?php echo json_encode($s); ?>;
        <?php echo file_get_contents($CFG->dirroot . '/local/ippanel/js/send.js'); ?>
    })();
</script>

<?php
echo $OUTPUT->footer();
?>