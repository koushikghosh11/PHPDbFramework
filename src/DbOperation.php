<?php

namespace MySQLDbOps;

use Exception;
use PDO, PDOException, PDOStatement;

const br = "<br>";
function br(): void
{
    echo br;
}

function pre(...$arr)
{
    echo "<pre>";
    foreach ($arr as $item) {
        print_r($item);
    }
    echo "</pre>";
}

function randStr($length = 8)
{
    return substr(str_shuffle(str_repeat('0123456789!$%^&*?@#abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', mt_rand(1, 10))), 1, $length);
}

require __DIR__ . "/DbConnection.php";

class DbOperation extends DbConnection
{
    const DEF_LIMIT = "0,1000", TABLE_DATA_TYPE = array("sl_no" => PDO::PARAM_INT, "id" => PDO::PARAM_STR, "name" => PDO::PARAM_STR, "passwd" => PDO::PARAM_STR, "tabs" => PDO::PARAM_STR, "creationTime" => PDO::PARAM_STR);
    private string $defaultTable;
    private ?PDO $connection;

    public function __construct(string $host = 'localhost', string $userName = 'me', string $password = '1ixdzAvOfL1iAsrl', string $dbName = 'textConv')
    {
        parent::__construct($host, $userName, $password, $dbName);
        $this->connection = $this->connect();
        $this->defaultTable = "siteInfo";
    }

    public function __destruct()
    {
        $this->connection = null;
//        echo "connection closed";
    }

    /**
     * Deletes all value from the table and sets AUTO_INCREMENT to 0
     * @param $table
     * @return void
     */
    protected function resetTable($table = null)
    {
        try {
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
            $sql = "DELETE FROM $table WHERE 1; ALTER TABLE $table AUTO_INCREMENT = 0;";
            $this->connection->exec($sql);
            echo "table reset";
        } catch (PDOException $e) {
            echo "cant reset " . $e->getMessage();
        }
    }

    /*protected function alterTable($table = null)
    {
        $sql = "ALTER TABLE $table AUTO_INCREMENT = 0";

    }*/

    /**
     * set the default table of this connection
     * @param string $defaultTable
     * @return DbOperation
     */
    public function setDefaultTable(string $defaultTable): DbOperation
    {
        $this->defaultTable = $defaultTable;
        return $this;
    }

    /** returns string from array imploded with ','
     * @param $array
     * @return string|null
     */
    private static function arr2str($array): ?string
    {
        /*if ($surround) {
            $array = array_map(function ($i) {
                return "'$i'";
            }, $array);
        }*/
//        print_r($array);
        return implode(",", $array);
    }

    /**
     * returns true if given array is associative array, can be specified if it includes numbered indexes with 2nd argument
     * @param array $array
     * @param bool $mixed
     * @return bool
     */
    private static function isAssocArr(array $array, bool $mixed = false): bool
    {
        return !empty($array) && ($mixed ? (array_keys($array) !== range(0, count($array) - 1) || count(array_filter(array_keys($array), 'is_string')) > 0) : (count(array_filter(array_keys($array), 'is_numeric')) == 0));
    }

    /**
     * map array including its keys and values
     * @param callable $func
     * @param array $array
     * @return mixed
     */
    private static function array_map_key_value(callable $func, array $array)
    {
        return array_map($func, array_keys($array), $array);
    }

    /**
     * prepare parameters which to be bound with prepared statement
     * @param ...$arrays
     * @return array
     */
    private static function prepParam(...$arrays): array
    {
        $reArr = array();
        foreach ($arrays as $array) {
            $reArr = array_merge($reArr, self::array_map_key_value(function ($k, $v) {
                return array("key" => $k, "val" => $v);
            }, $array));
        }
        return $reArr;
    }

    /**
     * returns sql statement to be prepared and stores the string keyed elements in the $array variable which to be bound later
     * @param array $assocArr
     * @param $array
     * @return string
     */
    private static function condSanitize(array $assocArr, &$array): string
    {
        /*foreach ($assocArr as $key => $value) {
            if (is_string($key)) {
                $array[":$key"] = $value;
            }
        }*/
        $a = self::htmlEntitiesArrOrStr(array_filter($assocArr, 'is_string', ARRAY_FILTER_USE_KEY));
        unset($a['passwd']);
        $array = self::prepParam($a);
//        pre($array);
        return implode(" ", self::array_map_key_value(function ($key, $value) {
            if (is_numeric($key)) {
                if ($value == "AND" || $value == "OR" || $value == "NOT")
                    return $value;
                else return "0";
            }
//            return "$key=:$key";
            return "$key=?";
        }, $assocArr));
    }

