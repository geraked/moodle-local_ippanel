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
 * Ippanel table implementation.
 *
 * @package    local_ippanel
 * @copyright  2021 Geraked
 * @author     Rabist
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class report_table extends table_sql
{

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid)
    {
        parent::__construct($uniqueid);

        // Define the list of columns to show.
        $columns = ['bulkid', 'originator', 'message', 'status', 'cost', 'payback', 'rcnt', 'pcnt', 'time'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = [
            get_string('id', 'local_ippanel'),
            get_string('originator', 'local_ippanel'),
            get_string('message_context', 'local_ippanel'),
            get_string('status'),
            get_string('cost', 'local_ippanel'),
            get_string('payback', 'local_ippanel'),
            get_string('rcnt', 'local_ippanel'),
            get_string('pcnt', 'local_ippanel'),
            get_string('time'),
        ];
        $this->define_headers($headers);
    }

    /**
     * This function is called for each data row to allow processing of the value.
     *
     * @param object $values Contains object with all the values of record.
     * 
     */
    function col_bulkid($values)
    {
        if ($this->is_downloading()) {
            return $values->bulkid;
        } else {
            return "<a href class='bid-links' data-toggle='modal' data-target='#report-modal' data-whatever='$values->bulkid'>$values->bulkid</a> <a href class='ref-links' data-whatever='$values->bulkid'><i class='fa fa-refresh'></i></a>";
        }
    }

    /**
     * This function is called for each data row to allow processing of the value.
     *
     * @param object $values Contains object with all the values of record.
     * 
     */
    function col_cost($values)
    {
        return number_format($values->cost);
    }

    /**
     * This function is called for each data row to allow processing of the value.
     *
     * @param object $values Contains object with all the values of record.
     * 
     */
    function col_payback($values)
    {
        return number_format($values->payback);
    }

    /**
     * This function is called for each data row to allow processing of the value.
     *
     * @param object $values Contains object with all the values of record.
     * 
     */
    function col_time($values)
    {
        return userdate($values->time, '%H:%M - %Y/%m/%d');
    }
}
