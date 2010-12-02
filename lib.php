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
 * This file contains the lirary functions for the courselogo plugin.
 */

/**
 * Prevent direct access to this script
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Extends the navigation for the course logo plugin.
 * 
 * This function gets called when the navigation is generated.
 * It then looks to see if there is a course other than the front page set and if
 * so adds a link to the courses navigation to allow the user to upload a file.
 *
 * @global moodle_page $PAGE
 * @param global_navigation $nav
 * @return bool 
 */
function courselogo_extends_navigation(global_navigation $nav) {
    global $PAGE;
    
    /** 
     * Get the current course
     */
    $courseid = $PAGE->course->id;
    if ($courseid == SITEID) {
        /**
         * Can't set a course logo for the front page yet.... I havn't had time
         * to implement this yet.
         */
        return false;
    }
    
    if (!has_capability('moodle/course:update', get_context_instance(CONTEXT_COURSE, $courseid))) {
        /**
         * The user doesn't have permisison to set a course logo.
         */
        return false;
    }
    
    /**
     * Locate the course node in the navigation for the current course.
     */
    $node = $nav->find($courseid, navigation_node::TYPE_COURSE);
    if ($node) {
        /**
         * Add a link to the bottom of the navigation to the index page
         * so that the current user can easily get to the page to associate an
         * image with this course.
         */
        $url = new moodle_url('/local/courselogo/index.php', array('course'=>$courseid));
        $node->add(get_string('navtitle', 'local_courselogo'), $url, navigation_node::TYPE_SETTING, null, 'chooselogo', new pix_icon('chooselogo', '', 'local_courselogo'));
        return true;
    }
    return false;
}

/**
 * Serves the uploaded file for a course when requested. 
 * 
 * You should NEVER call this function. 
 * It is disigned to serve the files and should only be called through the 
 * plufingfile.php script.
 * 
 * If you need a link you should call {@link local_courselogo_get_logo_url()}
 *
 * @global type $CFG Moodle config var
 * @global moodle_database $DB Database
 * @param stdClass $course The course this file is associated with.
 * @param stdClass $cm The course module this file is associated with.
 * @param stdClass $context The context this file was uploaded from.
 * @param string $filearea The filearea where the file is saved.
 * @param array $args An array of additional args provided to pluginfile script.
 * @param bool $forcedownload If set to true the file will force a download rather
 *                             than allowing the file to displayed inline.
 * @return bool|file
 */
function local_courselogo_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    /**
     * It HAS to be a course context.
     */
    if ($context->contextlevel != CONTEXT_COURSE) {
        return false;
    }

    /**
     * You must be able to access this course to see it.
     */
    require_login($course);
    array_shift($args);
    $relativepath = implode('/', $args);
    /**
     * Generate the full path for the file.
     */
    $fullpath = "/{$context->id}/local_courselogo/logo/0/$relativepath";

    /**
     * Search for the file in the file storage (File API).
     */
    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    /**
     * Send the stored file :)
     */
    send_stored_file($file, 0, 0, $forcedownload);
}

/**
 * Returns the path to the logo for the current course.
 *
 * @global moodle_page $PAGE
 * @global core_renderer $OUTPUT
 * @global stdClass $CFG
 * @param stdClass $course The course to get the image for.
 * @param string $defaultimg The default image to use. By default in your theme's pix directory)
 * @param string $defaultcompontent The component where the default image exists, normally the theme.
 * @return string|false The path to the image of false if default was false and
 *                       there is no image.
 */
function local_courselogo_get_logo_url($course, $defaultimg = 'logo', $defaultcompontent = 'theme') {
    global $PAGE, $OUTPUT, $CFG;
    
    $path = false;
    
    /**
     * The front page course cannot have an image associated with it.
     */
    if ($course->id != SITEID) {
        /**
         * Get the context for the course.
         */
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        /**
         * Search the file storage (File API) for an associated image file.
         */
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_courselogo', 'logo', 0);
        foreach ($files as $file) {
            /**
             * Check if the file is a valid image.
             */
            if ($file->is_valid_image()) {
                /**
                 * Get the path to the image.
                 */
                $filename = $file->get_filename();
                $path = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$context->id.'/local_courselogo/logo/0/'.$filename);
                break;
            }
        }
    }
    /**
     * If the path is empty and we have a default image set the path to the
     * default image.
     */
    if (empty($path) && is_string($defaultimg)) {
        $path = $OUTPUT->pix_url($defaultimg, $defaultcompontent);
    }
    return $path;
}

/**
 * Get an <img> tag for the course logo.
 *
 * @param stdClass $course
 * @param string $defaultimg
 * @param string $defaultcompontent
 * @param array $attributes
 * @return string 
 */
function local_courselogo_get_logo_html($course, $defaultimg = 'logo', $defaultcompontent = 'theme', array $attributes = array()) {
    $properties = array_merge(array(
        'src' => local_courselogo_get_logo_url($course, $defaultimg, $defaultcompontent),
        'alt' => $course->fullname,
        'class' => 'customcourselogo'
    ), $attributes);
    return html_writer::empty_tag('img', $properties);
}