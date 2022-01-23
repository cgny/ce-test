<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 9:43 AM
 */

namespace api;

class Api
{
    public $end_points = [];

    function __construct()
    {
        $this->loadEndPoints();
    }

    function readURI( $method, $uri, $data )
    {
        if (isset($this->end_points[$method][$uri]))
        {
            $end_point = $this->end_points[$method][$uri];
            return $this->getFunction($end_point, $data);
        }
        elseif (isset($this->end_points[$method]) && !isset($this->end_points[$method][$uri]))
        {
            throw new \ErrorException('No  request : /' . $uri . ' with method : ' . $method);
        }
        elseif (!isset($end_point['function']))
        {
            throw new \ErrorException('Incorrect URI : ' . $method);
        }
        elseif (!isset($this->end_points[$method]))
        {
            throw new \ErrorException('Incorrect method : ' . $method);
        }
        return false;
    }

    function getFunction( $end_point, $data )
    {
        $class    = $end_point['class'];
        $method   = $end_point['function'];
        $required = $end_point['required'];
        foreach ($required as $req)
        {
            if (!isset($data->$req))
            {
                throw new \ErrorException("Required field: $req is not present");
            }
        }

        if (method_exists($class, $method))
        {
            $c = new $class($data);
            return $c->$method($data);
        }
        return false;
    }

    function loadEndPoints( $return = false )
    {
        if (is_file('routes/api.php'))
        {
            $this->end_points = include 'routes/api.php';
        }
        if ($return)
        {
            $keys = array_keys($this->end_points);
            array_flip($keys);
            $uris = [];
            foreach ($keys as $key)
            {
                $uris[$key] = array_keys($this->end_points[$key]);
                $all[$key]  = [];
                array_walk($uris[$key], function ( $array ) use ( $key, &$all )
                {
                    $q           = [
                        'endpoint' => $array,
                        'args'     => $this->end_points[$key][$array]['args']
                    ];
                    $all[$key][] = $q;
                }
                );
            }
            return $all;
        }
    }

    function getUserData()
    {
        $session = new Session();
        return [
            'requests'       => $session->getRequsts(),
            'requestBalance' => $session->getRequestsBalance(),
            'throttle'       => $session->getThrottleLimit(),
            'timeLimit'      => $session->getTimeLimit(),
            'timer'          => $session->getTimeLeft(),
        ];
    }

}