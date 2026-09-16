<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Teacher attendance page.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tuitionattendance/attendance.php'));
$PAGE->set_title('Mark Attendance');
$PAGE->set_heading('Mark Tuition Attendance');

require_once(__DIR__ . '/classes/form/course_date_form.php');
require_once(__DIR__ . '/classes/local/student_manager.php');
require_once(__DIR__ . '/classes/local/attendance_manager.php');

/*
 * ---------------------------------------------------------
 * STEP 1: Process attendance submission.
 * ---------------------------------------------------------
 */

if (optional_param('saveattendance', false, PARAM_BOOL)) {

    require_sesskey();

    $courseid = required_param('courseid', PARAM_INT);
    $sessiondate = required_param('sessiondate', PARAM_INT);

    $attendance = optional_param_array(
        'attendance',
        [],
        PARAM_ALPHA
    );

    $attendancemanager =
        new \local_tuitionattendance\local\attendance_manager();

    foreach ($attendance as $userid => $status) {

        if (!in_array($status, ['present', 'absent'], true)) {
            continue;
        }

        $attendancemanager->save_attendance(
            (int) $courseid,
            (int) $userid,
            (int) $sessiondate,
            $status
        );
    }

    redirect(
        new moodle_url('/local/tuitionattendance/attendance.php'),
        'Attendance saved successfully.',
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

/*
 * ---------------------------------------------------------
 * STEP 2: Create the course/date selection form.
 * ---------------------------------------------------------
 */

$mform = new \local_tuitionattendance\form\course_date_form();

if ($mform->is_cancelled()) {
    redirect(
        new moodle_url('/local/tuitionattendance/index.php')
    );
}

/*
 * ---------------------------------------------------------
 * STEP 3: Course/date form submitted.
 * Load enrolled students.
 * ---------------------------------------------------------
 */

if ($data = $mform->get_data()) {

    $studentmanager =
        new \local_tuitionattendance\local\student_manager();

    $students = $studentmanager->get_enrolled_students(
        (int) $data->courseid
    );

    $attendancemanager =
    new \local_tuitionattendance\local\attendance_manager();

$existingattendance =
    $attendancemanager->get_attendance_for_session(
        (int) $data->courseid,
        (int) $data->sessiondate
    );

    echo $OUTPUT->header();

    echo $OUTPUT->heading('Mark Attendance');

    echo $OUTPUT->heading(
        'Enrolled Students',
        3
    );

    echo html_writer::start_tag('form', [
        'method' => 'post',
        'action' => new moodle_url(
            '/local/tuitionattendance/attendance.php'
        )
    ]);

    echo html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey()
    ]);

    if (empty($students)) {

        echo $OUTPUT->notification(
            'No students are enrolled in this course.',
            \core\output\notification::NOTIFY_INFO
        );

    } else {

        $table = new html_table();

        $table->head = [
            'Student',
            'Email',
            'Attendance'
        ];

        foreach ($students as $student) {

            $presentid =
                'attendance_present_' . $student->id;

            $absentid =
                'attendance_absent_' . $student->id;

            $presentchecked = true;

if (isset($existingattendance[$student->id])) {
    $presentchecked =
        $existingattendance[$student->id]->status === 'present';
}

$presentattributes = [
    'type' => 'radio',
    'id' => $presentid,
    'name' => 'attendance[' . $student->id . ']',
    'value' => 'present'
];

if ($presentchecked) {
    $presentattributes['checked'] = 'checked';
}

$present = html_writer::empty_tag(
    'input',
    $presentattributes
);

            $presentlabel = html_writer::tag(
                'label',
                'Present',
                ['for' => $presentid]
            );

            $absentchecked = false;

if (isset($existingattendance[$student->id])) {
    $absentchecked =
        $existingattendance[$student->id]->status === 'absent';
}

$absentattributes = [
    'type' => 'radio',
    'id' => $absentid,
    'name' => 'attendance[' . $student->id . ']',
    'value' => 'absent'
];

if ($absentchecked) {
    $absentattributes['checked'] = 'checked';
}

$absent = html_writer::empty_tag(
    'input',
    $absentattributes
);

            $absentlabel = html_writer::tag(
                'label',
                'Absent',
                ['for' => $absentid]
            );

            $attendancecontrols =
                $present . ' ' . $presentlabel . ' ' .
                $absent . ' ' . $absentlabel;

            $table->data[] = [
                fullname($student),
                s($student->email),
                $attendancecontrols
            ];
        }

        echo html_writer::table($table);

        echo html_writer::empty_tag('br');

        echo html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'courseid',
            'value' => $data->courseid
        ]);

        echo html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'sessiondate',
            'value' => $data->sessiondate
        ]);

        echo html_writer::tag(
            'button',
            'Save Attendance',
            [
                'type' => 'submit',
                'name' => 'saveattendance',
                'value' => '1',
                'class' => 'btn btn-primary'
            ]
        );
    }

    echo html_writer::end_tag('form');

    echo $OUTPUT->footer();

    exit;
}

/*
 * ---------------------------------------------------------
 * STEP 4: Initial page - display course/date form.
 * ---------------------------------------------------------
 */

echo $OUTPUT->header();

echo $OUTPUT->heading('Mark Attendance');

$mform->display();

echo $OUTPUT->footer();