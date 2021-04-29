<?php

class Model extends PDO
{

    protected $db;
    protected $sql;
    protected $tableName;
    protected $error;
    protected $wheres = [];
    protected $whereType = 'OR';
    public $returnAs = 'array';

    public function __construct($config = null)
    {
        if (!$config) {
            $config = file_get_contents(__DIR__ . '/../config/db.php');
        }
        $dbHost = $config['dbHost'];
        $dbName = $config['dbName'];
        $dbUser = $config['dbUser'];
        $dbPassword = $config['dbPassword'];
        $charset = $config['charset'];

        try {
            parent::__construct('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPassword);
            $this->query('SET CHARACTER SET ' . $charset);
            $this->query('SET NAMES ' . $charset);
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function setTable($table)
    {
        $this->tableName = $table;
        return $this;
    }

    public function select($columns = '*')
    {
        $this->sql = "SELECT " . $columns . " FROM " . $this->tableName;
        return $this;
    }

    public function getAll()
    {
        if ($this->wheres) {
            $values = $this->setWheres();
        }
        $query = $this->prepare($this->sql);
        $query->execute($values);
        if ($this->returnAs == 'array') {
            $result = $query->fetchAll(parent::FETCH_ASSOC);
        } elseif ($this->returnAs == 'json') {
            $result = $query->fetchAll(parent::FETCH_OBJ);
        }
        return $result;
    }

    public function get()
    {
        if ($this->wheres) {
            $this->sql .= ' WHERE ';
            $values = [];
            $tmpArray = [];
            foreach ($this->wheres as $where) {
                $tmpArray[] = " {$where['key']} {$where['operator']} ? ";
                $values[] = $where['value'];
            }
            $this->sql .= implode(' ' . $this->whereType . ' ', $tmpArray);
        }
        $query = $this->prepare($this->sql);
        $query->execute($values);
        if ($this->returnAs == 'array') {
            $result = $query->fetch(parent::FETCH_ASSOC);
        } elseif ($this->returnAs == 'json') {
            $result = $query->fetch(parent::FETCH_OBJ);
        }
        return $result;
    }

    public function insert($data)
    {
        $this->sql = "INSERT INTO " . $this->tableName . " SET ";
        $tmpArray = [];
        $values = [];
        foreach ($data as $key => $value) {
            $tmpArray[] = "$key = :{$key}";
            $values[] = $value;
        }
        $this->sql .= implode(', ', $tmpArray);
        try {
            $query = $this->prepare($this->sql);
            $result = $query->execute($values);
            return $result;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function where($key, $value = null, $operator = '=')
    {
        $this->wheres[] = [
            'key' => $key,
            'value' => $value,
            'operator' => $operator
        ];
    }

    public function join($side, $table, $on)
    {
        $this->sql .= " {$side} JOIN {$table} ON {$on} ";
    }

    public function update($data)
    {
        $this->sql = "UPDATE " . $this->tableName . " SET ";
        $tmpArray = [];
        $values = [];
        foreach ($data as $key => $value) {
            $tmpArray[] = "$key = ?";
            $values[] = $value;
        }
        $this->sql .= implode(', ', $tmpArray);
        if($this->wheres){
            $this->setWheres();
        }
        try {
            $query = $this->prepare($this->sql);
            $result = $query->execute($values);
            return $result;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function setWheres()
    {
        $this->sql .= ' WHERE ';
        $values = [];
        $tmpArray = [];
        foreach ($this->wheres as $where) {
            $tmpArray[] = " {$where['key']} {$where['operator']} ? ";
            $values[] = $where['value'];
        }
        $this->sql .= implode(' ' . $this->whereType . ' ', $tmpArray);
        return $values;
    }

    public function setWhereType($whereType)
    {
        $this->whereType = $whereType;
    }

    public function insertId()
    {
        return $this->lastInsertId();
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function hasError()
    {
        return (bool)$this->error;
    }

    public function getError()
    {
        return $this->error;
    }

}