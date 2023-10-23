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
 * Resend confirmation email
 *
 * @package     auth_psup
 * @copyright   2020 Laurent David - CALL Learning <laurent@call-learning.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
global $CFG, $OUTPUT, $PAGE, $USER;
require_login(null, false);

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_url('/auth/psup/resendconfirmation.php', ['returnurl' => $returnurl]);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('resendconfirmation:title', 'auth_psup'));
$PAGE->set_heading(get_string('resendconfirmation:title', 'auth_psup'));

echo $OUTPUT->header();
if (!send_confirmation_email($USER)) {
    echo $OUTPUT->notification(get_string('emailconfirmsentfailure'), \core\output\notification::NOTIFY_ERROR);
} else {
    echo $OUTPUT->notification(get_string('emailconfirmsentsuccess'), \core\output\notification::NOTIFY_SUCCESS);
}
echo $OUTPUT->single_button(new moodle_url($returnurl), get_string('continue'));
echo $OUTPUT->footer();
