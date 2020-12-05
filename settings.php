<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     auth_psup
 * @category    admin
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Introductory explanation.
    $settings->add(new admin_setting_heading('auth_psup/pluginname',
        new lang_string('generalsettings', 'auth_psup'),
        new lang_string('generalsettings_desc', 'auth_psup')));

    // Signup page description.
    $settings->add(new admin_setting_configtext('auth_psup/signupdesc',
        get_string('signupdesc', 'auth_psup'),
        get_string('signupdesc_desc', 'auth_psup'),
        get_string('signupdesc_default', 'auth_psup'),
        PARAM_TEXT));

    // Email description description.
    $settings->add(new admin_setting_configtext('auth_psup/emaildesc',
        get_string('emaildesc', 'auth_psup'),
        get_string('emaildesc_desc', 'auth_psup'),
        get_string('emaildesc_default', 'auth_psup'),
        PARAM_TEXT));

    // Email description description.
    $settings->add(new admin_setting_configtext('auth_psup/psupiddesc',
        get_string('psupiddesc', 'auth_psup'),
        get_string('psupiddesc_desc', 'auth_psup'),
        get_string('psupiddesc_default', 'auth_psup'),
        PARAM_TEXT));

}
