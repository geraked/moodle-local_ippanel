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
 * Settings file
 *
 * @package    local_ippanel
 * @copyright  2021 Geraked
 * @author     Rabist
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ippanel', get_string('modulename', 'local_ippanel'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading('local_ippanel_settings', '', get_string('modulename_help', 'local_ippanel')));
    $settings->add(new admin_setting_configtext('local_ippanel/apikey', get_string('apikey', 'local_ippanel'), '', 'test', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_ippanel/originator', get_string('originator', 'local_ippanel'), '', '+9810001', PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_ippanel/phone1_title', get_string('phone1_title', 'local_ippanel'), '', get_string('phone1'), PARAM_RAW));
    $settings->add(new admin_setting_configtext('local_ippanel/phone2_title', get_string('phone2_title', 'local_ippanel'), '', get_string('phone2'), PARAM_RAW));
    $settings->add(new admin_setting_configtextarea('local_ippanel/acl', get_string('acl', 'local_ippanel'), get_string('acl_desc', 'local_ippanel'), '', PARAM_RAW));
};

$ADMIN->add('root', new admin_category('ippanel', get_string('modulename', 'local_ippanel')));
$ADMIN->add('ippanel', new admin_externalpage('local_ippanel_send', get_string('sendmessage', 'local_ippanel'), $CFG->wwwroot . '/local/ippanel/send.php'));
$ADMIN->add('ippanel', new admin_externalpage('local_ippanel_report', get_string('sendreports', 'local_ippanel'), $CFG->wwwroot . '/local/ippanel/report.php'));
$ADMIN->add('ippanel', new admin_externalpage('local_ippanel_settings', get_string('settings'), $CFG->wwwroot . '/admin/settings.php?section=local_ippanel'));
