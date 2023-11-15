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
 * Execute auth_psup upgrade from the given old version.
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use auth_psup\utils;

/**
 * Execute auth_psup upgrade from the given old version.
 *
 * @param int $oldversion
 * @return true
 * @throws downgrade_exception
 * @throws upgrade_exception
 */
function xmldb_auth_psup_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020120209) {
        set_config('psupidregexp', '/^[0-9]{6,8}$/', 'auth_psup');
        upgrade_plugin_savepoint(true, 2020120209, 'auth', 'psup');
    }
    if ($oldversion < 202311012023) {
        global $DB;
        \auth_psup\utils::create_user_fields_auth_psup();
        $currentyear = date('Y');
        set_config('psupcurrentsession', $currentyear, 'auth_psup');
        // Now fill existing user data with current session and username.
        $existingusers = $DB->get_recordset('user', ['auth' => 'psup']);
        $DB->get_field('user_info_field', 'id', ['shortname' => 'psupcurrentsession']);
        $oldsessionname = "prev";
        foreach ($existingusers as $user) {
            $currentpsupid = $user->username;
            $user->username .= "_" . $oldsessionname;
            $user->timemodified = time();
            $DB->update_record('user', $user);

            utils::create_user_info_data($user->id, $currentpsupid, $oldsessionname);
        }
        upgrade_plugin_savepoint(true, 202311012023, 'auth', 'psup');

    }
    return true;
}
