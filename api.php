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
 * Api file
 *
 * @package    local_ippanel
 * @copyright  2021 Geraked
 * @author     Rabist
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/authlib.php');
require_once('autoload.php');

$op = optional_param('op', 0, PARAM_INT);
$cohortid = optional_param('cohortid', 0, PARAM_INT);
$bulkid = optional_param('bulkid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$acl = array_filter(array_map('trim', preg_split('/\r\n|[\r\n]/', get_config('local_ippanel', 'acl'))));

if (!is_siteadmin($USER) && !in_array($USER->id, $acl)) {
    print_error('nopermissions', 'error', '', 'view ippanel');
}

header('Content-Type: application/json');

if ($op == 1) {
    // Get users

    if ($cohortid == -1) {
        $sql = 'SELECT u.id, u.phone2, u.phone1, u.firstname, u.lastname
                FROM {user} u
                WHERE id != 1
                ORDER BY u.lastname, u.firstname';
    } else {
        $sql = 'SELECT u.id, u.phone2, u.phone1, u.firstname, u.lastname
                FROM {cohort_members} cm
                JOIN {user} u ON cm.userid = u.id
                WHERE cm.cohortid = ?
                ORDER BY u.lastname, u.firstname';
    }
    $users = $DB->get_records_sql($sql, [$cohortid,]);
    $users = array_values($users);
    echo json_encode($users);
}

if ($op == 2) {
    // Get Credit

    $apiKey = get_config('local_ippanel', 'apikey');
    $credit = 0;
    try {
        $client = new \IPPanel\Client($apiKey);
        $credit = $client->getCredit();
    } catch (Exception $e) {
        $credit = -1;
    }
    echo json_encode(number_format($credit));
}

if ($op == 3) {
    // Send message

    $message = json_decode($_POST['message']);
    $numbers = json_decode($_POST['numbers']);
    $msg = 0;

    $apiKey = get_config('local_ippanel', 'apikey');
    $originator = get_config('local_ippanel', 'originator');

    try {
        $client = new \IPPanel\Client($apiKey);
        $bulkID = $client->send($originator, $numbers, $message);

        $r = new stdClass();
        $r->bulkid = $bulkID;
        $r->originator = $originator;
        $r->message = $message;
        $r->time = time();

        $DB->insert_record('local_ippanel', $r);

        $msg = $client->getMessage($bulkID);

        $r = $DB->get_record('local_ippanel', ['bulkid' => $bulkID]);
        $r->status = $msg->status;
        $r->cost = $msg->cost;
        $r->payback = $msg->paybackCost;
        $r->rcnt = $msg->recipientsCount;
        $r->pcnt = $msg->page;
        $r->originator = $msg->number;

        $DB->update_record('local_ippanel', $r);
    } catch (Exception $e) {
        $msg = -1;
    }
    echo json_encode($msg);
}

if ($op == 4) {
    // Update message

    $msg = 0;
    $apiKey = get_config('local_ippanel', 'apikey');

    try {
        $client = new \IPPanel\Client($apiKey);
        $msg = $client->getMessage($bulkid);

        $r = $DB->get_record('local_ippanel', ['bulkid' => $bulkid]);
        $r->status = $msg->status;
        $r->cost = $msg->cost;
        $r->payback = $msg->paybackCost;
        $r->rcnt = $msg->recipientsCount;
        $r->pcnt = $msg->page;
        $r->originator = $msg->number;

        $DB->update_record('local_ippanel', $r);
    } catch (Exception $e) {
        $msg = -1;
    }
    echo json_encode($msg);
}

if ($op == 5) {
    // Get not finished messages

    $sql = 'SELECT i.bulkid
            FROM {local_ippanel} i
            WHERE i.status != "finish"';
    $rows = $DB->get_records_sql($sql);
    $rows = array_values($rows);
    echo json_encode($rows);
}

if ($op == 6) {
    // Get message delivery statuses

    $statuses = 0;
    $apiKey = get_config('local_ippanel', 'apikey');

    try {
        $client = new \IPPanel\Client($apiKey);
        $statuses = $client->fetchStatuses($bulkid, $page, 50);

        foreach ($statuses[0] as $s) {
            $sql = 'SELECT u.id, u.firstname, u.lastname
                    FROM {user} u
                    WHERE u.phone2 LIKE "%' . substr($s->recipient, -10) . '" OR u.phone1 LIKE "%' . substr($s->recipient, -10) . '"';
            $rows = $DB->get_records_sql($sql);
            $rows = array_values($rows);

            $s->users = [];
            foreach ($rows as $r) {
                $s->users[] = $r;
            }
        }
    } catch (Exception $e) {
        $statuses = -1;
    }
    echo json_encode($statuses);
}

if ($op == 7) {
    // Get message

    $r = $DB->get_record('local_ippanel', ['bulkid' => $bulkid], 'message');
    echo json_encode($r);
}
