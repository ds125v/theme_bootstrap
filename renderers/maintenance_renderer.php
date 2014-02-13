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

class theme_bootstrap_core_renderer_maintenance extends core_renderer_maintenance {

    /*
     * This renders a notification message.
     * Uses bootstrap compatible html.
     */
    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-danger';
        }
        if ($classes == 'notifywarning') {
            $type = 'alert alert-warning';
        }
        if ($classes == 'notifysuccess') {
            $type = 'alert alert-success';
        }
        if ($classes == 'notifymessage') {
            $type = 'alert alert-info';
        }
        if ($classes == 'redirectmessage') {
            $type = 'alert alert-block alert-info';
        }
        return "<div class=\"$type\">$message</div>";
    }

   /**
    * Print a message along with button choices for Continue/Cancel
    *
    * If a string or moodle_url is given instead of a single_button, method defaults to post.
    *
    * @param string $message The question to ask the user
    * @param single_button|moodle_url|string $continue The single_button component representing the Continue answer. Can also be a moodle_url or string URL
    * @param single_button|moodle_url|string $cancel The single_button component representing the Cancel answer. Can also be a moodle_url or string URL
    * @return string HTML fragment
    */
    public function confirm($message, $continue, $cancel) {
        if ($continue instanceof single_button) {
            // ok
        } else if (is_string($continue)) {
            $continue = new single_button(new moodle_url($continue), get_string('continue'), 'post');
        } else if ($continue instanceof moodle_url) {
            $continue = new single_button($continue, get_string('continue'), 'post');
        } else {
            throw new coding_exception('The continue param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        if ($cancel instanceof single_button) {
            // ok
        } else if (is_string($cancel)) {
            $cancel = new single_button(new moodle_url($cancel), get_string('cancel'), 'get');
        } else if ($cancel instanceof moodle_url) {
            $cancel = new single_button($cancel, get_string('cancel'), 'get');
        } else {
            throw new coding_exception('The cancel param to $OUTPUT->confirm() must be either a URL (string/moodle_url) or a single_button instance.');
        }

        $output = $this->box_start('panel panel-default');
        $output .= html_writer::tag('div', $message, array('class'=>'panel-body'));
        $output .= html_writer::tag('div', $this->render_continue_cancel($continue, $cancel), array('class' => 'panel-footer'));
        $output .= $this->box_end();
        return $output;
    }
    protected function render_continue_cancel(single_button $continue, single_button $cancel) {
        $attributes = array('type'     => 'submit',
                            'value'    => $continue->label,
                            'class'  => 'btn btn-primary',
                            'disabled' => $continue->disabled ? 'disabled' : null,
                            'title'    => $continue->tooltip);

        if ($continue->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($continue->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }

        // first the input element
        $output = html_writer::empty_tag('input', $attributes);

        // then hidden fields
        $params = $continue->url->params();
        if ($continue->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

        // now the form itself around it
        if ($continue->method === 'get') {
            $url = $continue->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $continue->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }
        if ($url === '') {
            $url = '#'; // there has to be always some action
        }
        $attributes = array('method' => $continue->method,
                            'action' => $url,
                            'id'     => $continue->formid);
        $output = html_writer::tag('form', $output, $attributes);

        $attributes = array('type'     => 'submit',
                            'value'    => $cancel->label,
                            'class'  => 'btn btn-cancel',
                            'disabled' => $cancel->disabled ? 'disabled' : null,
                            'title'    => $cancel->tooltip);

        if ($cancel->actions) {
            $id = html_writer::random_id('single_button');
            $attributes['id'] = $id;
            foreach ($cancel->actions as $action) {
                $this->add_action_handler($action, $id);
            }
        }

        $output .= html_writer::empty_tag('input', $attributes);

        // then hidden fields
        $params = $cancel->url->params();
        if ($cancel->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $var => $val) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $var, 'value' => $val));
        }

        // now the form itself around it
        if ($cancel->method === 'get') {
            $url = $cancel->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $cancel->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }
        if ($url === '') {
            $url = '#'; // there has to be always some action
        }
        $attributes = array('method' => $cancel->method,
                            'action' => $url,
                            'id'     => $cancel->formid);
        $output = html_writer::tag('form', $output, $attributes);

        return $output;
    }
}
