<?php
/**
 * Course logo - A local moodle plugin.
 * 
 * The course logo plugin provides a place where priviledged users within a Moodle
 * course can upload an image that relates to the course.
 * That image can then be used by theme designers and displayed anywhere they want
 * within their theme.
 * It is important to note that this plugin is probably not the best way to go
 * about this however it does work.
 * More information on this plugin, how to install and use it are included within
 * the README file that comes with this plugin.
 * 
 * The courselogo plugin like Moodle is free software: you can redistribute it 
 * and/or modify it under the terms of the GNU General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * The courselogo plugin like Moodle is distributed in the hope that it will be 
 * useful,but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * @package     local_courselogo
 * @copyright   2010 Sam Hemelryk
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This file contains the uninstall routine for the courselogo plugin.
 */

/**
 * Deletes all files uploaded through the courselogo plugin so that when you
 * delete it from the local directory you won't still have redundant files in
 * your file system.
 *
 * @global moodle_database $DB 
 */
function xmldb_local_courselogo_uninstall() {
    global $DB;

    $fs = get_file_storage();
    
    list($ctxselect, $ctxjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
    $rs = $DB->get_records_sql("SELECT c.id $ctxselect FROM {course} c $ctxjoin");
    foreach ($rs as $course) {
        $contextid = $course->ctxid;
        context_instance_preload($course);
        $fs->delete_area_files($contextid, 'local_courselogo');
    }
    return true;
}