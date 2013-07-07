<?php
/**
* Sticky Notes pastebin
* @ver 0.4
* @license BSD License - www.opensource.org/licenses/bsd-license.php
*
* Copyright (c) 2013 Sayak Banerjee <mail@sayakbanerjee.com>
* All rights reserved. Do not remove this copyright notice.
*/

class db
{
    // Class wide variables
    var $pdo;
    var $prefix;
    var $insert_id;
    var $affected_rows;

    // Function to initialize a db connection
    function connect()
    {
        global $gsod, $config, $core;

        try
        {
            if ($config->db_port)
            {
                $server = "{$config->db_host}:{$config->db_port}";
            }
            else
            {
                $server = $config->db_host;
            }

            // Set the DB prefix
            $this->prefix = $config->db_prefix;

            // Build the connection string
            switch ($config->db_type)
            {
                case 'mysql':
                case 'pgsql':
                    $this->pdo = new PDO(
                        "{$config->db_type}:host={$server}; dbname={$config->db_name}",
                        $config->db_username,
                        $config->db_password
                    );
                    break;

                case 'mssql':
                case 'sybase':
                    $this->pdo = new PDO(
                        "{$config->db_type}:host={$server}; dbname={$config->db_name}," .
                        "{$config->db_username}, {$config->db_password}"
                    );
                    break;

                case 'sqlite':
                    $this->pdo = new PDO("{$config->db_type}:{$config->db_name}");
                    break;
            }

            $this->pdo->exec("SET NAMES 'utf8'");
        }
        catch (PDOException $e)
        {
            $message  = '<b>Sticky Notes DB error</b><br /><br />';
            $message .= 'Error: ' . $e->getMessage();
            $gsod->trigger($message);
        }
    }

    // Function to return a recordset
    function query($sql, $params = array(), $single = false)
    {
        global $gsod;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $sql = strtolower($sql);

        if ($stmt !== false)
        {
            if (strpos($sql, 'select') === 0 || strpos($sql, 'show') === 0)
            {
                if ($single)
                {
                    return $stmt->fetch(PDO::FETCH_ASSOC);
                }
                else
                {
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }
            else if (strpos($sql, 'insert') === 0)
            {
                $this->insert_id = $stmt;
            }
            else if (strpos($sql, 'update') === 0 || strpos($sql, 'delete') === 0)
            {
                $this->affected_rows = $stmt->rowCount();
            }

            return true;
        }
        else
        {
            return false;
        }
    }

    // Gets the database size
    function get_size()
    {
        $rows = $this->query('SHOW TABLE STATUS');
        $size = 0;

        foreach($rows as $row)
        {
            $size += intval($row["Data_length"]) + intval($row["Index_length"]);
        }

        return $size;
    }
}

?>