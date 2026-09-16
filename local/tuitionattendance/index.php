<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Tuition Attendance landing page.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/tuitionattendance/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_tuitionattendance'));
$PAGE->set_heading(get_string('pluginname', 'local_tuitionattendance'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'local_tuitionattendance'));

echo $OUTPUT->notification(
    'The Tuition Attendance plugin is installed successfully.',
    \core\output\notification::NOTIFY_SUCCESS
);

echo $OUTPUT->footer();