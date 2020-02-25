<?php

// Show SQL for debug
define("debug_enable", true);
if (debug_enable) {
    @ini_set("display_errors", "1");
    error_reporting(E_ALL ^ E_NOTICE);
}

session_start();

// db data
define("host", "localhost");
define("user", "root");
define("pass", "vertrigo");
define("base", "digitalya");

$mysqli = new mysqli(host, user, pass, base);

if (mysqli_connect_errno()) {     printf("Connect failed: %s\n", mysqli_connect_error()); exit(); }

function mq($q) { global $mysqli; return $mysqli->query($q); }
function mfa($q) { return $q->fetch_assoc(); }
function mnr($q) { return $q->num_rows; }
function mres($q) { global $mysqli; return $mysqli->real_escape_string($q); }
function mlastid() { global $mysqli; return $mysqli->insert_id; }
function merror() { global $mysqli; return $mysqli->error;}
function mGetAll($q) {	$result = array(); 	while ($a = mfa($q)) { $result[] = $a; } return $result; }
function mScalar($q) { return mq($q)->fetch_row()[0]; }
function mRow($q) { return mq($q)->fetch_assoc(); }

if (!empty($_POST)) {
    $post = [];
    foreach ($_POST as $k => $v) {
        if (!is_array($v)) {
            $post[$k] = mres(trim($v));
        }else{
            $post[$k] = $v;
        }
    }
}

if (!empty($_GET)) {
    $get = [];
    foreach ($_GET as $k => $v) {
        if (!is_array($v)) {
            $get[$k] = mres(trim($v));
        }else{
            $get[$k] = $v;
        }
    }
}

$relationships = ['grandmother' => 2, 'grandfather' => 2, 'mother' => 1, 'father' => 1, 'son' => 0, 'daughter' => 0];

function printName($member, $showActions=false) {
	$res = "$member[firstName] $member[middleName] $member[lastName]";
	if ($showActions) {
		$res .= " <a href='?edit=$member[id]'>Editeaza</a> / <a href='#' onclick=\"if(confirm('Sigur?'))location.href='?delete=$member[id]'\">Sterge</a>"; 
	}
	return $res;
}