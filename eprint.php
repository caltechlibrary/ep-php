<?php
/**
 * eprints.php - this privides a light weight middleware for accessing an EPrints based repository
 * from etd-workflow.php. EPrints REST API returns XML objects, this middleware managed the
 * authenticated connection and transforms the XML into JSON for each of used in the browser
 * via JavaScript.
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

// setArrayValue is recursive so needs to be a separate function to descend
function setArrayValue(&$array, $stack, $value) {
    if ($stack) {
        $key = array_shift($stack);
        setArrayValue($array[$key], $stack, $value);
        return $array;
    } else {
        $array = $value;
    }
}

// Code based on example at http://php.net/manual/en/function.xml-parse-into-struct.php by Alf Marius Foss Olsen
function xmlToArray($xml) {
    $parser = xml_parser_create(); 
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
    xml_parse_into_struct($parser, $xml, $values);
    xml_parser_free($parser);

    $result = array();
    $stack = array();
    foreach($values as $val) {
        if($val['type'] == "open") {
            array_push($stack, $val['tag']);
        } elseif($val['type'] == "close") {
            array_pop($stack);
        } elseif($val['type'] == "complete") {
            array_push($stack, $val['tag']);
            setArrayValue($result, $stack, $val['value']);
            array_pop($stack);
        }
    }
    return $result;
}

// httpGET perform a basic HTTP GET and return the document
function httpGET($url, $user = "", $password = "") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($user && $password) {
        curl_setopt($ch, CURLOPT_USERPWD, urlencode($user) . ':' . urlencode($password));
    } else if ($user) {
        curl_setopt($ch, CURLOPT_USERPWD, urlencode($user));
    }
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

// getEPrintXML fetches an EPrint record for a given id and return an XML formatted document
function getEPrintXML($eprint_url, $eprint_id, $eprint_user = "", $eprint_password = "") {
    return httpGET($eprint_url . "/rest/eprint/$eprint_id.xml", $eprint_user, $eprint_password);
}

// getEPrintJSON fetches an EPrint record for a given id and return an JSON formatted document
function getEPrintJSON($eprint_url, $eprint_id, $eprint_user = "", $eprint_password = "", $pretty_print = true) {
    $src = getEPrintXML($eprint_url, $eprint_id, $eprint_user, $eprint_password);
    if ($src && stripos($src, "<?xml") !== false) {
        if ($pretty_print == true) {
            return json_encode(xmlToArray($src), JSON_PRETTY_PRINT);
        } else {
            return json_encode(xmlToArray($src));
        }
    }
    return '{}';
}
?>
