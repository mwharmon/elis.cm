<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2012 Remote Learner.net Inc http://www.remote-learner.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    elis
 * @subpackage curriculummanagement
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008-2012 Remote Learner.net Inc http://www.remote-learner.net
 *
 */

define('CM_CERTIFICATE_CODE_LENGTH', 15);

/**
 * Outputs a certificate for some sort of completion element
 *
 * @param  string  $person_fullname  The full name of the certificate recipient
 * @param  string  $entity_name      The name of the entity that is compelted
 * @param  string  $certificatecode  The unique certificate code
 * @param  string  $date_string      Date /time the certification was achieved
 * @param  string  $curriculum_frequency the curriculum frequency
 * @param  string  $expirydate       A string representing the time that the
 * certificate expires (optional).
 */
function certificate_output_completion($person_fullname, $entity_name, $certificatecode = '', $date_string,
                                       $expirydate = '', $curriculum_frequency = '', $border = '',
                                       $seal = '', $template = '') {
    global $CFG, $COURSE;

    //use the TCPDF library
    require_once($CFG->libdir .'/pdflib.php');

    //global settings
    $borders = 0;
    $font = 'FreeSerif';
    $large_font_size = 30;
    $small_font_size = 16;

    //create pdf
    $pdf = new pdf('L', 'in', 'Letter');

    //prevent the pdf from printing black bars
    $pdf->print_header = false;
    $pdf->print_footer = false;

    //add main (only) page
    $pdf->AddPage();

    //draw the border
    cm_certificate_check_data_path('borders');
    if (file_exists($CFG->dirroot . '/curriculum/pix/certificate/borders/' . $border)) {
        $pdf->Image($CFG->dirroot . '/curriculum/pix/certificate/borders/' . $border, 0.25, 0.25, 10.5, 8.0);
    } else if (file_exists($CFG->dataroot . '/' . $COURSE->id . '/curriculum/pix/certificate/borders/' . $border)) {
        $pdf->Image($CFG->dataroot . '/' . $COURSE->id . '/curriculum/pix/certificate/borders/' . $border, 0.25, 0.25, 10.5, 8.0);
    }

    //draw the seal
    cm_certificate_check_data_path('seals');
    if (file_exists($CFG->dirroot . '/curriculum/pix/certificate/seals/' . $seal)) {
        $pdf->Image($CFG->dirroot . '/curriculum/pix/certificate/seals/' . $seal, 8.0, 5.8);
    } else if (file_exists($CFG->dataroot . '/' . $COURSE->id . '/curriculum/pix/certificate/seals/' . $seal)) {
        $pdf->Image($CFG->dataroot . '/' . $COURSE->id . '/curriculum/pix/certificate/seals/' . $seal, 8.0, 5.8);
    }

    //include template

    cm_certificate_check_data_path('templates');

    if (file_exists($CFG->dirroot . '/curriculum/pix/certificate/templates/' . $template)) {
        include($CFG->dirroot . '/curriculum/pix/certificate/templates/' . $template);
    } else if (file_exists($CFG->dataroot . '/' . $COURSE->id . '/curriculum/pix/certificate/templates/' . $template)) {
        include($CFG->dirroot . '/curriculum/pix/certificate/templates/' . $template);
    }


    $pdf->Output();
}

