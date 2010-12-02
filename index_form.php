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
 * This file contains a moodle form that is used to allow the user to upload
 * and associate an image with a course.
 */

/**
 * Prevent direct access to this script
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

/**
 * Include the forms library
 */
require_once($CFG->libdir.'/formslib.php');

/**
 * This form allows the user to upload a single that will be associated with the
 * course.
 */
class local_courselogo_form extends moodleform {
    
    /**
     * Defines the elements that will appear in the form.
     *
     * @global stdClass $CFG Moodle config var
     */
    protected function definition() {
        global $CFG;
        
        $form = $this->_form;
        
        $fileoptions = array(
            'maxbytes'=>get_max_upload_file_size($CFG->maxbytes),
            'maxfiles'=>1
        );
        $form->addElement('filepicker', 'logofile', get_string('selectanimage', 'local_courselogo'), null, $fileoptions);
        
        $this->add_action_buttons(false, get_string('savechanges'));
    }
}