    /**
     * prepares and executes sql query with given arguments $sql & $params(default=null)
     * @param string $sql
     * @param array|null $params
     * @param int $fetchMode
     * @return false|PDOStatement
     */
    private function execQuery(string $sql, array $params = null, int $fetchMode = PDO::FETCH_ASSOC)
    {
//        echo $sql.br;
//        pre($params);
        try {
            $stmt = $this->connection->prepare($sql);
            if (!is_null($params)) {
                $i = 1;
                foreach ($params as $item) {
                    $stmt->bindValue($i, $item['val'], self::TABLE_DATA_TYPE[$item['key']]);
                    $i++;
                }
            }
            $stmt->execute();
            $stmt->setFetchMode($fetchMode);
            return $stmt;
        } catch (PDOException $e) {
            echo "Something wrong happened in execQuery method: " . $e->getMessage() . br;
            return false;
        }
    }

    /**
     * @param array|string|null $data
     * @return array|false|string
     */
    public static function htmlEntitiesArrOrStr($data, string $except = null)
    {
        try {
//            echo "this is ".gettype($data).br;
            if (is_array($data) && !empty($data)) {
                if (self::isAssocArr($data)) {
//                    echo "this is Assoc array".br;
                    return array_combine(array_keys($data), array_map(function ($e) use ($except, $data) {
                        if ($except != null) {
                            if ($e == $data[$except]) {
//                                echo "this is except " . $data[$except] . br;
                                return $e;
                            }
                        }
//                        echo "entity assoc array e-> \"$e\" ent(\"$e\") -> \"".htmlentities($e,ENT_QUOTES)."\"".br;
                        return htmlentities($e, ENT_QUOTES);
                    }, array_values($data)));
                }
//                echo "this is normal array";
                return array_map(function ($v) use ($except) {
                    if ($v == $except) return $v;
                    return htmlentities($v, ENT_QUOTES);
                }, $data);
            }
            return htmlentities($data, ENT_QUOTES);
        } catch (Exception $e) {
            // log
            echo "cant entity";
            return false;
        }
    }

    /**
     * returns true if the value is found in database otherwise false
     * @param array $condition
     * @param string $column
     * @param string|null $table
     * @return bool
     */
    public function includes(array $condition, string $column = "*", string $table = null): bool
    {
        try {
//            pre($condition);
            if (!self::isAssocArr($condition, true)) return false;
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
            $sql = "SELECT $column FROM $table WHERE " . self::condSanitize($condition, $arr);
//            echo $sql . br;
            // if "passwd" is given in condition array then hash the value of "passwd" to match with the hashed password stored in database
            /*if (isset($arr)) {
                echo "before hash ";
                pre($arr);
                $arr = array_map(function ($e){
                    if ($e['key']=="passwd"){
//                        var_dump(password_verify($e['val'], ));
                        $e['val'] = "after hash ** -> ".password_hash($e['val'], PASSWORD_BCRYPT);
                        return $e;
                    }
                    return $e;
                },$arr);
                echo "after hash ";
                pre($arr);
            }*/
            $preparedQuery = $this->execQuery($sql, $arr);
            if (!$preparedQuery) return false;
//            $result = $this->connection->query($sql);
//            $preparedQuery->execute();
//            $preparedQuery->setFetchMode(PDO::FETCH_ASSOC);
//            $result = $preparedQuery->fetchAll();
            $result = $preparedQuery->rowCount();
//            echo "prepQ ";
//            echo count($result);
//            var_dump($result);
//            pre($result);
//            br();
            return $result > 0;
        } catch (PDOException $e) {
            echo "Something wrong happened in includes method: " . $e->getMessage() . br;
            return false;
        }
    }

    /**
     * runs SELECT query with given arguments and returns value
     * @param string|array $column
     * @param array|int|string $condition
     * @param string|null $table
     * @param string|int $order
     * @param string $limit
     * @param bool $distinct
     * @return array|false
     */
    public function get($column = "*", $condition = 1, string $table = null, $order = 1, string $limit = self::DEF_LIMIT, bool $distinct = false)
    {
        try {
            if (is_array($condition)) {
                if (!self::isAssocArr($condition, true)) return false;
                $condition = self::condSanitize($condition, $arr);
            }
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
            if (is_array($column)) $column = self::arr2str($column);
            $distinct = $distinct ? "DISTINCT " : "";
            $sql = "SELECT $distinct$column FROM $table WHERE $condition ORDER BY $order LIMIT $limit";
//            echo $sql . br;
//            $result = $this->connection->query($sql);
//            pre($result->fetch_assoc(), $result->fetch_assoc(), $result->fetch_assoc(), $result->fetch_assoc());
//            pre($result->fetch_all(MYSQLI_ASSOC));
//            $result->free_result();
//            return $result->fetch_all(MYSQLI_ASSOC);

//        var_dump(isset($arr));
            $prep = $this->execQuery($sql, $arr ?? null);
            return $prep ? $prep->fetchAll() : false;
        } catch (PDOException $e) {
            echo "Something wrong happened in get method: " . $e->getMessage() . br;
            return false;
        }
    }

