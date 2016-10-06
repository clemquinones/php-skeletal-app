<?php
/**
 *
 * MySQL Query Builder
 *
 * @author     Clemente Quiñones Jr.
 * @copyright  Copyright (c) 2016
 * @since      Version 0.0.7
 * @link       https://github.com/clemquinones/mysql-querybuilder
 */


class QueryBuilder
{
    const QUERY_TYPE_SELECT = 'select';
    const QUERY_TYPE_INSERT = 'insert';
    const QUERY_TYPE_UPDATE = 'update';
    const QUERY_TYPE_DELETE = 'delete';
    const QUERY_TYPE_RAW = 'raw';

    protected $db;
    protected $query;
    protected $statement;

    //Query type.
    protected $type;
    protected $table;
    //Plain data inputs
    protected $data = [];
    protected $params = [];
    protected $where = [];
    protected $limit = null;
    protected $offset = null;
    protected $orders = [];
    protected $lastQuery = "";


    /**
     * Class constructor.
     * 
     * @param PDO $pdo
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /**
     * Set a table to be use.
     * 
     * @param string $table
     * @return $this
     */
    public function from($table)
    {
        $this->table = $table;

        return $this;
    }


    /**
     * Find the record using the given id.
     * 
     * @param string $table
     * @param int $id
     * @return array
     */
    public function find($table, $id)
    {
        return $this->from($table)->where(['id' => $id])->first();
    }


    /**
     * Get the first record.
     * 
     * @param string $table
     * @return array
     */
    public function first($table = null)
    {
        if (! is_null($table)) {
            return $this->from($table)->fetch();
        }

        return $this->fetch();
    }


    /**
     * Get all records.
     * 
     * @param $table
     * @return arary
     */
    public function all($table)
    {
        $this->type = static::QUERY_TYPE_SELECT;
        $this->from($table)->execute();

        return $this->fetchAll();
    }


    /**
     * Register an insert query.
     * 
     * @param string $table
     * @param array $data
     * @return array
     */
    public function insert($table, array $data)
    {
        $this->type = static::QUERY_TYPE_INSERT;
        $this->data = array_merge($this->data, $data);
        $this->execute();
        $this->clear();

        return $this->from($table)->where($data)->first();
    }


    /**
     * Register an update query
     */
    public function update($table, array $data)
    {
        $this->type = static::QUERY_TYPE_UPDATE;
        $this->data = array_merge($this->data, $data);

        $this->from($table)->execute();

        return $this->clear()->from($table)->where($this->where)->first();
    }


    /**
     * Deletes a record.
     * 
     * @param string $table
     * @param int $id
     * @return boolean
     */
    public function delete($table, $id = null)
    {
        $this->type = static::QUERY_TYPE_DELETE;

        if (! is_null($id)) {
            $this->where(['id' => $id ]);
        }

        return $this->from($table)->execute();
    }


    /**
     * Set new where
     * 
     * @param array|callable $conditions
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function where($conditions, $operator = '=', $value = null)
    {
        if (is_callable($conditions)) {
            return call_user_func($conditions, $this);
        }

        if (is_array($conditions)) {
            $this->where = array_merge($this->where, $conditions);            
        }

        return $this;
    }


    /**
     * Set a limit and/or offset
     * 
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }


    /**
     * Set orders.
     * 
     * @param array|string $fields
     * @param string $order
     */
    public function orderBy($fields, $order = 'asc')
    {
        $order = strtolower($order);

        if (! is_array($fields)) {
            return $this->orderBy([$fields => $order]);
        }

        foreach ($fields as $key => $value) {
            if (! in_array($value, ['asc', 'desc'])) {
                throw new Exception('Invalid order.');
            }

            $this->orders[$key] = $value;
        }

        return $this;
    }


    /**
     * Get the query result.
     * 
     * @param string $table
     * @return array
     */
    public function get($table = null)
    {
        if (! is_null($table)) {
            $this->from($table);
        }

        return $this->fetchAll();
    }


    /**
     * Fetch a single record.
     * 
     * @return array|Exception
     */
    public function fetch()
    {
        $this->type = static::QUERY_TYPE_SELECT;        
        $this->limit(1)->execute();

        if ($item = $this->statement->fetch(PDO::FETCH_ASSOC) ) {
            return $item;
        }

        throw new Exception('Record not found.');
    }


