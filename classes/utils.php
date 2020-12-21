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

use core\notification;
use core_renderer;
use core_user;
use html_writer;
use moodle_url;

/**
 * Class utils
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    const USER_PREFS_EMAIL_CONFIRMED = 'auth_psup_emailconfirmed';
    const USER_PREFS_WANTS_URL = 'auth_psup_wantsurl';

    /**
     * Add info to field
     *
     * @param $mform
     * @param $fieldname
     * @param $desc
     */
    public static function add_info_to_field(&$mform, $fieldname, $desc) {
        /** @var core_renderer $icon */
        $icon = html_writer::tag('i',
            '',
            array(
                'class' => 'icon fa fa-exclamation-triangle text-warning fa-fw',
                'title' => ''
            )
        );
        $labelfor = \html_writer::label(
            $icon . $desc,
            $fieldname,
            true,
            array('class' => 'psup-additional-description'));
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
            // Check against regular expression.
            if (!static::is_valid_psup_identifier($data['psupid'])) {
                $errors['psupid'] = get_string('invalidpsupid', 'auth_psup');
            }

        }
        return $errors;
    }

    /**
     * Check if it is a valid identifier
     *
     * @param string $value
     * @return bool
     * @throws \dml_exception
     */
    public static function is_valid_psup_identifier($value) {
        $isvalididentifier = true;
        $regexp = get_config('auth_psup', 'psupidregexp');
        if ($regexp && (preg_match($regexp, $value) === 0)) {
            $isvalididentifier = false;
        }
        // Check allowed characters.
        if ($value !== core_user::clean_field($value, 'username')) {
            $isvalididentifier = false;
        }
        return $isvalididentifier;
    }

    /**
     * Add a CTA to the top of the page, so we display a link so the user can confirm the account.
     *
     * @param $user
     */
    public static function display_cta_send_new_email($user) {
        if ($user->auth == 'psup' &&
            empty(get_user_preferences(static::USER_PREFS_EMAIL_CONFIRMED, false, $user))) {
            $actions = [
                [
                    'title' => get_string('emailconfirmationresend'),
                    'url' => new moodle_url('/auth/psup/resendconfirmation.php',
                        array('returnurl' => qualified_me())),
                    'data' => []
                ],
            ];
            $icon = [
                'pix' => 'i/warning',
                'component' => 'core'
            ];

            notification::add_call_to_action($icon,
                get_string('mustvalidateemail', 'auth_psup'),
                $actions
            );
        }
    }
}