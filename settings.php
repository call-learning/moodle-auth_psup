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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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

    $settings->add(new admin_setting_heading('auth_psup/pluginname',
        new lang_string('generalsettings', 'auth_psup'),
        new lang_string('generalsettings_desc', 'auth_psup')));

    // Parcoursup identifier regexp validation.
    $settings->add(new admin_setting_configtext('auth_psup/psupidregexp',
        get_string('psupidregexp', 'auth_psup'),
        get_string('psupidregexp_desc', 'auth_psup'),
        '/^[0-9]{6,8}$/',
        PARAM_TEXT));

}
