<?php

class DB
{
    public static $lastQueryTime = 0;

    private static $mysqli;
    private static $field_sanitizer = "`";
    private static $data_sanitizer = "'";
    private $statements = array();

    protected function _where($key, $operator = null, $value = null, $joiner = 'AND')
    {
        $this->statements['wheres'][] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }
    
    protected function addStatement($key, $value)
    {
        if (!is_array($value)) $value = array($value);

        if (!array_key_exists($key, $this->statements)) $this->statements[$key] = $value;
        else $this->statements[$key] = array_merge($this->statements[$key], $value);
    }
    
    private function aggregate($type)
    {
        throw new Exception("Not implemented");
    }
    
    private function arrayStr(array $pieces, $glue, $wrapSanitizer = true, $useKeys = false)
    {
        $str = '';
        
        foreach ($pieces as $key => $value) 
        {
            if ($useKeys)
            {
                if ($wrapSanitizer) 
                {
                    $key = $this->wrapSanitizer($key, self::$field_sanitizer);
                    $value = $this->wrapSanitizer($value, self::$data_sanitizer);
                }
                $str .= "$value as $key$glue";
            }
            else
            {
                if ($wrapSanitizer) $value = $this->wrapSanitizer($value, self::$field_sanitizer);
                $str .= $value . $glue;
            }
        }
        return trim($str, $glue);
    }
    
    private function buildCriteria($statements, $bindValues = true)
    {
        $criteria = '';
        $bindings = array();
        
        foreach ($statements as $statement) 
        {
            $key = $this->wrapSanitizer($statement['key'], self::$field_sanitizer);
            $value = $statement['value'];

            if (is_array($value)) 
            {
                $valuePlaceholder = '';
                foreach ($statement['value'] as $subValue) 
                {
                    $valuePlaceholder .= '?, ';
                    $bindings[] = $subValue;
                }

                $valuePlaceholder = trim($valuePlaceholder, ', ');
                $criteria .= $statement['joiner'] . " $key " . $statement['operator'] . " ($valuePlaceholder)";
            } 
            else 
            {
                if (!$bindValues) 
                {
                    $value = $this->wrapSanitizer($value, self::$data_sanitizer);
                    $criteria .= $statement['joiner'] . " $key " . $statement['operator'] . " $value ";
                } 
                else 
                {
                    $valuePlaceholder = '?';
                    $bindings[] = $value;
                    $criteria .= $statement['joiner'] . " $key " . $statement['operator'] . " $valuePlaceholder ";
                }
            }
        }

        $criteria = preg_replace('/^(\s?AND ?|\s?OR ?)|\s$/i','', $criteria);
        return array($criteria, $bindings);
    }
    
    private function buildCriteriaWithType($key, $type, $bindValues = true)
    {
        $criteria = '';
        $bindings = array();

        if (isset($this->statements[$key])) 
        {
            list($criteria, $bindings) = $this->buildCriteria($this->statements[$key], $bindValues);
            if ($criteria) $criteria = $type . ' ' . $criteria;
        }
        return array($criteria, $bindings);
    }
    
    private function buildJoin()
    {
        $sql = '';
        if (!array_key_exists('joins', $this->statements) || !is_array($this->statements['joins'])) return $sql;

        foreach ($this->statements['joins'] as $type => $joinArr) 
        {
            $table = $this->wrapSanitizer($joinArr['table'], self::$field_sanitizer);
            $joinBuilder = $joinArr['joinBuilder'];

            $sqlArr = array($sql, strtoupper($type), 'JOIN', $table, 'ON', $joinBuilder->getQuery('criteriaOnly', false)->getSql());
            $sql = $this->concatenateQuery($sqlArr);
        }

        return $sql;
    }
    
    public static function clearMultiQueryResults()
    {
        while(self::$mysqli->more_results() && self::$mysqli->next_result()) self::$mysqli->store_result();
    }
    
    public static function close()
    {
        self::$mysqli->close();
    }

