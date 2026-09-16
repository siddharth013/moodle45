<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Course and date selection form.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tuitionattendance\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for selecting a course and attendance date.
 */
class course_date_form extends \moodleform {

    /**
     * Define the form elements.
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;

        $courses = $DB->get_records_menu(
            'course',
            null,
            'fullname ASC',
            'id, fullname'
        );

        $mform->addElement(
            'select',
            'courseid',
            get_string('course'),
            $courses
        );

        $mform->addRule(
            'courseid',
            null,
            'required',
            null,
            'client'
        );

        $mform->addElement(
            'date_selector',
            'sessiondate',
            'Attendance date'
        );

        $mform->setDefault('sessiondate', time());

        $this->add_action_buttons(true, 'Load Students');
    }
}