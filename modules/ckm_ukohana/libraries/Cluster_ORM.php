<?php

class Cluster_ORM extends ORM
{
	public function __construct($data = NULL, $other_table_name = null)
    {
        if (isset($other_table_name)) {
            $this->_table_name = $other_table_name;
        }

        parent::__construct($data);
    }

    protected function get_iterator($result)
    {
        return new Cluster_ORM_Iterator($this, $result, $this->_table_name);
    }

    public function set_table_name($other_table_name)
    {
        $this->_table_name = $other_table_name;
    }
}

class Cluster_ORM_Iterator extends ORM_Iterator
{
    // Class attributes
    protected $class_name;
    protected $primary_key;
    protected $primary_val;

    // Database result object
    protected $result;

    protected $table_name;

    public function __construct(ORM $model, Database_Result $result, $other_table_name)
    {
        $this->table_name = $other_table_name;

        parent::__construct($model, $result);
    }

    /**
     * Iterator: current
     */
    public function current()
    {
        $current = $this->result->current();
        $current->set_table_name($this->table_name);
        return $current;
    }

    /**
     * ArrayAccess: offsetGet
     */
    public function offsetGet($offset)
    {
        if ($this->result->offsetExists($offset))
        {
            $val = $this->result->offsetGet($offset);
            $val->set_table_name($this->table_name);
            return $val;
        }
    }
}
