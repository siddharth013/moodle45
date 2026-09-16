<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Upgrade steps for the Tuition Attendance plugin.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_tuitionattendance_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026091701) {
        $table = new xmldb_table('tuitionattendance');

        $field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);

        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file(
                __DIR__ . '/install.xml',
                'tuitionattendance'
            );
        }

        upgrade_plugin_savepoint(
            true,
            2026091701,
            'local',
            'tuitionattendance'
        );
    }

    return true;
}