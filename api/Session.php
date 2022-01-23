<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 2:18 PM
 */

namespace api;

class Session{

    private $throttle_limit = 50; //per min

    function __construct()
    {
        if(!isset($_SESSION['start_time']))
        {
            $_SESSION['start_time'] = date('Y-m-d H:i');
            $_SESSION['requests'] = 0;
        }
        $this->renewRequests();
    }

    function request()
    {
        if($_SESSION['requests'] == $this->throttle_limit)
        {
            throw new \Exception('You have reached your API request limit');
        }
        $_SESSION['requests']++;
    }

    function renewRequests()
    {
        if($_SESSION['start_time'] != date('Y-m-d H:i'))
        {
            $_SESSION['start_time'] = date('Y-m-d H:i');
            $_SESSION['requests'] = 0;
        }
    }

    function getRequsts()
    {
        return $_SESSION['requests'];
    }

    function getRequestsBalance()
    {
        return ($this->throttle_limit - $_SESSION['requests']);
    }

    function getThrottleLimit()
    {
        return $this->throttle_limit;
    }

}