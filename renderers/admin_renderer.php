<?php
// This file is part of The Bootstrap 3 Moodle theme
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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_bootstrap
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . "/admin/renderer.php");

class theme_bootstrap_core_admin_renderer extends core_admin_renderer {

    protected function maturity_info($maturity) {
        if ($maturity == MATURITY_STABLE) {
            return ''; // No worries.
        }

        $notify_level = 'notifywarning';

        if ($maturity == MATURITY_ALPHA) {
            $notify_level = 'notifyproblem';
        }

        $maturitylevel = get_string('maturity' . $maturity, 'admin');
        $warningtext = get_string('maturitycoreinfo', 'admin', $maturitylevel);
        $doc_link = $this->doc_link('admin/versions', get_string('morehelp'));

        return $this->notification($warningtext . ' ' . $doc_link, $notify_level);
    }
    /**
     * Output a warning message, of the type that appears on the admin notifications page.
     * @param string $message the message to display.
     * @param string $type type class
     * @return string HTML to output.
     */
    protected function warning($message, $type = 'warning') {
        if ($type == 'warning') {
            return $this->notification($message, 'notifywarning');
        } else if ($type == 'error') {
            return $this->notification($message, 'notifyproblem');
        }
    }
    protected function available_updates($updates, $fetch) {

        $updateinfo = $this->box_start('alert alert-info');
        $someupdateavailable = false;
        if (is_array($updates)) {
            if (is_array($updates['core'])) {
                $someupdateavailable = true;
                $updateinfo .= $this->heading(get_string('updateavailable', 'core_admin'), 4);
                foreach ($updates['core'] as $update) {
                    $updateinfo .= $this->moodle_available_update_info($update);
                }
            }
            unset($updates['core']);
            // If something has left in the $updates array now, it is updates for plugins.
            if (!empty($updates)) {
                $someupdateavailable = true;
                $updateinfo .= $this->heading(get_string('updateavailableforplugin', 'core_admin'), 4);
                $pluginsoverviewurl = new moodle_url('/admin/plugins.php', array('updatesonly' => 1));
                $updateinfo .= $this->container(get_string('pluginsoverviewsee', 'core_admin',
                    array('url' => $pluginsoverviewurl->out())));
            }
        }

        if (!$someupdateavailable) {
            $now = time();
            if ($fetch and ($fetch <= $now) and ($now - $fetch < HOURSECS)) {
                $updateinfo .= $this->heading(get_string('updateavailablenot', 'core_admin'), 4);
            }
        }

        $updateinfo .= $this->container_start('checkforupdates');
        $fetchurl = new moodle_url('/admin/index.php', array('fetchupdates' => 1, 'sesskey' => sesskey(), 'cache' => 0));
        $updateinfo .= $this->single_button($fetchurl, get_string('checkforupdates', 'core_plugin'));
        if ($fetch) {
            $updateinfo .= $this->container(get_string('checkforupdateslast', 'core_plugin',
                userdate($fetch, get_string('strftimedatetime', 'core_langconfig'))));
        }
        $updateinfo .= $this->container_end();

        $updateinfo .= $this->box_end();

        return $updateinfo;
    }
    protected function test_site_warning($testsite) {

        if (!$testsite) {
            return '';
        }

        return $this->notification(get_string('testsiteupgradewarning', 'admin', $testsite), 'notifyproblem');
    }
    protected function maturity_warning($maturity) {
        if ($maturity == MATURITY_STABLE) {
            return ''; // No worries.
        }

        $maturitylevel = get_string('maturity' . $maturity, 'admin');
        $maturity_warning = get_string('maturitycorewarning', 'admin', $maturitylevel);
        $maturity_warning .= $this->doc_link('admin/versions', get_string('morehelp'));

        return $this->notification($maturity_warning, 'notifyproblem');
    }
    protected function release_notes_link() {
        $releasenoteslink = get_string('releasenoteslink', 'admin', 'http://docs.moodle.org/dev/Releases');
        return $this->notification($releasenoteslink, 'notifymessage');
    }
}
