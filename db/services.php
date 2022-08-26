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
 * Web service local plugin template external functions and service definitions.
 *
 * @package    local_apiextension
 * @category   external
 * @copyright  2022 Pavel Sokolov <pavel.m.sokolov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
    'core_user_get_user_logs' => array(
        'classname'   => 'local_apiextension_external',
        'methodname'  => 'get_user_logs',
        'classpath'   => 'local/apiextension/externallib.php',
        'description' => 'Returns logs for the given user specified by id per given course id',
        'type'        => 'read'
    ),
    'core_grades_update_grade_item' => array(
        'classname'   => 'local_apiextension_external',
        'methodname'  => 'update_grade_item',
        'classpath'   => 'local/apiextension/externallib.php',
        'description' => 'Updates grade item by the id or course id',
        'type'        => 'write'
    )
);
