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
namespace auth_psup\form;

use auth_psup\utils;
use core_user;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/editlib.php');
/**
 * User sign-up form for Psup.
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class psup_signup_form extends \moodleform implements \renderable, \templatable {
    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Fields needed for form validation.
        $mform->addElement('hidden', 'username');
        $mform->setType('username', PARAM_RAW);
        $mform->addElement('hidden', 'email2');
        $mform->setType('email2', core_user::get_property_type('email'));

        $namefields = useredit_get_required_name_fields();
        foreach ($namefields as $field) {
            $mform->addElement('text', $field, get_string($field), 'maxlength="100" size="30"');
            $mform->setType($field, core_user::get_property_type('firstname'));
            $stringid = 'missing' . $field;
            if (!get_string_manager()->string_exists($stringid, 'moodle')) {
                $stringid = 'required';
            }
            $mform->addRule($field, get_string($stringid), 'required', null, 'client');
        }
        $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="25"');
        $mform->setType('email', core_user::get_property_type('email'));
        $mform->addRule('email', get_string('missingemail'), 'required', null, 'client');
        $mform->setForceLtr('email');
        utils::add_info_to_field($mform, 'email', get_string('emaildesc', 'auth_psup'));

        $mform->addElement('text', 'psupid', get_string('psupid', 'auth_psup'), 'maxlength="100" size="12" autocapitalize="none"');
        $mform->setType('psupid', PARAM_RAW);
        $mform->addRule('psupid', get_string('missingpsupid', 'auth_psup'), 'required', null, 'client');
        utils::add_info_to_field($mform, 'email', get_string('psupiddesc', 'auth_psup'));

        $mform->addElement('passwordunmask', 'password', get_string('password'), 'maxlength="32" size="12"');
        $mform->setType('password', core_user::get_property_type('password'));
        $mform->addRule('password', get_string('missingpassword'), 'required', null, 'client');
        if (!empty($CFG->passwordpolicy)) {
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }

        $mform->addElement('header', 'supplyinfo', get_string('supplyinfo'), '');

        $mform->addElement('text', 'city', get_string('city'), 'maxlength="120" size="20"');
        $mform->setType('city', core_user::get_property_type('city'));
        if (!empty($CFG->defaultcity)) {
            $mform->setDefault('city', $CFG->defaultcity);
        }

        $country = get_string_manager()->get_list_of_countries();
        $defaultcountry[''] = get_string('selectacountry');
        $country = array_merge($defaultcountry, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);

        if (!empty($CFG->country)) {
            $mform->setDefault('country', $CFG->country);
        } else {
            $mform->setDefault('country', '');
        }

        profile_signup_fields($mform);

        if (signup_captcha_enabled()) {
            $mform->addElement('recaptcha', 'recaptcha_element', get_string('security_question', 'auth'));
            $mform->addHelpButton('recaptcha_element', 'recaptcha', 'auth');
            $mform->closeHeaderBefore('recaptcha_element');
        }

        // Hook for plugins to extend form definition.
        core_login_extend_signup_form($mform);

        // Add "Agree to sitepolicy" controls. By default it is a link to the policy text and a checkbox but
        // it can be implemented differently in custom sitepolicy handlers.
        $manager = new \core_privacy\local\sitepolicy\manager();
        $manager->signup_form($mform);

        // Button.
        $this->add_action_buttons(true, get_string('createaccount'));

    }

    /**
     * Setup form with data
     */
    public function definition_after_data() {
        $mform = $this->_form;
        $mform->applyFilter('psupid', 'trim');
        $mform->setConstant('username', $mform->getSubmitValue('psupid'));
        $mform->setConstant('email2', $mform->getSubmitValue('email'));

        // Trim required name fields.
        foreach (useredit_get_required_name_fields() as $field) {
            $mform->applyFilter($field, 'trim');
        }
    }

    /**
     * Validate user supplied data on the signup form.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     * @throws \coding_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Extend validation for any form extensions from plugins.
        $errors = array_merge($errors, core_login_validate_extend_signup_form($data));

        if (signup_captcha_enabled()) {
            $recaptchaelement = $this->_form->getElement('recaptcha_element');
            if (!empty($this->_form->_submitValues['g-recaptcha-response'])) {
                $response = $this->_form->_submitValues['g-recaptcha-response'];
                if (!$recaptchaelement->verify($response)) {
                    $errors['recaptcha_element'] = get_string('incorrectpleasetryagain', 'auth');
                }
            } else {
                $errors['recaptcha_element'] = get_string('missingrecaptchachallengefield');
            }
        }

        // Validate Parcoursup Identifier.
        $errors += utils::validate_psup_identifier($data, $files);
        $errors += signup_validate_data($data, $files);

        return $errors;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        ob_start();
        $this->display();
        $formhtml = ob_get_contents();
        ob_end_clean();
        $site = get_site();
        $context = [
            'formhtml' => $formhtml,
            'sitename' => format_string($site->fullname)
        ];
        return $context;
    }
}
