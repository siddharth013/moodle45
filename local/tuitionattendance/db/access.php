<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Capability definitions.
 *
 * @package    local_tuitionattendance
 * @copyright  2026 Siddharth Patel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'local/tuitionattendance:markattendance' => [
        'riskbitmask' => RISK_DATALOSS,

        'captype' => 'write',

        'contextlevel' => CONTEXT_COURSE,

        'archetypes' => [
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        ],
    ],
];