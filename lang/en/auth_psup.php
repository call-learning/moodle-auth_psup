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
 * Plugin strings are defined here.
 *
 * @package     auth_psup
 * @category    string
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['auth_description'] = 'Parcoursup Authentification - allow user to be created at first, able to access
a course and then confirmed';
$string['generalsettings'] = 'General settings';
$string['generalsettings_desc'] = 'General settings';
$string['createuserandpass'] = 'Create an account on {$a}';

$string['emaildesc'] = 'The email should be the same as Parcoursup email.';
$string['invalidpsupid'] = 'Invalid Parcoursup Identifier.';
$string['missingpsupid'] = 'Missing parcoursup Identifier.';
$string['mustvalidateemail'] = 'You should validate your email. Please follow instruction sent to you by email when you
created your account.';
$string['resendconfirmation:title'] = 'Resend confirmation email';
$string['psupid'] = 'Parcoursup Identifier';
$string['psupid_desc'] = 'Parcoursup Identifier';

$string['psupiddesc'] = 'Make sure that your identifier is valid';
$string['psupidregexp'] = 'Parcoursup Identifier validation pattern.';
$string['psupidregexp_desc'] = 'Parcoursup Identifier validation pattern (regular expression). We match
this as valid Parcoursup identifier.';
$string['pluginname'] = 'Parcoursup Authentication';
$string['privacy:metadata'] = 'The Parcoursup authentication plugin does not store any personal data.';


$string['userexists'] = 'A user with the same Parcoursup ID has already registered.';