    protected function concatenateQuery(array $pieces)
    {
        $str = '';
        foreach ($pieces as $piece) $str = trim($str) . ' ' . trim($piece);
        return trim($str);
    }
    
    public static function connect($server, $username, $password, $database)
    {
        if (empty(self::$mysqli)) self::$mysqli = new mysqli($server, $username, $password, $database);
        self::$mysqli->set_charset("utf8");
        return !self::$mysqli->connect_errno;
    }
    
    public static function connection()
    {
        return self::$mysqli;
    }
    
    public function count()
    {
        $this->addStatement("count", "true");
        $start = microtime(TRUE);
        $query = $this->getQuery();
        $result = self::$mysqli->query($query);
        self::$lastQueryTime = microtime(TRUE) - $start;
        if (!$result) return NULL;
        $row = $result->fetch_row();
        return $row[0];
    }
    
    public function countQuery()
    {
        $this->addStatement("count", "true");
        return $this->getQuery();
    }

    public static function createTableIfNotExists($table_name, $fields)
    {
        $sql = "CREATE TABLE IF NOT EXISTS $table_name ($fields);";
        return self::query($sql);
    }
    
    public function delete()
    {
        $statements = $this->statements;
        if (!isset($statements['tables'])) throw new Exception('No table specified', 3);
        $table = end($statements['tables']);

        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType('wheres', 'WHERE');

        $sqlArray = array('DELETE from', $table, $whereCriteria);
        $sql = $this->concatenateQuery($sqlArray, ' ', false);
        $bindings = $whereBindings;
        $res = $this->interpolateQuery($sql, $bindings);
        
        return self::$mysqli->query($res);
    }
    
    public function expression($fields)
    {
        $this->addStatement("expression", $fields);
        return $this;
    }
    
    public static function escape($value)
    {
        return mysqli_real_escape_string(DB::$mysqli, $value);
    }
    
    public static function error()
    {
        return self::$mysqli->error;
    }
    
    public static function error_list()
    {
        return self::$mysqli->error_list;
    }

    public function find($fieldName = 'id', $value = '')
    {
        $this->where($fieldName, '=', $value);
        return $this->first();
    }
    
    public function findAll($fieldName, $value)
    {
        $this->where($fieldName, '=', $value);
        return $this->get();
    }

    public function first()
    {
        $this->limit(1);
        $result = $this->get();
        if (!isset($result)) return NULL;
        return $result->fetchArray();
    }
    
    /**
     * 
     * @return DBResult
     */
    public function get()
    {
        $start = microtime(TRUE);
        $query = $this->getQuery();
        $result = self::$mysqli->query($query);
        //Log::f($query, "query.log");
        self::$lastQueryTime = microtime(TRUE) - $start;
        if ($result === FALSE) return NULL;
        return new DBResult($result);
    }
    
    public function getQuery()
    {
        $s = $this->statements;
        
        if (!array_key_exists('tables', $s)) throw new Exception('No table specified.', 3);
        elseif (!array_key_exists('selects', $s)) $s['selects'][] = '*';
        
        $tables = $this->arrayStr($s['tables'], ', ', TRUE);
        $selects = $this->arrayStr($s['selects'], ', ', TRUE);
        
        if (isset($s['count'])) $selects = "COUNT(" . $selects . ")";
        
        $expression = '';
        if (isset($s['expression']) && $expression = $this->arrayStr($s['expression'], ', ', TRUE, TRUE)) $expression = ", " . $expression;

        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType('wheres', 'WHERE');
        
        $groupBys = '';
        if (isset($s['groupBys']) && $groupBys = $this->arrayStr($s['groupBys'], ', ')) $groupBys = 'GROUP BY ' . $groupBys;

        $orderBys = '';
        if (isset($s['orderBys']) && is_array($s['orderBys'])) 
        {
            foreach ($s['orderBys'] as $orderBy) $orderBys .= $this->wrapSanitizer($orderBy['field'], self::$field_sanitizer) . ' ' . $orderBy['type'] . ',';
            if ($orderBys = trim($orderBys, ',')) $orderBys = 'ORDER BY ' . $orderBys;
        }

        $limit = isset($s['limit']) ? 'LIMIT ' . $s['limit'] : '';
        $offset = isset($s['offset']) ? 'OFFSET ' . $s['offset'] : '';

        list($havingCriteria, $havingBindings) = $this->buildCriteriaWithType('havings', 'HAVING');

        $joinString = $this->buildJoin();

        $sqlArray = array(
            'SELECT',
            $selects,
            $expression,
            'FROM',
            $tables,
            $joinString,
            $whereCriteria,
            $groupBys,
            $havingCriteria,
            $orderBys,
            $limit,
            $offset
        );

        $sql = $this->concatenateQuery($sqlArray);
        $bindings = array_merge($whereBindings, $havingBindings);
        return $this->interpolateQuery($sql, $bindings);
    }
    
