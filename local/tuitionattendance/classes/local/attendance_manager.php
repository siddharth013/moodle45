<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Attendance data manager.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tuitionattendance\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Handles attendance data operations.
 */
class attendance_manager {

    /**
     * Save or update an attendance record.
     *
     * @param int $courseid Moodle course ID.
     * @param int $userid Moodle user ID.
     * @param int $sessiondate Unix timestamp for the session date.
     * @param string $status Attendance status.
     * @return int Attendance record ID.
     */
    public function save_attendance(
        int $courseid,
        int $userid,
        int $sessiondate,
        string $status
    ): int {
        global $DB;

        $existing = $DB->get_record('tuitionattendance', [
            'courseid' => $courseid,
            'userid' => $userid,
            'sessiondate' => $sessiondate,
        ]);

        $now = time();

        if ($existing) {
            $existing->status = $status;
            $existing->timemodified = $now;

            $DB->update_record('tuitionattendance', $existing);

            return (int) $existing->id;
        }

        $record = new \stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->sessiondate = $sessiondate;
        $record->status = $status;
        $record->timecreated = $now;
        $record->timemodified = $now;

        return (int) $DB->insert_record('tuitionattendance', $record);
    }

    /**
     * Get attendance for a student on a particular date.
     *
     * @param int $courseid Moodle course ID.
     * @param int $userid Moodle user ID.
     * @param int $sessiondate Unix timestamp for the session date.
     * @return object|false Attendance record or false.
     */
    public function get_attendance(
        int $courseid,
        int $userid,
        int $sessiondate
    ) {
        global $DB;

        return $DB->get_record('tuitionattendance', [
            'courseid' => $courseid,
            'userid' => $userid,
            'sessiondate' => $sessiondate,
        ]);
    }

        /**
     * Get all attendance records for a course and session date.
     *
     * @param int $courseid Moodle course ID.
     * @param int $sessiondate Unix timestamp for the session date.
     * @return array Attendance records indexed by user ID.
     */
    public function get_attendance_for_session(
        int $courseid,
        int $sessiondate
    ): array {
        global $DB;

        $records = $DB->get_records(
            'tuitionattendance',
            [
                'courseid' => $courseid,
                'sessiondate' => $sessiondate,
            ],
            '',
            'id, courseid, userid, sessiondate, status'
        );

        $attendance = [];

        foreach ($records as $record) {
            $attendance[$record->userid] = $record;
        }

        return $attendance;
    }
}