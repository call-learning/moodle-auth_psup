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
 * File containing tests for test_psup_auth.php.
 *
 * @package     auth_psup
 * @category    test
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace auth_psup;
use advanced_testcase;
use auth_plugin_psup;
use auth_psup\utils;
use context_system;
use core_user;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/auth/psup/auth.php');
require_once($CFG->dirroot . '/user/editlib.php');

/**
 * The test_psup_auth.php test class.
 *
 * @package    auth_psup
 * @copyright  2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_test extends advanced_testcase {

    /**
     * @var auth_plugin_psup|null $authplugin
     */
    protected $authplugin = null;
    /**
     * @var stdClass|null $userdata
     */
    protected $userdata = null;

    /**
     * @var int $newroleid
     */
    protected $newroleid = 0;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp() {
        $this->resetAfterTest();
        set_config('registerauth', 'psup');
        $this->authplugin = new auth_plugin_psup();
        $userdata = (object) [
            'username' => '12345678',
            'email' => 'email@example.com',
            'password' => 'n@wPassw0%rd',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname'
        ];
        $this->newroleid = $this->getDataGenerator()->create_role(['name' => 'New role',
            'shortname' => 'newrole']);
        $this->userdata = signup_setup_new_user($userdata); // Complete user profile.
    }

    /**
     * Test identifier checks
     *
     * @return void
     */
    public function test_utils_psup_identifier() {
        $this->resetAfterTest();
        $this->assertTrue(utils::is_valid_psup_identifier('1234556')); // Ok.
        $this->assertFalse(utils::is_valid_psup_identifier('123456789')); // Too long.
        $this->assertFalse(utils::is_valid_psup_identifier('12345')); // Too short.
        $this->assertTrue(utils::is_valid_psup_identifier('000000')); // Same repetitive letter, we let it go.
        $this->assertTrue(utils::is_valid_psup_identifier('111111')); // Same repetitive letter, we let it go too.
        $this->assertFalse(utils::is_valid_psup_identifier('p120210')); // A letter.
        set_config('psupidregexp', '/^.*$/', 'auth_psup');
        $this->assertTrue(utils::is_valid_psup_identifier('1a20210')); // We allowed anything.
    }

    /**
     * Test that an email is sent
     *
     * @return void
     */
    public function test_create_send_email() {
        $emailsink = $this->redirectEmails();
        @$this->authplugin->user_signup($this->userdata, false);
        $this->assertEquals(1, $emailsink->count());
        $messages = $emailsink->get_messages();
        $this->assertEquals('noreply@www.example.com', $messages[0]->from);
        $this->assertEquals('email@example.com', $messages[0]->to);
        $user = core_user::get_user_by_email('email@example.com');
        $this->assertEquals('12345678', $user->username);
        $this->assertEquals('Firstname', $user->firstname);
        $this->assertEquals('Lastname', $user->lastname);
    }

    /**
     * Test that event is created
     *
     * @return void
     */
    public function test_create_event() {
        $eventsink = $this->redirectEvents();
        @$this->authplugin->user_signup($this->userdata, false);
        $events = $eventsink->get_events();
        $this->assertEquals(2, $eventsink->count());
        $this->assertEquals('\core\event\user_created', $events[0]->get_data()['eventname']);
        $this->assertEquals('\core\event\user_loggedin', $events[1]->get_data()['eventname']);
        $this->assertEquals(['username' => '12345678'], $events[1]->get_data()['other']);
    }

    /**
     * Test that email confirmation is working
     *
     * @return void
     */
    public function test_confirm_email() {
        @$this->authplugin->user_signup($this->userdata, false);
        $user = core_user::get_user_by_email('email@example.com');
        $this->assertEquals('0', get_user_preferences(utils::USER_PREFS_EMAIL_CONFIRMED, null, $user));
        $this->assertEquals(AUTH_CONFIRM_FAIL, $this->authplugin->user_confirm($this->userdata->username, '123345'));
        $this->assertEquals('0', get_user_preferences(utils::USER_PREFS_EMAIL_CONFIRMED, null, $user));
        $this->assertEquals(AUTH_CONFIRM_ERROR, $this->authplugin->user_confirm('AZERTY', '123345'));
        $this->assertEquals('0', get_user_preferences(utils::USER_PREFS_EMAIL_CONFIRMED, null, $user));
        $this->assertEquals(AUTH_CONFIRM_OK, $this->authplugin->user_confirm($this->userdata->username, $user->secret));
        unset($user->preference);// This is a hack so we get the value from db instead of cache. The undelying issue
        // is that if we run it too fast, the cache is not refreshed.
        check_user_preferences_loaded($user); // If not this will fail as it is the previous value that is returned.
        $emailconfirmed = get_user_preferences(utils::USER_PREFS_EMAIL_CONFIRMED, null, $user);
        $this->assertEquals('1', $emailconfirmed);
    }

    /**
     * Test that role is assigned
     *
     * @param string $roleshortname
     * @dataProvider role_assigned_data
     */
    public function test_role_assigned($roleshortname) {
        global $DB;
        $roleid = 0;
        if (!empty($roleshortname)) {
            $roleid = $DB->get_field('role', 'id', array('shortname' => $roleshortname));
        }
        set_config('defaultsystemrole', $roleid, 'auth_psup');
        $context = context_system::instance();
        @$this->authplugin->user_signup($this->userdata, false);
        $user = core_user::get_user_by_email('email@example.com');
        $roles = array_map(function($r) {
            return $r->shortname;
        }, get_user_roles($context, $user->id));
        if ($roleid) {
            $this->assertContains($roleshortname, $roles);
        } else {
            $this->assertEmpty($roles);
        }
    }

    /**
     * Data for test_role_assigned test
     *
     * @return string[][]
     */
    public function role_assigned_data() {
        return [
            'no role is assigned' => [''],
            'manager is assigned' => ['manager'],
            'newrole is assigned' => ['newrole'],
        ];
    }
}