    public function groupBy($field)
    {
        $this->addStatement('groupBys', $field);
        return $this;
    }
    
    public function having($key, $operator, $value, $joiner = 'AND')
    {
        $this->statements['havings'][] = compact('key', 'operator', 'value', 'joiner');
        return $this;
    }
    
    public function insert($data)
    {
        $query = $this->insertQuery($data);
        //Log::f($query, "query.log");
        return self::$mysqli->query($query);
    }
    
    public function insertQuery($data)
    {
        $statements = $this->statements;
        if (!isset($statements['tables'])) throw new Exception('No table specified', 3);
        elseif (count($data) < 1) throw new Exception('No data given.', 4);

        $table = end($statements['tables']);
        $bindings = $keys = $values = array();

        foreach ($data as $key => $value) 
        {
            $keys[] = $key;
            $values[] = ":$key";
            $bindings[$key] = $value;
        }

        $sqlArray = array(
            'INSERT INTO',
            $table,
            '(' . $this->arrayStr($keys, ',', FALSE) . ')',
            'VALUES',
            '(' . $this->arrayStr($values, ',', FALSE) . ')',
        );

        $sql = $this->concatenateQuery($sqlArray, ' ', FALSE);
        return $this->interpolateQuery($sql, $bindings);
    }
    
    private function interpolateQuery($query, $params)
    {
        $keys = array();
        $values = $params;

        foreach ($params as $key => $value) 
        {
            if (is_string($key)) $keys[] = '/:' . $key . '/';
            else $keys[] = '/[?]/';

            if (is_string($value)) $values[$key] = $this->quote($value);
            elseif (is_array($value)) $values[$key] = implode(',', $this->quote($value));
            elseif (is_null($value)) $values[$key] = 'NULL';
        }
        $query = preg_replace($keys, $values, $query, 1, $count);
        return $query;
    }
        
    public function limit($limit)
    {
        $this->statements['limit'] = $limit;
        return $this;
    }
    
    public function offset($offset)
    {
        $this->statements['offset'] = $offset;
        return $this;
    }
    
    public static function multi_query ($sql)
    {
        //Log::f("-------- START MULTI QUERY ---------", "query.log");
        //Log::f($sql, "query.log");
        //Log::f("-------- END MULTI QUERY ---------", "query.log");
        
        return self::$mysqli->multi_query($sql);
    }
    
    public function orHaving($key, $operator, $value)
    {
        return $this->having($key, $operator, $value, 'OR');
    }
    
