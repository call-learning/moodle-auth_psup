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
use core\session\manager;
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

    /**
     * Settings and flags for a newly created user
     */
    const USER_PREFS_EMAIL_CONFIRMED = 'auth_psup_emailconfirmed';
    /**
     * Settings and flags for a newly created user
     */
    const USER_PREFS_WANTS_URL = 'auth_psup_wantsurl';
    /**
     * Settings and user profile field to store parcoursup id for a newly created user
     */
    const AUTH_PSUP_USERNAME_FIELD = 'psupid';
    /**
     * Settings and user profile fields to store session for user (2023, 2024...)
     */
    const AUTH_PSUP_SESSION_FIELD = 'psupsession';

    /**
     * Add info to field
     *
     * @param object $mform
     * @param string $fieldname
     * @param string $desc
     */
    public static function add_info_to_field(&$mform, $fieldname, $desc) {
        /** @var core_renderer $icon */
        $icon = html_writer::tag('i',
            '',
            [
                'class' => 'icon fa fa-exclamation-triangle text-warning fa-fw',
                'title' => '',
            ]
        );
        $labelfor = \html_writer::label(
            $icon . $desc,
            $fieldname,
            true,
            ['class' => 'psup-additional-description']);
        $mform->addElement('static', $fieldname . 'desc', '', $labelfor);
    }

    /**
     * Validate parcoursup identifier
     *
     * @param array $data
     * @param array $files
     */
    public static function validate_psup_identifier($data, $files) {
        $errors = [];
        $user = self::get_user_with_psupid_and_session($data['psupid'], 'psup');
        if (!empty($user)) {
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
     * Get users with same psupid and session
     * @param string $psupid
     * @param string $authtype
     */
    public static function get_user_with_psupid_and_session(string $psupid, string $authtype) {
        global $DB, $CFG;
        $currentsession = get_config('auth_psup', 'currentsession');
        $user = $DB->get_record_sql('SELECT DISTINCT u.* FROM {user} u
                LEFT JOIN {user_info_data} uidataid ON u.id = uidataid.userid
                LEFT JOIN {user_info_field} uifieldid ON uifieldid.id = uidataid.fieldid
                    AND uifieldid.shortname = :psupidfieldname
                LEFT JOIN {user_info_data} uidatasession ON u.id = uidatasession.userid
                LEFT JOIN {user_info_field} uifieldsession ON uifieldsession.id = uidatasession.fieldid
                    AND uifieldsession.shortname = :psupsessionfieldname
                WHERE uidataid.data = :psupid
                    AND uidatasession.data = :currentsession
                    AND uifieldsession.id IS NOT NULL
                    AND uifieldid.id IS NOT NULL
                    AND u.mnethostid = :mnethostid
                    AND u.auth = :auth',
            [
                'psupidfieldname' => self::AUTH_PSUP_USERNAME_FIELD,
                'psupsessionfieldname' => self::AUTH_PSUP_SESSION_FIELD,
                'psupid' => $psupid,
                'currentsession' => $currentsession,
                'mnethostid' => $CFG->mnet_localhost_id,
                'auth' => $authtype,
            ]);
        return $user;
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
     * @param object $user
     */
    public static function display_cta_send_new_email($user) {
        if ($user->auth == 'psup'
            && signup_get_user_confirmation_authplugin()
            && empty(get_user_preferences(static::USER_PREFS_EMAIL_CONFIRMED, false, $user))) {
            $actions = [
                [
                    'title' => get_string('emailconfirmationresend'),
                    'url' => new moodle_url('/auth/psup/resendconfirmation.php',
                        ['returnurl' => qualified_me()]),
                    'data' => [],
                ],
            ];
            $icon = [
                'pix' => 'i/warning',
                'component' => 'core',
            ];

            notification::add_call_to_action($icon,
                get_string('mustvalidateemail', 'auth_psup'),
                $actions
            );
        }
    }

    /**
     *  Get the right label for username (either username or parcoursupid)
     *
     * @param int $userid
     * @return string
     */
    public static function get_username_label(int $userid): string {
        global $USER;
        $iscurrentuser = $userid == $USER->id && !manager::is_loggedinas();

        if ((!isloggedin() || $iscurrentuser) && $userid) {
            $currentuser = core_user::get_user($userid);
            if ($currentuser && $currentuser->auth == 'psup') {
                return get_string('psupid', 'auth_psup');
            }
        }
        return get_string('username');
    }

    /**
     * Create user profile field for the auth module
     */
    public static function create_user_fields_auth_psup() {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');
        static::create_or_update_user_profile_field([
            'shortname' => static::AUTH_PSUP_USERNAME_FIELD,
            'name' => get_string('profile:psupid', 'auth_psup'),
            'datatype' => 'text',
            'signup' => 0,
            'visible' => PROFILE_VISIBLE_NONE,
            'required' => 0,
            'category' => get_string('profile:psupcategory', 'auth_psup'),
        ]);
        static::create_or_update_user_profile_field([
            'shortname' => static::AUTH_PSUP_SESSION_FIELD,
            'name' => get_string('profile:psupsession', 'auth_psup'),
            'datatype' => 'text',
            'signup' => 0,
            'visible' => PROFILE_VISIBLE_NONE,
            'required' => 0,
            'category' => get_string('profile:psupcategory', 'auth_psup'),
        ]);
    }

    /**
     * Create user profile field (taken from core code generator)
     *
     * @param array $data example [ 'shortname' => 'superfield', 'name' => 'Super field',
     * 'datatype' => 'text', 'signup' => 1, 'visible' => 1, 'required' => 1, 'sortorder' => 1]
     * @return object
     * @throws \moodle_exception
     */
    protected static function create_or_update_user_profile_field(array $data): object {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/user/profile/lib.php');

        // Set up category if necessary.
        $categoryid = null;
        if (isset($data['category'])) {
            $categoryid = $DB->get_field('user_info_category', 'id',
                ['name' => $data['category']]);
            if (!$categoryid) {
                $created = static::create_custom_profile_field_category(['name' => $data['category']]);
                $categoryid = $created->id;
            }
        } else {
            $categoryid = $data['categoryid'] ?? null;
        }
        if (empty($categoryid)) {
            throw new \moodle_exception('categoryidmustexist', 'auth_psup');
        }
        $data['categoryid'] = $categoryid;
        // Pick sort order if necessary.
        if (!array_key_exists('sortorder', $data)) {
            $data['sortorder'] = (int) $DB->get_field_sql(
                    'SELECT MAX(sortorder) FROM {user_info_field} WHERE categoryid = ?',
                    [$data['categoryid']]) + 1;
        }

        if ($data['datatype'] === 'menu' && isset($data['param1'])) {
            // Convert new lines to the proper character.
            $data['param1'] = str_replace('\n', "\n", $data['param1']);
        }

        // Defaults for other values.
        $defaults = [
            'description' => '',
            'descriptionformat' => 0,
            'required' => 0,
            'locked' => 0,
            'visible' => PROFILE_VISIBLE_ALL,
            'forceunique' => 0,
            'signup' => 0,
            'defaultdata' => '',
            'defaultdataformat' => 0,
            'param1' => '',
            'param2' => '',
            'param3' => '',
            'param4' => '',
            'param5' => '',
        ];

        // Type-specific defaults for other values.
        $typedefaults = [
            'text' => [
                'param1' => 30,
                'param2' => 2048,
            ],
            'menu' => [
                'param1' => "Yes\nNo",
                'defaultdata' => 'No',
            ],
            'datetime' => [
                'param1' => '2010',
                'param2' => '2015',
                'param3' => 1,
            ],
            'checkbox' => [
                'defaultdata' => 0,
            ],
        ];
        foreach ($typedefaults[$data['datatype']] ?? [] as $field => $value) {
            $defaults[$field] = $value;
        }

        foreach ($defaults as $field => $value) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = $value;
            }
        }

        if ($existingrecord = $DB->get_record('user_info_field', ['shortname' => $data['shortname']])) {
            $data['id'] = $existingrecord->id;
            $DB->update_record('user_info_field', (object) $data);
        } else {
            $data['id'] = $DB->insert_record('user_info_field', $data);
        }
        return (object) $data;
    }

    /**
     * Utility method to create a custom profile field category. Taken from the core code (generator).
     *
     * @param array $data
     * @return \stdClass
     */
    protected static function create_custom_profile_field_category(array $data): \stdClass {
        global $DB;

        // Pick next sortorder if not defined.
        if (!array_key_exists('sortorder', $data)) {
            $data['sortorder'] = (int) $DB->get_field_sql('SELECT MAX(sortorder) FROM {user_info_category}') + 1;
        }

        $category = (object) [
            'name' => $data['name'],
            'sortorder' => $data['sortorder'],
        ];
        $category->id = $DB->insert_record('user_info_category', $category);

        return $category;
    }

    /**
     * Create user info data
     *
     * @param int $userid
     * @param string $psupid
     * @param string $currentsession
     * @return void
     */
    public static function create_user_info_data(int $userid, string $psupid, string $currentsession) {
        static $psupid = null;
        static $psupsessionid = null;
        global $DB;
        if (empty($psupid)) {
            $psupfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => self::AUTH_PSUP_SESSION_FIELD]);
        }
        if (empty($psupsessionid)) {
            $psupsessionfieldid = $DB->get_field('user_info_field', 'id', ['shortname' => self::AUTH_PSUP_SESSION_FIELD]);
        }
        foreach ([$psupfieldid => $psupid, $psupsessionfieldid => $currentsession] as $fieldid => $data) {
            $existingfield = $DB->get_record('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid]);
            if (!$existingfield) {
                $DB->insert_record('user_info_data', [
                    'userid' => $userid,
                    'fieldid' => $fieldid,
                    'data' => $data,
                    'dataformat' => 0,
                ]);
            } else {
                $existingfield->data = $data;
                $DB->update_record('user_info_data', $existingfield);
            }
        }
    }

    /**
     * Get user info data
     *
     * @param int $userid
     * @param string $field
     * @return false|mixed
     * @throws \dml_exception
     */
    public static function get_user_info_data(int $userid, string $field) {
        global $DB;
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => $field]);
        $data = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => $fieldid]);
        return $data;
    }
}
