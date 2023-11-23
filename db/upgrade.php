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
    if ($oldversion < 202311012024) {
        \auth_psup\utils::create_user_fields_auth_psup();
        $updatetask = new \auth_psup\task\update_parcoursup_users();
        core\task\manager::queue_adhoc_task($updatetask);
        upgrade_plugin_savepoint(true, 202311012024, 'auth', 'psup');

    }
    return true;
}