    public function orWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() == 2) 
        {
            $value = $operator;
            $operator = '=';
        }

        return $this->_where($key, $operator, $value, 'OR');
    }
    
    public function orWhereIn($key, array $values)
    {
        return $this->where($key, 'IN', $values, 'OR');
    }

    public function orWhereNotIn($key, array $values)
    {
        return $this->where($key, 'NOT IN', $values, 'OR');
    }
    
    public function orderBy($field, $type = 'ASC')
    {
        $this->statements['orderBys'][] = compact('field', 'type');
        return $this;
    }
    
    public static function query($sql)
    {
        return self::$mysqli->query($sql);
    }

    private function quote($value)
    {
        if (is_array($value)) foreach ($value as &$v) $v = self::$data_sanitizer . self::$mysqli->real_escape_string($v) . self::$data_sanitizer;
        else $value = self::$data_sanitizer . self::$mysqli->real_escape_string($value) . self::$data_sanitizer;
        return $value;
    }

    public function select($fields = "*")
    {
        $this->addStatement("selects", $fields);
        return $this;
    }
    
    /**
     * 
     * @param type $tables
     * @return DB
     */
    public static function table($tables)
    {
        $instance = new self();
        $instance->addStatement('tables', $tables);
        return $instance;
    }
    
    /**
     * 
     * @param type $tableName
     * @return DB
     */
    public static function tableg($tableName)
    {
        return self::table($GLOBALS['tables'][$tableName]);
    }
    
    public static function tableExists($table_name)
    {
        $res = self::$mysqli->query("show tables like '$table_name';");
        return ($res->num_rows > 0);
    }
    
    public static function truncateTable($tableName)
    {
        self::$mysqli->query("TRUNCATE TABLE " . self::$field_sanitizer . "$tableName" . self::$field_sanitizer);
    }
    
    public static function truncateTableg($tableName)
    {
        self::truncateTable($GLOBALS['tables'][$tableName]);
    }
    
    public function update($data)
    {
        $query = $this->updateQuery($data);
        //Log::f($query, "query.log");
        return self::$mysqli->query($query);
    }
    
    public function updateQuery($data)
    {
        $statements = $this->statements;
        if (!isset($statements['tables'])) throw new Exception('No table specified', 3);
        elseif (count($data) < 1) throw new Exception('No data given.', 4);

        $table = $this->wrapSanitizer(end($statements['tables']), self::$field_sanitizer);

        $bindings = $keys = $values = array();
        $updateStatement = '';

        foreach ($data as $key => $value) 
        {
            $updateStatement .= $this->wrapSanitizer($key, self::$field_sanitizer) . "=:$key,";
            $bindings[$key] = $value;
        }

        $updateStatement = trim($updateStatement, ',');

        list($whereCriteria, $whereBindings) = $this->buildCriteriaWithType('wheres', 'WHERE');

        $sqlArray = array(
            'UPDATE',
            $table,
            'SET ' . $updateStatement,
            $whereCriteria,
        );

        $sql = $this->concatenateQuery($sqlArray, ' ', false);
        $bindings = array_merge($bindings, $whereBindings);
        return $this->interpolateQuery($sql, $bindings);
    }
    
    public function where($key, $operator = null, $value = null)
    {
        if (func_num_args() == 2) 
        {
            $value = $operator;
            $operator = '=';
        }
        
        return $this->_where($key, $operator, $value, 'AND');
    }
    
    public function whereIn($key, array $values)
    {
        return $this->_where($key, 'IN', $values, 'AND');
    }

    public function whereNotIn($key, array $values)
    {
        return $this->_where($key, 'NOT IN', $values, 'AND');
    }
    
    private function wrapSanitizer($value, $sanitizer)
    {
        $valueArr = explode('.', $value, 2);
        foreach ($valueArr as $key => $subValue) $valueArr[$key] = trim($subValue) == '*' ? $subValue : $sanitizer . $subValue . $sanitizer;
        return implode('.', $valueArr);
    }
}

class DBResult
{
    private $res;
    
    function __construct($result)
    {
        $this->res = $result;
    }
    
    /*
     * @return array
     */
    function fetchAll()
    {
        if (!$this->res) return NULL;
        $result = array();
        while ($row = $this->fetchArray()) $result[] = $row;
        return $result;
    }
    
    function fetchArray()
    {
        if (!$this->res) return NULL;
        $res = $this->res;
        return $res->fetch_array(MYSQLI_ASSOC);
    }
}