<?php
class Model
{

    protected $db;
    protected $table = "";
    public function __construct()
    {
        $dns = 'mysql:dbname=' . DB_NAME . ";host=" . DB_HOST;
        try {
            $this->db = new PDO($dns, DB_USER, DB_PASSWORD);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
            exit();
        }
    }

    /*
    * Returns all as an array
    * @return array
    */
    public function getAll()
    {
        try {
            $sth = $this->db->prepare("SELECT * FROM $this->table");
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e)
        {
            return array();
        }
    }

    /*
    * Returns count
    * @return int
    */
    public function count()
    {
        try {
            return (int)$this->db->query("SELECT COUNT(*) FROM $this->table")->fetchColumn();
        } catch (PDOException $e)
        {
            return 0;
        }
    }

    /*
    * Find by id
    * @param null $id
    * @return array|mixed
    */
    public function getById($id = null)
    {
        try {
            $sth = $this->db->prepare("SELECT * FROM $this->table WHERE id = ?");
            $sth->execute(array($id));
            return $sth->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e)
        {
            return false;
        }
    }

    /*
    * Find by column, returns count if $count = true
    * @return int
    */
    public function getBy($columnName, $value, $count = false)
    {
        try
        {
            $sth = $this->db->prepare("SELECT * FROM $this->table WHERE $columnName = :columnValue");
            $sth->bindParam(':columnValue', $value, PDO::PARAM_STR);
            $sth->execute();
            $resultSet = $sth->fetchAll(PDO::FETCH_ASSOC);
            if ($count)
            {
                return count($resultSet);
            }
            return $resultSet;
        } catch (PDOException $e)
        {
            return false;
        }
    }

    /*
    * Insert
    * @param array $data
    * @return int
    */
    public function save($data = array())
    {
        $fields = array_keys($data); // here you have to trust your field names!
        $values = array_values($data);
        $fieldlist = implode(',', $fields);
        $qs = str_repeat("?,", count($fields) - 1);
        $sql = "insert into $this->table($fieldlist) values(${qs}?)";
        try {
            $sth = $this->db->prepare($sql);
            $sth->execute($values);
            return $this->db->lastInsertId();
        } catch (PDOException $e)
        {
            return false;
        }
    }

    /*
    * Delete
    * @param null $id
    * @return int
    */
    public function delete($id = null)
    {
        try {
            $sth = $this->db->prepare("DELETE FROM $this->table WHERE id = ? LIMIT 1");
            return $sth->execute(array($id));
        } catch (PDOException $e)
        {
            return false;
        }
    }
    /* Update
    * @param null $id
    * @param array $data
    * @return bool
    */
    public function update($id = null, $data = array())
    {
        $temp = array();
        foreach (array_keys($data) as $name) {
            $temp[] = "`$name` = ?";
        }

        $setPara = implode(', ', $temp);
        $sql = "UPDATE $this->table SET $setPara WHERE id = ?";
        $queryData = array_values($data);
        $queryData[] = $id;
        try
        {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($queryData);
        } catch (PDOException $e)
        {
            return false;
        }
    }
}