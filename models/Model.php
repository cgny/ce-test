<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 6:10 PM
 */

namespace models;


class Model
{

    function getConnection($user, $password)
    {
        $connection = new Connection($user, $password);
        $conn = $connection->connect();
        if($conn)
        {
            $this->connection = $conn;
            return true;
        }
        else
        {
            return false;
        }
    }

}