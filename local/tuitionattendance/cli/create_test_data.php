<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Create test courses and students for the Tuition Attendance plugin.
 *
 * Run from the Moodle root:
 * php local/tuitionattendance/cli/create_test_data.php
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');

require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/enrollib.php');

$categoryname = 'Class 10';

$courses = [
    'Mathematics',
    'Science',
    'English',
    'Social Science',
    'Hindi',
];

$students = [
    [
        'username' => 'class10student1',
        'firstname' => 'Aarav',
        'lastname' => 'Shah',
        'email' => 'class10student1@example.com',
    ],
    [
        'username' => 'class10student2',
        'firstname' => 'Diya',
        'lastname' => 'Patel',
        'email' => 'class10student2@example.com',
    ],
    [
        'username' => 'class10student3',
        'firstname' => 'Vivaan',
        'lastname' => 'Mehta',
        'email' => 'class10student3@example.com',
    ],
];

$admin = get_admin();

echo "Creating Class 10 test data...\n\n";

/*
 * ---------------------------------------------------------
 * 1. Create or find category.
 * ---------------------------------------------------------
 */

$category = $DB->get_record(
    'course_categories',
    ['name' => $categoryname],
    '*',
    IGNORE_MULTIPLE
);

if (!$category) {
    $category = \core_course_category::create([
        'name' => $categoryname,
        'description' => 'Test category for Class 10 tuition courses.',
        'descriptionformat' => FORMAT_HTML,
        'parent' => 0,
        'visible' => 1,
    ]);

    echo "Created category: {$categoryname}\n";
} else {
    echo "Category already exists: {$categoryname}\n";
}

/*
 * ---------------------------------------------------------
 * 2. Create or find test students.
 * ---------------------------------------------------------
 */

$studentrole = $DB->get_record(
    'role',
    ['shortname' => 'student'],
    '*',
    MUST_EXIST
);

$teacherrole = $DB->get_record(
    'role',
    ['shortname' => 'teacher'],
    '*',
    MUST_EXIST
);

$studentusers = [];

foreach ($students as $studentdata) {

    $student = $DB->get_record(
        'user',
        ['username' => $studentdata['username']]
    );

    if (!$student) {

        $student = create_user_record(
            $studentdata['username'],
            'Moodle@12345'
        );

        $student->firstname = $studentdata['firstname'];
        $student->lastname = $studentdata['lastname'];
        $student->email = $studentdata['email'];
        $student->confirmed = 1;
        $student->mnethostid = $CFG->mnet_localhost_id;

        user_update_user($student, false);

        echo "Created student: {$student->firstname} {$student->lastname}\n";

    } else {

        echo "Student already exists: {$student->firstname} {$student->lastname}\n";
    }

    $studentusers[] = $student;
}

/*
 * ---------------------------------------------------------
 * 3. Create courses.
 * ---------------------------------------------------------
 */

foreach ($courses as $coursename) {

    $shortname = 'CLASS10_' . strtoupper(
        str_replace(' ', '_', $coursename)
    );

    $course = $DB->get_record(
        'course',
        ['shortname' => $shortname]
    );

    if (!$course) {

        $course = \core_course_category::get(
            $category->id
        )->create_course([
            'fullname' => 'Class 10 - ' . $coursename,
            'shortname' => $shortname,
            'category' => $category->id,
            'summary' => 'Test course for Class 10 ' . $coursename . ' attendance.',
            'summaryformat' => FORMAT_HTML,
            'visible' => 1,
        ]);

        echo "Created course: {$course->fullname}\n";

    } else {

        echo "Course already exists: {$course->fullname}\n";
    }

    /*
     * -----------------------------------------------------
     * 4. Get/create manual enrolment instance.
     * -----------------------------------------------------
     */

    $manual = enrol_get_plugin('manual');

    $enrolinstances = enrol_get_instances(
        $course->id,
        true
    );

    $manualinstance = null;

    foreach ($enrolinstances as $instance) {

        if ($instance->enrol === 'manual') {
            $manualinstance = $instance;
            break;
        }
    }

    if (!$manualinstance) {

        $manualinstance = $manual->add_instance(
            $course,
            [
                'status' => ENROL_INSTANCE_ENABLED,
                'name' => 'Manual enrolments',
            ]
        );
    }

    /*
     * -----------------------------------------------------
     * 5. Enrol administrator as teacher.
     * -----------------------------------------------------
     */

    $manual->enrol_user(
        $manualinstance,
        $admin->id,
        $teacherrole->id
    );

    /*
     * -----------------------------------------------------
     * 6. Enrol test students.
     * -----------------------------------------------------
     */

    foreach ($studentusers as $student) {

        $manual->enrol_user(
            $manualinstance,
            $student->id,
            $studentrole->id
        );
    }

    echo "  Enrolled administrator + "
        . count($studentusers)
        . " students.\n";
}

echo "\n";
echo "========================================\n";
echo "Test data creation completed.\n";
echo "========================================\n";
echo "Category: Class 10\n";
echo "Courses: " . count($courses) . "\n";
echo "Students: " . count($studentusers) . "\n";
echo "Test student password: Moodle@12345\n";