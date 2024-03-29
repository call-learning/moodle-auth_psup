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
 * Authentication class for psup is defined here.
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/authlib.php');

// For further information about authentication plugins please read
// https://docs.moodle.org/dev/Authentication_plugins.
//
// The base class auth_plugin_base is located at /lib/authlib.php.
// Override functions as needed.

/**
 * Authentication class for psup.
 */
class auth_plugin_psup extends auth_plugin_base {

    /**
     * Set the properties of the instance.
     */
    public function __construct() {
        $this->authtype = 'psup';
        $this->config = new stdClass();
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username.
     * @param string $password The password.
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG, $DB;

        // Validate the login by using the Moodle user table.
        // Remove if a different authentication method is desired.
        $user = $DB->get_record('user',
            array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id, 'auth' => $this->authtype));

        // User does not exist.
        if (!$user) {
            return false;
        }
        $validated = validate_internal_user_password($user, $password);
        return $validated;
    }

    /**
     * Returns true if this authentication plugin can change the user's password.
     *
     * @return bool
     */
    public function can_change_password() {
        return true;
    }

    /**
     * Returns true if this authentication plugin can edit the users'profile.
     *
     * @return bool
     */
    public function can_edit_profile() {
        return true;
    }

    /**
     * Returns true if this authentication plugin is "internal".
     *
     * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal() {
        return true;
    }

    /**
     * Indicates if password hashes should be stored in local moodle database.
     *
     * @return bool True means password hash stored in user table, false means flag 'not_cached' stored there instead.
     */
    public function prevent_local_passwords() {
        return false;
    }

    /**
     * Indicates if moodle should automatically update internal user
     * records with data from external sources using the information
     * from get_userinfo() method.
     *
     * @return bool True means automatically copy data from ext to user table.
     */
    public function is_synchronised_with_external() {
        return false;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool.
     */
    public function can_reset_password() {
        return true;
    }

    /**
     * Returns true if plugin allows signup and user creation.
     *
     * @return bool
     */
    public function can_signup() {
        return true;
    }

    /**
     * Returns true if plugin allows confirming of new users.
     *
     * @return bool
     */
    public function can_confirm() {
        return true;
    }

    /**
     * Returns whether or not this authentication plugin can be manually set
     * for users, for example, when bulk uploading users.
     *
     * This should be overriden by authentication plugins where setting the
     * authentication method manually is allowed.
     *
     * @return bool
     */
    public function can_be_manually_set() {
        return true;
    }

    /**
     * Processes and stores configuration data for the plugin.
     *
     * @param stdClass $config Object with submitted configuration settings (without system magic quotes).
     * @return bool True if the configuration was processed successfully.
     */
    public function process_config($config) {
        return true;
    }

    /**
     * Captcha is always enabled
     *
     * @return bool|void
     */
    public function is_captcha_enabled() {
        return true;
    }

    /**
     * Return a form to capture user details for account creation.
     * This is used in /login/signup.php.
     *
     * @return \moodleform A form which edits a record from the user table.
     */
    public function signup_form() {
        return new \auth_psup\form\psup_signup_form(
            null,
            null,
            'post',
            '',
            array('autocomplete' => 'on')
        );
    }

    /**
     * Sign up a new user ready for confirmation.
     * Password is passed in plaintext.
     *
     * @param object $user new user object
     * @param boolean $notify print notice with link and terminate
     * @return bool
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function user_signup($user, $notify = true) {
        global $CFG, $SESSION;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        require_once($CFG->dirroot . '/user/editlib.php');

        $plainpassword = $user->password;
        $user->password = hash_internal_user_password($user->password);
        if (empty($user->calendartype)) {
            $user->calendartype = $CFG->calendartype;
        }

        // TODO : check what we can set as default.
        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            if (empty($user->$field)) {
                $user->$field = get_string($field);
            }
        }
        $user->confirmed = 1; // Auto confirm user.
        $user->id = user_create_user($user, false, false);

        user_add_password_history($user->id, $plainpassword);

        // Save any custom profile field information.
        profile_save_data($user);

        // Save wantsurl against user's profile, so we can return them there upon confirmation.
        if (!empty($SESSION->wantsurl)) {
            set_user_preference(\auth_psup\utils::USER_PREFS_WANTS_URL, $SESSION->wantsurl, $user);
        }
        set_user_preference(\auth_psup\utils::USER_PREFS_EMAIL_CONFIRMED, '0', $user);

        // Trigger event.
        \core\event\user_created::create_from_userid($user->id)->trigger();
        if (!$user = get_complete_user_data('id', $user->id)) {
            throw new moodle_exception('cannotfinduser', '', '', $user->id);
        }
        complete_user_login($user);
        \core\session\manager::apply_concurrent_login_limit($user->id, session_id());

        $notificationmessage = get_string('emailconfirmsent', '', $user->email);
        if (!send_confirmation_email($user)) {
            $notificationmessage = get_string('auth_emailnoemail', 'auth_email');
        }

        $defaultroleid = get_config('auth_psup', 'defaultsystemrole');
        if (!empty($defaultroleid)) {
            role_assign($defaultroleid, $user->id, context_system::instance()->id);
        }
        if ($notify) {
            global $CFG, $PAGE, $OUTPUT;
            $emailconfirm = get_string('emailconfirm');
            $PAGE->navbar->add($emailconfirm);
            $PAGE->set_title($emailconfirm);
            $PAGE->set_heading($PAGE->course->fullname);
            echo $OUTPUT->header();
            notice($notificationmessage, "$CFG->wwwroot/index.php");
        } else {
            return true;
        }
    }

    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param object $user User table object
     * @param string $newpassword Plaintext password
     * @return boolean result
     * @throws dml_exception
     */
    public function user_update_password($user, $newpassword) {
        $user = get_complete_user_data('id', $user->id);
        // This will also update the stored hash to the latest algorithm
        // if the existing hash is using an out-of-date algorithm (or the
        // legacy md5 algorithm).
        return update_internal_user_password($user, $newpassword);
    }

    /**
     * Confirm the new user as registered.
     *
     * @param string $username
     * @param string $confirmsecret
     * @return int
     * @throws coding_exception
     * @throws dml_exception
     */
    public function user_confirm($username, $confirmsecret) {
        global $CFG;
        require_once($CFG->dirroot . '/user/lib.php');
        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            $emailconfirmed = get_user_preferences(\auth_psup\utils::USER_PREFS_EMAIL_CONFIRMED, false, $user);
            if ($user->auth != $this->authtype) {
                return AUTH_CONFIRM_ERROR;
            } else if ($user->secret == $confirmsecret && $emailconfirmed) {
                return AUTH_CONFIRM_ALREADY;

            } else if ($user->secret == $confirmsecret) {   // They have provided the secret key to confirm email.
                if ($wantsurl = get_user_preferences(\auth_psup\utils::USER_PREFS_WANTS_URL, false, $user)) {
                    global $SESSION;
                    // Ensure user gets returned to page they were trying to access before signing up.
                    $SESSION->wantsurl = $wantsurl;
                    unset_user_preference('auth_email_wantsurl', $user);
                }
                set_user_preference(\auth_psup\utils::USER_PREFS_EMAIL_CONFIRMED, '1', $user);
                return AUTH_CONFIRM_OK;
            }
            return AUTH_CONFIRM_FAIL; // Does not match.
        } else {
            return AUTH_CONFIRM_ERROR;
        }
    }
}
