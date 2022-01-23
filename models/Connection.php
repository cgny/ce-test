<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 5:24 PM
 */

namespace models;

class Connection
{
    private $config, $user, $password;

    function __construct( $user, $password )
    {
        $this->user     = $user;
        $this->password = $password;
    }

    function connect()
    {
        $this->config = include 'config/database.php';
        $connection   = new \mysqli($this->config['host'], sprintf("%s", $this->user), sprintf("%s", $this->password), $this->config['db_name'], $this->config['port']);
        if ($connection->connect_errno)
        {
            throw new \ErrorException("Failed to connect : " . $connection->connect_error);
        }
        else
        {
            return $connection;
        }
    }
}