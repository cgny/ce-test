<?php
session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST,GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 9:34 AM
 */

include('autoload.php');

$session = new \api\Session();
$session->request();

$post = $data           = (object) $_POST;
$data           = (object) $_GET;

$data->username = $_SERVER['PHP_AUTH_USER'] ?? "";
$data->password = $_SERVER['PHP_AUTH_PW'] ?? "";

$host   = $_SERVER['HTTP_HOST'] ?? "";
$method = $_SERVER['REQUEST_METHOD'] ?? "";
$uri    = $_SERVER['REQUEST_URI'] ?? "";

$base       = "";
$function   = "";
$url_params = [];
$parse      = parse_url($uri);

if ($uri)
{
    $uri_sep   = explode("?", $uri);
    $uri_parts = explode("/", $uri_sep[0]);
    $base      = $uri_parts[1] ?? "";
    $function  = $uri_parts[2] ?? "";
}

$req = [
    'host'     => $host,
    'uri'      => $parse['path'],
    'method'   => $method,
    'base'     => $base,
    'function' => $function,
    'query'    => $parse['query'] ?? "",
    'data'     => $post
];

$obj = "";
if ($base == "api")
{
    $api      = new \api\Api();
    $userData = $api->getUserData();
    $obj = new stdClass();

    //$obj = $api->readURI($method, $function, $data);

    try
    {
        $obj = $api->readURI($method, $function, $data);
        $success = true;
        $log = "OK";
        $msg = false;
    }
    catch (ErrorException $e)
    {
        $success = false;
        $log = "ERROR";
        $msg = $e->getMessage();
    }

    $resp = json_encode([
        'server'  => $req,
        'user'    => $userData,
        'data'    => $obj,
        'success' => $success,
        'msg'     => $msg
    ]);

    echo $resp;
    error_log($log.': '. $resp);
}