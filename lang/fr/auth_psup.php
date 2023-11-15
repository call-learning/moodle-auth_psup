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

$string['auth_description'] = 'La création d’un compte est réservée aux candidats voulant
 postuler pour le concours véto post-bac.
Des informations supplémentaires seront demandées après le remplissage du questionnaire de pré-orientation.';
$string['defaultsystemrole'] = 'Rôle à la création de l\'utilisateur';
$string['defaultsystemrole_desc'] = 'Rôle à la création de l\'utilisateur. Ceci est un rôle système assigné à l\'utilisateur.
 Par défaut aucun rôle n\'est assigné.';
$string['emaildesc'] = '<span>L\'email doit <b> impérativement </b> être le même que celui fourni à Parcoursup.<span>';
$string['generalsettings'] = 'Paramètres généraux';
$string['generalsettings_desc'] = 'Paramètres généraux';
$string['createuserandpass'] = 'Créer un compte sur {$a}';
$string['invalidpsupid'] = 'Identifiant Parcoursup invalide (un identifiant valide est constitué de maximum 8 chiffres sans aucune lettre).';
$string['missingpsupid'] = 'Identifiant Parcoursup manquant.';
$string['mustvalidateemail'] = 'Vous devez valider votre email. Veuillez suivre les instructions envoyées par email
lors de la création de votre compte.';
$string['resendconfirmation:title'] = 'Renvoi de l\'email de confirmation';
$string['pluginname'] = 'Authentification Parcoursup';
$string['psupid'] = 'Identifiant Parcoursup';
$string['psupid_desc'] = 'Parcoursup Identifier';
$string['psupiddesc'] = 'Assurez-vous que votre identifiant est valide';
$string['psupidregexp'] = 'Expression régulière pour valider les identifiants Parcoursup.';
$string['psupidregexp_desc'] = 'Expression régulière de validation pour les identifiants Parcoursup. Cela permet
de valider les identifiants Parcoursup.';
$string['currentsession'] = 'Parcoursup current session';
$string['currentsession_desc'] = 'Parcoursup current session. This is used to validate Parcoursup identifier for a session.';
$string['profile:psupid'] = 'Parcoursup ID';
$string['profile:psupsession'] = 'Parcoursup session';
$string['profile:psupsession_desc'] = 'Parcoursup session for User (2023, 2024, ...)';
$string['profile:psupcategory'] = 'Specific Parcoursup authentication User Fields';
$string['privacy:metadata'] = 'Le plugin d\'authentification PSup (Parcoursup) ne stocke pas d\'information personelle';
$string['userexists'] = 'Un utilisateur avec le même identifiant Parcoursup a déjà été créé.';
