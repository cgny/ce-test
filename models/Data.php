<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 1/23/22
 * Time: 9:53 AM
 */

namespace models;

class Data
{

    private $connection = false;

    protected
        $ticker = false, $date_from = false, $date_to = false, $max_days = 32;

    function __construct( $data )
    {
        $this->init($data);
    }

    function init( $data )
    {
        $user     = $data->username;
        $password = $data->password;

        if (!$this->auth($user, $password))
        {
            throw new \Exception('Authentication failed.');
        }

        if (isset($data->ticker))
        {
            $this->ticker = $this->connection->real_escape_string($data->ticker);
        }

        if (isset($data->date_from))
        {
            $this->date_from = $this->connection->real_escape_string($data->date_from);
        }
        else
        {
            $data->date_from = date("Y-m-d H:i:s", strtotime("-24 HOURS"));
        }

        if (isset($data->date_to))
        {
            $this->date_to = $this->connection->real_escape_string($data->date_to);
        }
        else
        {
            $data->date_to = date("Y-m-d H:i:s");
        }

        $origin = new \DateTime($this->date_from);
        $target = new \DateTime($this->date_to);
        $interval = $origin->diff($target);
        $dif = $interval->format('%R%a');

        if ($dif > $this->max_days)
        {
            throw new \Exception('Date range is more than 31 days');
        }
        if($dif < 0)
        {
            throw new \Exception('From date is greater than to date');
        }

    }

    function auth( $user, $password )
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

    function getCompanyData()
    {
        $w = sprintf(" ticker = '%s' ",$this->ticker);

        $query  = "SELECT * FROM companies  WHERE $w";
        $result = $this->connection->query($query);
        $r      = [];
        while ($obj = $result->fetch_object())
        {
            $r[] = $obj;
        }
        $result->free_result();
        $this->connection->close();
        return $r;
    }

    function show()
    {
        echo 1;
    }

    function getAllCompanies()
    {
        $result = $this->connection->query("SELECT * FROM companies ");
        $r      = [];
        while ($obj = $result->fetch_object())
        {
            $r[] = $obj;
        }
        $result->free_result();
        $this->connection->close();
        return $r;
    }

    function getCompanyHistory()
    {
        $dw    = sprintf(" AND d BETWEEN ('%s') AND ('%s') ", $this->date_from, $this->date_to);
        $tw    = sprintf(" JOIN companies ON (companies.company_id = historical.company_id) WHERE ticker = '%s' $dw ", $this->ticker);

        $query  = "SELECT * FROM historical $tw ";

        echo $query;
        $result = $this->connection->query($query);
        $r      = [];
        while ($obj = $result->fetch_object())
        {
            $r[] = $obj;
        }
        $result->free_result();
        $this->connection->close();
        return $r;
    }

    function getCompanyHighLow()
    {
        $dw = sprintf(" AND d BETWEEN ('%s') AND ('%s') ", $this->date_from, $this->date_to);
        $tw = sprintf(" JOIN companies ON (companies.company_id = historical.company_id) WHERE ticker = '%s' $dw ", $this->ticker);

        $query  = "SELECT MAX(high) as high, MIN(low) as low FROM historical $tw ";
        $result = $this->connection->query($query);
        $r      = [];
        while ($obj = $result->fetch_object())
        {
            $r[] = $obj;
        }
        $result->free_result();
        $this->connection->close();
        return $r;
    }

    function getCompanySupportResistanceAVG()
    {
        $dw = sprintf(" AND d BETWEEN ('%s') AND ('%s') ", $this->date_from, $this->date_to);
        $tw = sprintf(" JOIN companies ON (companies.company_id = historical.company_id) WHERE ticker = '%s' $dw  ", $this->ticker);

        $query  = "SELECT * FROM historical $tw ";
        $result = $this->connection->query($query);
        $r      = [];

        $history = [];
        while ($obj = $result->fetch_object())
        {
            if (!isset($history[date("Y-m-d", strtotime($obj->d))]))
            {
                $history[date("Y-m-d", strtotime($obj->d))] = [];
            }
            $history[date("Y-m-d", strtotime($obj->d))][] = $obj->open;
            $history[date("Y-m-d", strtotime($obj->d))][] = $obj->close;
        }
        $result->free_result();
        $this->connection->close();

        $sup_res = [];
        foreach ($history as $day => $data)
        {
            $last  = $history[$day][count($history[$day]) - 1];
            $low   = min($history[$day]);
            $high  = max($history[$day]);
            $open  = $history[$day][0];
            $close = end($history[$day]);

            if (!isset($sup_res[$day]))
            {
                $sup_res[$day] = [];
            }

            $calcRes             = $this->calculateResistance($low, $high, $last);
            $sup_res[$day]['r1'] = $calcRes[0];
            $sup_res[$day]['r2'] = $calcRes[1];
            $sup_res[$day]['r3'] = $calcRes[2];

            $calcSup             = $this->calculateSupport($low, $high, $last);
            $sup_res[$day]['s1'] = $calcSup[0];
            $sup_res[$day]['s2'] = $calcSup[1];
            $sup_res[$day]['s3'] = $calcSup[2];

            $sup_res[$day]['low']   = $low;
            $sup_res[$day]['high']  = $high;
            $sup_res[$day]['mean']  = ($low + $high) / 2;
            $sup_res[$day]['open']  = $open;
            $sup_res[$day]['close'] = $close;
        }

        return $sup_res;
    }

    function calculateResistance( $low, $high, $last )
    {
        $pp = ($high + $low + $last) / 3;
        $r1 = (2 * $pp) - $low;
        $r2 = $pp + ($high - $low);
        $r3 = $high + 2 * ($pp - $low);
        return [ $r1, $r2, $r3 ];

    }

    function calculateSupport( $low, $high, $last )
    {
        $pp = ($high + $low + $last) / 3;
        $s1 = (2 * $pp) - $high;
        $s2 = $pp - ($high - $low);
        $s3 = $low - 2 * ($high - $pp);
        return [ $s1, $s2, $s3 ];

    }

}