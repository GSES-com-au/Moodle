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
 *
 * @package    block_crisp_chat
 * @copyright  2022 Elizabeth Joannou
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 class block_crisp_chat extends block_base {
    public function init() {
        $this->title = get_string('crisp_chat', 'block_crisp_chat');
    }
    public function get_content() {
        if ($this->content !== null) return $this->content;
        global $USER, $PAGE, $COURSE;
        $this->content = new stdClass;
        if (!isloggedin()) {
            return $this->content;
        } else {
            //javascript provided by crisp
            $this->content->text = '<script>window.$crisp=[];window.CRISP_WEBSITE_ID="e0e9e32b-244c-4043-889e-d92a5d095998";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>';
            //hides drawer-right if block_crisp_chat is the only block. ".block_crisp_chat" must also be set to display:none.
            $this->content->text .= '<script>document.addEventListener("DOMContentLoaded", () => {var secondchild = document.getElementById("block-region-side-pre").children[1]; if (!secondchild && document.getElementsByClassName("block_crisp_chat")) {document.querySelector(".drawer-right-toggle").style.display = "none";document.getElementById("page").classList.remove("show-drawer-right");document.getElementById("theme_boost-drawers-blocks").classList.remove("show");document.getElementById("theme_boost-drawers-blocks").style.display = "none";}});</script>';
            $this->content->text .= '<script>window.onload = function() {if(document.getElementById("theme_boost-drawers-blocks").style.display  == "none"){document.getElementById("theme_boost-drawers-blocks").classList.remove("show");document.getElementById("page").classList.remove("show-drawer-right");}}</script>';
            //send initial picker selection to crisp
            $this->content->text .= '<script>$crisp.push(["on", "message:received", (message) => { if (message.content.id == "initialpicker" && message.content.choices[0].selected) { $crisp.push(["do", "message:send", ["text", "Course administration"]]); $crisp.push(["off", "message:received"]);} else if (message.content.id == "initialpicker" && message.content.choices[1].selected) { $crisp.push(["do", "message:send", ["text", "Technical question"]]); $crisp.push(["off", "message:received"]);}}])</script>;';
            //send course name to crisp
            if ($COURSE->fullname) {
                $this->content->text .= '<script>$crisp.push(["set", "session:data", ["course_name", "';
                $this->content->text .=  $COURSE->fullname;
                $this->content->text .= '"]]);</script>';
            }
            //send current module name to crisp
            $this->content->text .= '<script>window.onload = function() {var currentcm = document.querySelector(\'a[href*="mod"]\'); if(currentcm){$crisp.push(["set", "session:data", ["current_module", ';
            $this->content->text .=  'currentcm.innerHTML';
            $this->content->text .= ']]);}}</script>';
            //initatechat function
            $this->content->text .= '<script>function initiatechat() { if ($crisp.is("session:ongoing") == false) {$crisp.push(["set", "user:email", "';
            $this->content->text .= $USER->email;
            $this->content->text .= '"]); $crisp.push(["set", "user:nickname", "';
            $this->content->text .= $USER->firstname;
            $this->content->text .= ' ';
            $this->content->text .= $USER->lastname;
            $this->content->text .= '"]); $crisp.push(["do", "message:show", ["picker", { "id": "initialpicker", "text": "Do you have a question about course admin, or a technical question?", "choices": [{ "value": "admin", "label": "Course administration", "selected": false }, { "value": "technical", "label": "Technical question", "selected": false }]}]]);}}</script>';
            $this->content->text .= '<script>$crisp.push(["on", "chat:opened", initiatechat]);</script>';
            return $this->content;
        }
    }
    public function hide_header() {
        return true;
    }
    public function applicable_formats() {
        return array(
           'all' => true,
        );
    }
}