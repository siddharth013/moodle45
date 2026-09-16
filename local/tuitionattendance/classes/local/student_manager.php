<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Student data manager.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tuitionattendance\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles student-related operations.
 */
class student_manager {

    /**
     * Get students enrolled in a course.
     *
     * @param int $courseid Moodle course ID.
     * @return array
     */
    public function get_enrolled_students(int $courseid): array {
        global $DB;

        $context = \context_course::instance($courseid);

        $users = get_enrolled_users(
            $context,
            '',
            0,
            'u.id, u.firstname, u.lastname, u.email',
            'u.lastname ASC, u.firstname ASC',
            0,
            0,
            true
        );

        $studentrole = $DB->get_record(
            'role',
            ['shortname' => 'student'],
            '*',
            MUST_EXIST
        );

        $students = [];

        foreach ($users as $user) {

            $roles = get_user_roles(
                $context,
                $user->id,
                false
            );

            foreach ($roles as $role) {
                if ((int) $role->roleid === (int) $studentrole->id) {
                    $students[$user->id] = $user;
                    break;
                }
            }
        }

        return array_values($students);
    }
}