function cm_certificate_get_borders() {
    global $CFG, $COURSE;

    // Add default images
    $my_path = "{$CFG->dirroot}/curriculum/pix/certificate/borders";
    $borderstyleoptions = array();
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.png',1)||strpos($file, '.jpg',1) ) {
                $i = strpos($file, '.');
                if ($i > 1) {
                    $borderstyleoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Add custom images
    cm_certificate_check_data_path('borders');
    $my_path = "{$CFG->dataroot}/{$COURSE->id}/curriculum/pix/certificate/borders";
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.png',1)||strpos($file, '.jpg',1) ) {
                $i = strpos($file, '.');
                if ($i > 1) {
                    $borderstyleoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Sort borders
    ksort($borderstyleoptions);

    // Add no border option
    $borderstyleoptions['none'] = get_string('none');

    return $borderstyleoptions;
}

function cm_certificate_get_seals() {
    global $CFG, $COURSE;

    // Add default images
    $my_path = "{$CFG->dirroot}/curriculum/pix/certificate/seals";
    $sealoptions = array();
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.png',1)||strpos($file, '.jpg',1) ) {
                $i = strpos($file, '.');
                if($i > 1) {
                    $sealoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Add custom images
    cm_certificate_check_data_path('seals');
    $my_path = "{$CFG->dataroot}/{$COURSE->id}/curriculum/pix/certificate/seals";
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.png',1)||strpos($file, '.jpg',1) ) {
                $i = strpos($file, '.');
                if($i > 1) {
                    $sealoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Sort seals
    ksort($sealoptions);

    // Add no seal option
    $sealoptions['none'] = get_string('none');

    return $sealoptions;
}

function cm_certificate_check_data_path($imagetype) {
    global $CFG, $COURSE;

    $path_array = array($COURSE->id, 'curriculum', 'pix', 'certificate', $imagetype);
    $full_path = $CFG->dataroot;

    foreach ($path_array as $path) {
        $full_path .= '/' . $path;
        if (!file_exists($full_path)) {
            mkdir($full_path);
        }
    }
}

function cm_certificate_get_templates() {
    global $CFG, $COURSE;

    // Add default templates
    $my_path = "{$CFG->dirroot}/curriculum/pix/certificate/templates";
    $templateoptions = array();
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.php',1)) {
                $i = strpos($file, '.');
                if($i > 1) {
                    $templateoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Add custom images
    cm_certificate_check_data_path('templates');
    $my_path = "{$CFG->dataroot}/{$COURSE->id}/curriculum/pix/certificate/templates";
    if (file_exists($my_path) && $handle = opendir($my_path)) {
        while (false !== ($file = readdir($handle))) {
            if (strpos($file, '.php',1) ) {
                $i = strpos($file, '.');
                if($i > 1) {
                    $templateoptions[$file] = substr($file, 0, $i);
                }
            }
        }
        closedir($handle);
    }

    // Sort templates
    ksort($templateoptions);


    return $templateoptions;

}

/**
 * This function returns a random string of numbers and characters.
 * The standard length of the string is CM_CERTIFICATE_CODE_LENGTH
 * characters.  Pass a parameter to append more characters to the
 * standard CM_CERTIFICATE_CODE_LENGTH characters
 *
 * @param int $append - The number of characters to append to the standard
 * length of CM_CERTIFICATE_CODE_LENGTH
 */
function cm_certificate_generate_code($append = 0) {

    $size = CM_CERTIFICATE_CODE_LENGTH + intval($append);
    $code = random_string($size);

    return $code;
}

/**
 * This function sends a message to the development team indicating that
 * the maximum number of attempts to generate a random string has been
 * exhausted
 */

function cm_certificate_email_random_number_fail($tableobj = null) {

    global $CFG;

    if (empty($tableobj)) {
        return false;
    }

    require_once($CFG->dirroot . '/message/lib.php');

    //construct the message
    $a = new stdClass;
    $a->sitename = get_field('course', 'fullname', 'id', SITEID);
    $a->url = $CFG->wwwroot;

    $message_text = get_string('certificate_code_fail', 'block_curr_admin', $a) . "\n\n";
    $message_text .= get_string('certificate_code_fail_text', 'block_curr_admin') . "\n";
    $message_text .= get_string('certificate_code_fail_text_data', 'block_curr_admin', $tableobj) . "\n";

    $message_html = nl2br($message_text);

    //send message to rladmin user if possible
    if($rladmin_user = get_record('user', 'username', 'rladmin')) {
        $result = message_post_message($rladmin_user, $rladmin_user, addslashes($message_html), FORMAT_HTML, 'direct');

        if($result === false) {
            return $result;
        }
    }

    //email to specified address
    $user_obj = new stdClass;
    $user_obj->email = CURR_ADMIN_DUPLICATE_EMAIL;
    $user_obj->mailformat = FORMAT_HTML;
    email_to_user($user_obj, get_admin(), get_string('certificate_code_fail', 'block_curr_admin', $a), $message_text, $message_html);

    //output to screen if possible
    if(!empty($output_to_screen)) {
        echo $message_html;
    }

    return true;

}

?>
