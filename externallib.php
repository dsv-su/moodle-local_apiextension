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
 * External local_apiextension API
 *
 * @package    local_apiextension
 * @category   external
 * @copyright  2022 Pavel Sokolov <pavel.m.sokolov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/gradelib.php");

class local_apiextension_external extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function get_user_logs_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'userid' => new external_value(PARAM_INT, 'id of the user', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'id of the course', VALUE_REQUIRED)
            )
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_user_logs_returns()
    {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'log id'),
                    'relateduserid' => new external_value(PARAM_INT, 'affected user id'),
                    //'contextname' => new external_value(PARAM_TEXT, 'event context name'),
                    'contextid' => new external_value(PARAM_INT, 'event context id'),
                    'component' => new external_value(PARAM_TEXT, 'component'),
                    'eventname' => new external_value(PARAM_TEXT, 'event name'),
                    'target' => new external_value(PARAM_TEXT, 'target'),
                    'action' => new external_value(PARAM_TEXT, 'action'),
                    'objecttable' => new external_value(PARAM_TEXT, 'component'),
                    'objectid' => new external_value(PARAM_TEXT, 'objectid'),
                    'timecreated' => new external_value(PARAM_INT, 'timecreated'),
                )
            ), 'List of log objects.'
        );
    }

    /**
     * Get logs
     *
     * @param $userid
     * @param $courseid
     * @return array of log events
     * @throws dml_exception
     * @since Moodle 2.2
     */
    public static function get_user_logs($userid, $courseid): array
    {
        global $DB;
        // Logs should be in a separate file since it has to be done in chunks
        $logs = $DB->get_records_sql("SELECT * from {logstore_standard_log} WHERE courseid = ? AND userid = ? ORDER BY id DESC", [$courseid, $userid]);

        /* This code would include 'event context name'
        foreach ($logs as $log) {
            // Add context name.
            if ($log->contextid) {
                $context = context::instance_by_id($log->contextid, IGNORE_MISSING);
                if ($context) {
                    $contextname = $context->get_context_name(true);
                } else {
                    $contextname = get_string('other');
                }
            } else {
                $contextname = get_string('other');
            }
            $log->contextname = $contextname;
        }
        */

        return $logs;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function update_grade_item_parameters(): external_function_parameters
    {
        return new external_function_parameters(
            array(
                'scaleid' => new external_value(PARAM_INT, 'id of the scale', VALUE_REQUIRED),
                'courseid' => new external_value(PARAM_INT, 'id of the course', VALUE_REQUIRED),
                'gradeitemid' => new external_value(PARAM_INT, 'id of the grade item', VALUE_OPTIONAL)
            )
        );
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function update_grade_item_returns()
    {
        return new external_single_structure(
            array(
                'gradeitemid' => new external_value(PARAM_INT, 'grade item id'),
                'scaleid' => new external_value(PARAM_INT, 'scale id'),
                'courseid' => new external_value(PARAM_INT, 'course id'),
                'timemodified' => new external_value(PARAM_INT, 'timestamp')
            )
        );
    }

    /**
     * Update grade item
     *
     * @param $scaleid
     * @param $courseid
     * @param null $gradeitemid
     * @return array
     * @throws coding_exception
     * @since Moodle 2.2
     */
    public static function update_grade_item($scaleid, $courseid, $gradeitemid = null): array
    {
        if (!$gradeitemid) {
            $grade_item = grade_item::fetch_course_item($courseid);
        } else {
            $grade_item = grade_item::fetch(array('id' => $gradeitemid, 'courseid' => $courseid));
        }
        $grade_item->gradetype = GRADE_TYPE_SCALE;
        $grade_item->scaleid = $scaleid;
        $grade_item->scale = grade_scale::fetch(array('id' => $scaleid));
        $grade_item->scale->scale_items = $grade_item->scale->load_items();
        //$grade_item->set_locked(1);
        grade_regrade_final_grades($courseid);
        $gradeitemid = $grade_item->id;

        $result = $grade_item->update('external');
        if ($result) {
            $updated_item = grade_item::fetch(array('id' => $gradeitemid));
            return [
                'gradeitemid' => $gradeitemid,
                'scaleid' => $updated_item->load_scale()->id,
                'courseid' => $updated_item->courseid,
                'timemodified' => $updated_item->timemodified
            ];
        } else {
            throw new coding_exception('Grade item update resulted in an error');
        }
    }
}