    /**
     * runs INSERT query and returns true if data inserted into database otherwise false
     * @param array $column_value
     * @param ?string $table
     * @return bool
     */
    public function insert(array $column_value, string $table = null): bool
    {
        try {
//            var_dump(empty($column_value));
            if (!self::isAssocArr($column_value)) return false;
//            echo "array not empty";
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
//            pre($column_value);
            $column_value = self::htmlEntitiesArrOrStr($column_value);
//            pre($column_value);
            // hash the password
            $passHash = password_hash($column_value['passwd'], PASSWORD_BCRYPT);
            $column_value['passwd'] = $passHash;
//            $f = fopen("passHash.txt","a");
//            echo "passhash -> $passHash".br;
//            fwrite($f, $passHash.PHP_EOL);
//            fclose($f);
            $sql = "INSERT INTO $table (" . self::arr2str(array_keys($column_value)) . ") VALUES (" . self::arr2str(array_pad(array(), count($column_value), "?")) . ")";
//            echo $sql . br;
//            return $this->connection->query($sql);

            /*$column_value = array_combine(array_map(function ($k) {
                return ":$k";
            }, array_keys($column_value)), array_values($column_value));*/
//            pre($column_value);
            return boolval($this->execQuery($sql, self::prepParam($column_value)));
        } catch (PDOException $e) {
            echo "Something wrong happened in insert method: " . $e->getMessage() . br;
            return false;
        }
    }

    /**
     * runs UPDATE query and returns true if data updated in database otherwise false
     * @param array $update
     * @param array $condition
     * @param ?string $table
     * @return bool
     */
    public function update(array $update, array $condition, string $table = null): bool
    {
        try {
            if (!self::isAssocArr($update) && !self::isAssocArr($condition, true)) return false;
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
            /*$arr = array_combine(array_map(function ($k) {
                return ":$k";
            }, array_keys($update)), array_values($update));*/
            $u = implode(",", array_map(function ($k) {
                return "$k=?";
            }, array_keys($update)));
            $update = self::htmlEntitiesArrOrStr($update);
            $sql = "UPDATE $table SET $u WHERE " . self::condSanitize($condition, $arr);
//            echo $sql.br;
//        return $this->connection->query($sql);
            return boolval($this->execQuery($sql, array_merge(self::prepParam($update), $arr)));
        } catch (PDOException $e) {
            echo "Something wrong happened in update method: " . $e->getMessage() . br;
            return false;
        }
    }

    /**
     * runs DELETE query and returns true if data deleted from database otherwise false
     * @param array|int $condition
     * @param ?string $table
     * @return bool
     */
    public function delete($condition = 0, string $table = null): bool
    {
        try {
            if (is_array($condition)) {
                if (!self::isAssocArr($condition, true)) return false;
                $condition = self::condSanitize($condition, $arr);
            }/*elseif (is_numeric($condition) && $condition == 1) {
                return false;
            }*/
            // if table not given , setting table value as default table value
            $table = $table ?? $this->defaultTable;
            $sql = "DELETE FROM $table WHERE $condition";
//            return $this->connection->query($sql);
            return boolval($this->execQuery($sql, $arr ?? null));
        } catch (PDOException $e) {
            echo "Something wrong happened in delete method: " . $e->getMessage() . br;
            return false;
        }
    }

    public function passwdVerify(string $password,array $condition): bool
    {
        if (!self::isAssocArr($condition)) return false;
        return password_verify(self::htmlEntitiesArrOrStr($password), self::get("passwd",$condition)[0]['passwd']);
    }

    /**
     * returns the last query's id
     * @return false|string
     */
    public function getInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * @return void
     */
//    public function test()
//    {
//        var_dump(self::includes(array("name"=>"I1PlLu#M","AND","passwd" => "@r6jG3sX")));
//        br();
//        var_dump(self::update(array("name"=>"a1s2d3f4"), array("name"=>"a1s2d3f4aa11")));
//        br();
//        var_dump(self::insert(array("id"=>randStr(),"name"=>randStr(),"passwd"=>randStr(),"tabs"=>randStr(20))));
//        br();
//        var_dump(self::getInsertId());
//        br();
//        pre(self::get("*"));
//        br();
//        self::resetTable();
//        br();
//        var_dump(self::delete(1));
//        br();
//        pre(self::get("*"));
//        br();
//    }
}

//set_exception_handler(function ($e) {
//    echo "Something wrong happened : ", $e->getMessage() . br;
//});

//$t = new DbOperation();
//$t->test();
//echo randStr().br;
