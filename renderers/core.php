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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_bootstrap
 * @copyright  2012
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class theme_bootstrap_core_renderer extends core_renderer {

    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-error';
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

    public function navbar() {
        $items = $this->page->navbar->get_items();
        foreach ($items as $item) {
            $item->hideicon = true;
                $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $list_items = '<li>'.join(" $divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }
    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }


    /*
     * This renders the bootstrap top menu
     *
     * This renderer is needed to enable the Bootstrap style navigation
     *
     */

    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $addlangmenu = true;
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
            or empty($CFG->langmenu)
            or $this->page->course != SITEID and !empty($this->page->course->lang)) {
            $addlangmenu = false;
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        if ($addlangmenu) {
            $language = $menu->add(get_string('language'), new moodle_url('#'), get_string('language'), 999);
            foreach ($langs as $langtype => $langname) {
                $language->add($langname,
                new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '<ul class="nav">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }
        return $content.'</ul>';
    }

    /*
     * This code renders the custom menu items for the
     * bootstrap dropdown menu
     */

    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0 ) {
        static $submenucount = 0;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $dropdowntype = 'dropdown';
            } else {
                $dropdowntype = 'dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array('class'=>$dropdowntype));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::start_tag('a', array('href'=>$url, 'class'=>'dropdown-toggle', 'data-toggle'=>'dropdown'));
            $content .= $menunode->get_title();
            if ($level == 1) {
                $content .= '<b class="caret"></b>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title'=>$menunode->get_title()));
        }
        return $content;
    }


    /**
     * Renders tabs
     *
     * This function replaces print_tabs() used before Moodle 2.5 but with slightly different arguments
     *
     * @param array $tabs array of tabs, each of them may have it's own ->subtree
     * @param string|null $selected which tab to mark as selected, the parent tab will
     *     automatically be marked as selected too
     * @param array|string|null $inactive list of ids of inactive tabs
     * @return string
     */
    public function tabtree($tabs, $selected = null, $inactive = array()) {
        $lis = array();
        if (!is_array($inactive)) {
            $inactive = array($inactive);
        }
        $subtree = '';
        foreach ($tabs as $tab) {
            if ($this->child_tab_selected($tab, $selected)) {
                $lis[] = $this->active_tab($tab->text);
                $subtree = $this->tabtree($tab->subtree, $selected, $inactive);
            } else if ($tab->id === $selected) {
                $lis[] = $this->active_tab($tab->text);
            } else if (in_array($tab->id, $inactive)) {
                $lis[] = $this->disabled_tab($tab->text);
            } else {
                $lis[] = $this->tab($tab->text, $tab->link);
            }
        }
        return $this->tab_row(implode($lis)) . $subtree;
    }

    public function child_tab_selected($tab, $selected) {
        if (!isset($tab->subtree)) {
            return false;
        }
        foreach ($tab->subtree as $subtab) {
            if ($subtab->id === $selected) {
                return true;
            }
        }
        return false;
    }

    public function tab_row($content) {
        return "<ul class=\"nav nav-tabs\">$content</ul>";
    }

    public function active_tab($text, $subtree = '') {
        return "<li class=\"active\"><a>$text</a>$subtree</li>";
    }

    public function disabled_tab($text) {
        return "<li class=\"disabled\"><a>$text</a></li>";
    }

    public function tab($text, $href) {
        return "<li><a href=\"$href\">$text</a></li>";
    }
}
