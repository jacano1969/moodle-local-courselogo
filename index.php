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
 * This file displays an page to the user that allows them to upload an image to
 * associate with this course.
 * 
 * You must supply one param to this script the course id of the course to focus
 * on. Within that course you must pass the require_login test and have
 * the course:update capability.
 */

/**
 * Include the required files
 *  - Moodles config.php
 *  - Courselogo lib
 *  - The form to upload an image
 */
require_once('../../config.php');
require_once($CFG->dirroot.'/local/courselogo/lib.php');
require_once($CFG->dirroot.'/local/courselogo/index_form.php');

/**
 * The course id to focus on
 */
$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $courseid, MUST_EXIST);

/**
 * Access and capability checks
 */
require_login($course);
require_capability('moodle/course:update', $context);

/**
 * Setup the $PAGE object for this page
 */
$PAGE->set_url(new moodle_url('/local/courselogo/index.php', array('course'=>$courseid)));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

/**
 * Create the form and track its success
 */
$success = false;
$mform = new local_courselogo_form($PAGE->url);

/**
 * Process the form if it has been submit
 */
$data = $mform->get_data();
if ($data && confirm_sesskey()) {
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'local_courselogo', 'logo', 0);
    if ($newfilename = $mform->get_new_filename('logofile')) {
        $mform->save_stored_file('logofile', $context->id, 'local_courselogo', 'logo', 0, '/', $newfilename, true);
        $success = true;
    }
}

/**
 * Display the page
 */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('selectalogo','local_courselogo'));
if ($success) {
    /**
     * Display a notice informing the user that the file has been successfully 
     * updated.
     */
    echo $OUTPUT->notification(get_string('uploadsuccess', 'local_courselogo'), 'notifysuccess');
}
/**
 * Attempt to get the current course logo.
 * Use false as the default so that we get back an empty string if there isn't one.
 */
$logo = local_courselogo_get_logo_url($course, false);
if (!empty($logo)) {
    /**
     * Display the current course logo.
     */
    echo html_writer::start_tag('div', array('class'=>'existinglogo'));
    echo html_writer::empty_tag('img', array('src'=>$logo, 'alt'=>get_string('currentlogo', 'local_courselogo')));
    echo html_writer::end_tag('div');
}
$mform->display();
echo $OUTPUT->footer();