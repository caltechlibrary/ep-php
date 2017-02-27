<?php
/**
 * eprint_test.php - this is a test script to make sure the EPrints Middleware API functions work.
 *
 * @author R. S. Doiel, <rsdoiel@library.caltech.edu>
 * 
 * Copyright (c) 2017, Caltech
 * All rights not granted herein are expressly reserved by Caltech.
 * 
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *  
 */

include_once "eprint.php";
include_once "config.php";

// Decide if we're running the tests from command line or as a webservice
if (php_sapi_name() === "cli") {
    if (count($argv) > 1 ) {
        $eprint_id = $argv[1];
    } else {
        die("USAGE $argv[0] EPRINT_ID" . PHP_EOL);
    }

    // $Tests is an array holding test functions to run.
    // If the function returns a result other than OK it is is
    $Tests = [
    ];
        
    $Tests["Prerequisites"] = function () {
        $depends_on = [
            "xml_parser_create" => "php7.0-xml module",
            "curl_init" => "php7.0-curl",
        ];
        $missing_list = [];
        foreach ($depends_on as $func => $mod) {
            if (function_exists($func) === false) {
                $mod_list[] = $mod;
            }
        }
        if (count($missing_list) > 0) {
            return "Failed, missing modules " . implode(", ", $missing_list);
        }
        return "";
    };
    
    if ($_get["debug"]) {
        $Tests["ShowEPrint"] = function() {
        	global $eprint_id;
        	global $EPRINT_URL;
        	global $EPRINT_USER;
        	global $EPRINT_PASSWORD;
    
        	$res = getEPrintXML($EPRINT_URL, $eprint_id, $EPRINT_USER, $EPRINT_PASSWORD);
    
        	if ($eprint_id && strlen($res) === 0) {
            	return 'Failed, expected EPrint JSON data for EPrint ID "' . $eprint_id . '"'; 
        	}
    	return '<pre>' . str_replace(["<", ">"], ["&lt;", "&gt;"],$res) . '</pre>';
        };
    }
    
    $Tests["TestEPrintResultXML"] = function() {
        global $eprint_id;
        global $EPRINT_URL;
        global $EPRINT_USER;
        global $EPRINT_PASSWORD;
    
        $xml_response = getEPrintXML($EPRINT_URL, $eprint_id, $EPRINT_USER, $EPRINT_PASSWORD);
    
        if ($eprint_id && strlen($xml_response) === 0) {
            return 'Failed, expected EPrint XML data for EPrint ID "' . $eprint_id . '"'; 
        }
        return "";
    };
    
    $Tests["TestEPrintResultJSON"] = function() {
        global $eprint_id;
        global $EPRINT_URL;
        global $EPRINT_USER;
        global $EPRINT_PASSWORD;
    
        $json_response = getEPrintJSON($EPRINT_URL, $eprint_id, $EPRINT_USER, $EPRINT_PASSWORD);
        if ($eprint_id && strlen($json_response) === 0) {
            return 'Failed, expected EPrint JSON data for EPrint ID "' . $eprint_id . '"'; 
        }
        $rec = json_decode($json_response, true);
        if (! isset($rec['eprints'])) {
        	return 'Missing eprints array response <pre>' . $json_response; //print_r($rec, true);
        }
        return "";
    };
    
    
    if (php_sapi_name() != "cli") {
        print("<pre>");
    }
    $err_count = 0;
    $pass_count = 0;
    foreach ($Tests as $name => $func) {
        $err = $func();
        if ($err) {
            $err_count += 1;
            print("$name $err" . PHP_EOL);
        } else {
            $pass_count += 1;
        }
    }
    if ($err_count > 0) {
        die(PHP_EOL . "Passed $pass_count tests, FAILED $err_count" . PHP_EOL);
    } else {
        die(PHP_EOL. "Passed $pass_count tests" . PHP_EOL . "OK" . PHP_EOL);
    }
} else {
    die("eprint_test.php is a command line PHP script");
}
?>
