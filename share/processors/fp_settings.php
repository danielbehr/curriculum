<?php
/** This file is part of curriculum - http://www.joachimdieterich.de
* FormProcessor
* @package core
* @filename fp_settings.php
* @copyright 2017 Joachim Dieterich
* @author Joachim Dieterich
* @date 2017.01.30 09:26
* @license: 
*
* The MIT License (MIT)
* Copyright (c) 2012 Joachim Dieterich http://www.curriculumonline.de
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), 
* to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
* and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
* MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
* DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR 
* THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
include(dirname(__FILE__).'/../setup.php');  // Klassen, DB Zugriff und Funktionen
include(dirname(__FILE__).'/../login-check.php');  //check login status and reset idletimer
global $USER, $CFG;
$USER            = $_SESSION['USER'];
if (!isset($_SESSION['PAGE']->target_url)){     //if target_url is not set -> use last PAGE url
    $_SESSION['PAGE']->target_url       = $_SESSION['PAGE']->url;
}

$config = new Config(); 

$gump              = new Gump();    /* Validation */
$_POST             = $gump->sanitize($_POST);       //sanitize $_POST
$gump->validation_rules(array(
'template'         => 'required'
));

$validated_data  = $gump->run($_POST);
if($validated_data === false) {/* validation failed */
    $_SESSION['FORM']            = new stdClass();
    $_SESSION['FORM']->form      = 'settings';
    foreach($_POST as $key => $value){                                         
        $_SESSION['FORM']->$key  = $value;
    }
    $_SESSION['FORM']->error     = $gump->get_readable_errors();
    $_SESSION['FORM']->func      = $_POST['func']; 
} else {
    switch (isset($_POST)) {
        case isset($_POST['user_template_save']):   $context              = new Context();
                                                    $context->resolve('context', 'userFiles');
                                                    $config->name         = 'template';
                                                    $config->value        = filter_input(INPUT_POST, 'template',    FILTER_UNSAFE_RAW);
                                                    $config->context_id   = $context->context_id;
                                                    $config->reference_id = $USER->id;

                                                    $config->set();
        
            break;
        default:
            break;
    }
    $content = new Content();
    if (isset($_POST['global_terms_save'])){    // edit AGBs
        $content->get('terms');
        $purify           = HTMLPurifier_Config::createDefault();
        $purify->set('Core.Encoding', 'UTF-8'); // replace with your encoding
        $purify->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
        $purifier         = new HTMLPurifier($purify);
        $content->content = $purifier->purify(filter_input(INPUT_POST, 'global_terms', FILTER_UNSAFE_RAW)); //replace with new version
        if (checkCapabilities('user:userListComplete', $USER->role_id, false)){ //== superadmin
            $content->update();
        }
    }
    
    if (isset($_POST['user_signature_save'])){    // edit signature
        $content->get('signature', $USER->id);
        
        $purify           = HTMLPurifier_Config::createDefault();
        $purify->set('Core.Encoding', 'UTF-8'); // replace with your encoding
        $purify->set('HTML.Doctype', 'HTML 4.01 Transitional'); // replace with your doctype
        $purifier         = new HTMLPurifier($purify);
        $content->content = $purifier->purify(filter_input(INPUT_POST, 'user_signature', FILTER_UNSAFE_RAW)); //replace with new version
        if (checkCapabilities('user:update', $USER->role_id, false)){ 
            if (isset($content->id)){
                $content->update();
            } else {
                $content->title         = 'Signatur '.$USER->lastname;
                $co                     = new Context();
                $co->resolve('context', 'signature');
                $content->context_id    = $co->context_id;
                $content->file_context  = '1'; //global
                $content->reference_id  = $USER->id;
                $content->add();
            }
        }
    }
    $_SESSION['FORM']            = null;                     // reset Session Form object 
}

header('Location:'.$_SESSION['PAGE']->target_url);