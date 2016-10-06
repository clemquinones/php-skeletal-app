<?php

class QueryBuilder
{
    protected $connection;

    /**
     * Accept required collaborators
     * 
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;       
    }
}