    /**
     * Fetch all results.
     * 
     * @return array
     */
    public function fetchAll()
    {
        if ($this->isRaw()) {
            return $this->statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->type = 'select';
        $this->execute();

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Prepare a raw query.
     * 
     * @param string $query
     * @param array $params
     */
    public function raw($query, array $params = [])
    {
        $this->clear();
        $this->query = $query;
        $this->type = static::QUERY_TYPE_RAW;
        $this->execute($params);

        return $this;
    }


    /**
     * Check if the query type is RAW.
     * 
     * @return boolean
     */
    protected function isRaw()
    {
        $this->type = static::QUERY_TYPE_RAW;
    }


    /**
     * Build the query string.
     * 
     * @return $this
     */
    protected function build()
    {
        $this->makeIntroQuery()
            ->normalizeData()
            ->setConditions()
            ->setOrders()
            ->setLimits();

        //Save the current query to history
        $this->lastQuery = $this->query;

        return $this;
    }


    /**
     * Get intro query
     * 
     * @return $this
     */
    protected function makeIntroQuery()
    {
        switch ($this->type) {
            case static::QUERY_TYPE_SELECT:
                $this->query = "select * from `{$this->table}`";
                break;

            case static::QUERY_TYPE_INSERT:
                $this->query = "insert into `{$this->table}`%s values (%s)";
                break;

            case static::QUERY_TYPE_UPDATE:
                $this->query = "update `{$this->table}` set";
                break;

            case static::QUERY_TYPE_DELETE:
                $this->query = "delete from `{$this->table}`";
                break;
        }

        return $this;
    }


    /**
     * Normalize the data.
     * 
     * @return $this
     */
    protected function normalizeData()
    {
        if (! count($this->data)) {
            return $this;
        }

        if ($this->type == static::QUERY_TYPE_INSERT) {
            foreach ($this->data as $field => $value) {
                $fields[] = "`{$field}`";
                $values[] = ":{$field}";
            }

            $this->query = sprintf($this->query, implode(', ', $fields), implode(', ', $values));
        }

        if ($this->type == static::QUERY_TYPE_UPDATE) {
            foreach ($this->data as $field => $value) {
                $data[] = "`{$field}` = :{$field}";
            }

            $this->query .= implode(', ', $data);
        }

        return $this;
    }


    /**
     * Normalize conditions
     * 
     * @return $this
     */
    protected function setConditions()
    {
        if (count($this->where)) {
            $conditions = [];
            foreach ($this->where as $field => $value) {
                $conditions[] = "`{$field}` = :$field";
                $this->params[":{$field}"] = $value;
            }

            $this->query .= sprintf(" where %s", implode(' and ', $conditions));
        }

        return  $this;
    }


    /**
     * Set orders
     * 
     * @return $this
     */
    protected function setOrders()
    {
        if (count($this->orders)) {
            $orders = [];
            foreach ($this->orders as $field => $order) {
                $orders[] = "{$field} $order";
            }

            $this->query .= sprintf(" order by %s", implode(', ', $orders));
        }

        return $this;
    }


    /**
     * Set limits.
     * 
     * @return $this
     */
    protected function setLimits()
    {
        if ($this->limit) {
            $this->query .= sprintf(" limit %s%s",
                $this->limit,
                ($this->offset ? ", ".$this->offset : "")
            );
        }

        return $this;
    }


    /**
     * Execute the prepared query.
     * 
     * @param array $params
     * @return boolean
     */
    protected function execute(array $params = [])
    {
        $this->build();

        if (count($params)) {
            $this->params = array_merge($this->params, $params);
        }

        $this->statement = $this->db->prepare($this->query);

        foreach ($this->params as $key => $value) {
            $this->statement->bindValue($key, $value, $this->getType($value));
        }

        return $this->statement->execute();
    }


    /**
     * Get the last executed query.
     * 
     */
    public function lastQuery()
    {
        return $this->lastQuery;
    }


    /**
     * Get instance of the given value.
     * 
     * @param mixed $value
     * @return PDO PARAM TYPE
     */
    protected function getType($value)
    {
        switch (true) {
            case is_int($value):
                return PDO::PARAM_INT;

            case is_bool($value):
                return PDO::PARAM_BOOL;

            case is_string($value):
                return PDO::PARAM_STR;

            case is_null($value):
                return PDO::PARAM_NULL;
            
            default:
                return false;
        }
    }


    /**
     * Reset everything.
     * 
     */
    protected function clear()
    {
        $this->query = null;
        $this->statement = null;
        $this->limit = null;
        $this->offset = null;
        $this->where = [];
        $this->table = null;
        $this->params = [];
        $this->data = [];
        $this->type = static::QUERY_TYPE_SELECT;

        return $this;
    }
}