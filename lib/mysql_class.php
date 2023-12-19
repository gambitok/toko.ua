<?php

class dbm
{

    /**
     * @var mysqli
     */
    private $db;
    private $rowsCount = 0;
    private $result;

    private $host;
    private $dbname;
    private $username;
    private $password;

    private function load_auth_param()
    {
        $this->host = 'localhost';
        $this->dbname = 'myparts_dba';
        $this->username = 'toko_usr';
        $this->password = 'T0k0U$er183729Pa$$#';
    }

    public function connect()
    {
        $this->load_auth_param();
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);

        mysqli_set_charset($this->db, 'cp1251');
        mysqli_query($this->db, "SET NAMES 'cp1251' COLLATE 'cp1251_general_ci'");
        mysqli_query($this->db, "SET CHARACTER SET 'cp1251' COLLATE 'cp1251_general_ci'");
    }

    public function close()
    {
        mysqli_close($this->db);
    }

    public function num_rows($result)
    {
        $this->rowsCount = mysqli_num_rows($result);

        return $this->rowsCount;
    }

    public function query($query)
    {
        $this->result = mysqli_query($this->db, $query);

        if (mysqli_error($this->db) != "") {
            print mysqli_error($this->db) . "<br>query=" . $query;
        }

        return $this->result;
    }

    public function result($result, $number, $field_name)
    {
        $rowsCount = mysqli_num_rows($result);
        if ($rowsCount && $number <= ($rowsCount - 1) && $number >= 0) {
            mysqli_data_seek($result, $number);
            $resrow = is_numeric($field_name) ? mysqli_fetch_row($result) : mysqli_fetch_assoc($result);
            if (isset($resrow[$field_name])) {
                return $resrow[$field_name];
            }
        }

        return false;
    }

    public function getDbName()
    {
        return $this->dbname;
    }

    public function clear_param($value)
    {
        $value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
        return mysqli_real_escape_string($this->db, $value);
    }

}

class db
{

    /**
     * @var mysqli
     */
    private $db;
    private $rowsCount = 0;
    private $result;

    private $host;
    private $dbname;
    private $username;
    private $password;

    private function load_auth_param()
    {
        $this->host = 'localhost';
        $this->dbname = 'toko_dba';
        $this->username = 'toko_usr';
        $this->password = 'T0k0U$er183729Pa$$#';
    }

    public function connect()
    {
        $this->load_auth_param();
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);

        mysqli_set_charset($this->db, 'cp1251');
        mysqli_query($this->db, "SET NAMES 'cp1251' COLLATE 'cp1251_general_ci'");
        mysqli_query($this->db, "SET CHARACTER SET 'cp1251' COLLATE 'cp1251_general_ci'");
    }

    public function close()
    {
        mysqli_close($this->db);
    }

    public function num_rows($result)
    {
        $this->rowsCount = mysqli_num_rows($result);

        return $this->rowsCount;
    }

    public function query($query)
    {
        $this->result = mysqli_query($this->db, $query);

        if (mysqli_error($this->db) != "") {
            echo mysqli_error($this->db) . "<br>query=" . $query;
        }

        return $this->result;
    }

    public function result($result, $number, $field_name)
    {
        $rowsCount = mysqli_num_rows($result);
        if ($rowsCount && $number <= ($rowsCount - 1) && $number >= 0) {
            mysqli_data_seek($result, $number);
            $resrow = is_numeric($field_name) ? mysqli_fetch_row($result) : mysqli_fetch_assoc($result);
            if (isset($resrow[$field_name])) {
                return $resrow[$field_name];
            }
        }

        return false;
    }

    public function getDbName()
    {
        return $this->dbname;
    }

    public function clear_param($value)
    {
        $value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
        return mysqli_real_escape_string($this->db, $value);
    }

}

class dbc
{

    /**
     * @var mysqli
     */
    private $db;
    private $rowsCount = 0;
    private $result;

    private $host;
    private $dbname;
    private $username;
    private $password;

    private function load_auth_param()
    {
        $this->host = 'localhost';
        $this->dbname = 'toko_dba_cache';
        $this->username = 'toko_usr';
        $this->password = 'T0k0U$er183729Pa$$#';
    }

    public function connect()
    {
        $this->load_auth_param();
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);

        mysqli_set_charset($this->db, 'cp1251');
        mysqli_query($this->db, "SET NAMES 'cp1251' COLLATE 'cp1251_general_ci'");
        mysqli_query($this->db, "SET CHARACTER SET 'cp1251' COLLATE 'cp1251_general_ci'");
    }

    public function close()
    {
        mysqli_close($this->db);
    }

    public function num_rows($result)
    {
        $this->rowsCount = mysqli_num_rows($result);

        return $this->rowsCount;
    }

    public function query($query)
    {
        $this->result = mysqli_query($this->db, $query);

        if (mysqli_error($this->db) != "") {
            echo mysqli_error($this->db) . "<br>query=" . $query;
        }

        return $this->result;
    }

    public function result($result, $number, $field_name)
    {
        $rowsCount = mysqli_num_rows($result);
        if ($rowsCount && $number <= ($rowsCount - 1) && $number >= 0) {
            mysqli_data_seek($result, $number);
            $resrow = is_numeric($field_name) ? mysqli_fetch_row($result) : mysqli_fetch_assoc($result);
            if (isset($resrow[$field_name])) {
                return $resrow[$field_name];
            }
        }

        return false;
    }

    public function getDbName()
    {
        return $this->dbname;
    }

    public function clear_param($value)
    {
        $value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
        return mysqli_real_escape_string($this->db, $value);
    }

}

class dbe
{

    /**
     * @var mysqli
     */
    private $db;
    private $rowsCount = 0;
    private $result;

    private $host;
    private $dbname;
    private $username;
    private $password;

    private function load_auth_param()
    {
        $this->host = 'localhost';
        $this->dbname = 'toko_dba';
        $this->username = 'toko_usr';
        $this->password = 'T0k0U$er183729Pa$$#';
    }

    public function connect()
    {
        $this->load_auth_param();
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->dbname);

        mysqli_set_charset($this->db, 'utf8mb4');
        mysqli_query($this->db, "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_bin'");
        mysqli_query($this->db, "SET CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin'");
    }

    public function close()
    {
        mysqli_close($this->db);
    }

    public function num_rows($result)
    {
        $this->rowsCount = mysqli_num_rows($result);

        return $this->rowsCount;
    }

    public function query($query)
    {
        $this->result = mysqli_query($this->db, $query);

        if (mysqli_error($this->db) != "") {
            echo mysqli_error($this->db) . "<br>query=" . $query;
        }

        return $this->result;
    }

    public function result($result, $number, $field_name)
    {
        $rowsCount = mysqli_num_rows($result);
        if ($rowsCount && $number <= ($rowsCount - 1) && $number >= 0) {
            mysqli_data_seek($result, $number);
            $resrow = is_numeric($field_name) ? mysqli_fetch_row($result) : mysqli_fetch_assoc($result);
            if (isset($resrow[$field_name])) {
                return $resrow[$field_name];
            }
        }

        return false;
    }

    public function getDbName()
    {
        return $this->dbname;
    }

    public function clear_param($value)
    {
        $value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $value);
        return mysqli_real_escape_string($this->db, $value);
    }

}
