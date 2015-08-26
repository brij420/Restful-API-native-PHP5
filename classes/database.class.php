<?php


class Database {

    var $host = 'localhost';
    var $db = 'SID_DevDB';
    var $user = 'root';
    var $pass = 'root';
    var $query;
    var $check;
    var $querytype = "select";
    var $conn;
    var $result;
    var $query_result;
    var $row;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass);
        mysqli_query($this->conn, 'SET character_set_results=utf8');
        mysqli_query($this->conn, 'SET names=utf8');
        mysqli_query($this->conn, 'SET character_set_client=utf8');
        mysqli_query($this->conn, 'SET character_set_connection=utf8');
        mysqli_query($this->conn, 'SET character_set_results=utf8');
        mysqli_query($this->conn, 'SET collation_connection=utf8_general_ci');
        mysqli_select_db($this->conn, $this->db);
    }

    function execute_query($query, $check, $querytype = "select") {
        $this->query = $query;
        $this->check = $check;
        $this->querytype = $querytype;
        $query_result = array();
        $count = 0;

        $this->result = mysqli_query($this->conn, $this->query) OR die(mysqli_error($this->conn));

        if ($this->querytype == "select") {


            if ((isset($this->result)) && (count($this->result) > 0) && ($this->check)) {
                while ($this->row = mysqli_fetch_array($this->result, MYSQLI_ASSOC)) {
                    $this->query_result[] = $this->row;
                    $count++;
                }
                if (($this->check) && ($count)) {
                    $this->query_result['count'] = $count;
                } else {
                    $this->query_result['count'] = 0;
                }
                if (!isset($this->query_result['count'])) {
                    unset($this->query_result['count']);
                }
            }
            if ((isset($this->result)) && (count($this->result) > 0) && (!is_bool($this->result)) && (!$this->check)) {
                $this->query_result = mysqli_fetch_array($this->result, MYSQLI_ASSOC);
            }
        }
        if ($this->querytype == "insert") {
            $this->query_result['count'] = mysqli_affected_rows($this->conn);
            $this->query_result['last_id'] = mysqli_insert_id($this->conn);
        }
        if ($this->querytype == "delete") {
            $this->query_result['count'] = mysqli_affected_rows($this->conn);
        }
        if ($this->querytype == "update") {
            $this->query_result['count'] = mysqli_affected_rows($this->conn);
        }
        mysqli_close($this->conn);
        return $this->query_result;
    }

}

?>