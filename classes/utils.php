<?php
// This file is part of Moodle - http://moodle.org/
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
 * Utilities
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_psup;

use core_user;

/**
 * Class utils
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Add info to field
     *
     * @param $mform
     * @param $fieldname
     * @param $desc
     */
    public static function add_info_to_field(&$mform, $fieldname, $desc) {
        $labelfor = \html_writer::label($desc, $fieldname);
        $mform->addElement('static', $fieldname . 'desc', '', $labelfor);
    }

    /**
     * Validate parcoursup identifier
     *
     * @param $mform
     * @param $fieldname
     * @param $desc
     */
    public static function validate_psup_identifier($data, $files) {
        global $DB, $CFG;

        $errors = [];
        if ($DB->record_exists('user', array('username' => $data['psupid'],
            'mnethostid' => $CFG->mnet_localhost_id))) {
            $errors['psupid'] = get_string('userexists', 'auth_psup');
        } else {
            // Check allowed characters.
            if ($data['psupid'] !== core_user::clean_field($data['psupid'], 'username')) {
                $errors['psupid'] = \get_string('invalidpsupid', 'auth_psup');
            }
            // Check that it is only number eventually prefixed by 'p'.
            if ($data['psupid'] !== self::filter_psupid($data['psupid'])) {
                $errors['psupid'] = \get_string('invalidpsupid', 'auth_psup');
            }
        }
        return $errors;
    }

    /**
     * Add info to field
     *
     * @param $mform
     * @param $fieldname
     * @param $desc
     */
    public static function filter_psupid($text) {
        $text = strtolower($text);
        $text = trim($text);
        $text = (strpos($text, 'p') === 0) ? $text : 'p' . $text;
        return $text;
    }
}