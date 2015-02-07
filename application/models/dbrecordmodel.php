<?
/**
  * Handles the operations regarding a database record.
  */
class dbRecordModel extends CI_Model {

	/**
	 * Constructs the object
	 */
	protected $table = NULL;
        protected $columns = array();
        protected $primaryKey = NULL;
	
	function __construct() {
		
		parent::__construct();
		// Load  the db library
		$this->load->database();
		$this->load->helper("assertion");
	}
	
	function getPrimaryKey() {
		return $this->primaryKey;
	}
	
	function getColumns() {
		return $this->columns;
	}
	/**
	 * Constructs the object
	 * 
	 * @param mixed[]|string $filters Array of filter values or an SQL string
	 * @param int $start Start of the records to return
	 * @param int $count Number of the records to return
	 * @param mixed[] $columns Array structure of the field name to retrieve.
	 * @param string $OrderBy Order By SQL string
	 * @param string $ArrayTyp Array of filter values or an SQL string

	 * @return mixed[] Returns the database records.
	 */
	function find($filters = NULL, $start = NULL, $count = NULL, $columns  = NULL, $OrderBy='',$ArrayTyp='CHRONOLOGIC') {

		$results = array();
		// Load the database library
		$this->load->database();
		if ($columns == NULL) {
			$columns = $this->columns;
		}

		// Filter could be an array of filter values or an SQL string.
		$where_clause = '';
		if ($filters) {
			if (is_string($filters)) {
				$where_clause = $filters;
			}
			elseif (is_array($filters)) {
				// Build your filter rules
				if (count($filters) > 0) {
					foreach ($filters as $field => $value) {
						$filter_list[] = " $field = '$value' ";
					}
					$where_clause = ' WHERE ' . join(' AND ', $filter_list );
				}
			}

		}
		
		//Handling of the limits
		$limit_clause = '';
		if (is_numeric($start)) {
			if ($count) {
				$limit_clause = " LIMIT $start, $count ";
			}
			else {
				$limit_clause = " LIMIT $start ";
			}
		}

		// Build up the SQL query string and run the query
		$sql = 'SELECT `' . implode('`,`',$columns) . '` FROM ' . $this->table . ' ' . $where_clause . " " . $OrderBy . " " . $limit_clause;
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {      // Go through the result set
				// Build up a list for each column from the database and place it in
				// ...the result set
				for($j = 0;$j<count($columns);$j++) {
					$query_results[$columns[$j]] = $row[$columns[$j]];
				}
				//Set the array key to be the primary key or an ascending number
				if ($ArrayTyp == 'CHRONOLOGIC') {
					$results[] = $query_results;
				} else {
					$results[$query_results[$this->primaryKey]] = $query_results;
				}
			}

		}
		return $results;
	}

	/**
	 * Return all database records
	 * 
	 * @param int $start Start of the records to return
	 * @param int $count Number of the records to return
	 * 
	 * @return mixed[]|bool Returns the database record or false.
	 */
	function findAll($start = NULL, $count = NULL) {
		
		return $this->find(NULL, $start, $count);
	}
        function get_all() {
            $query = $this->db->query('SELECT * FROM bestellungen');
            return $query;
        }

	/**
	 * Adds a new database record
	 * 
	 * @param int $idField Primary key of the record to retrieve.
	 * @param mixed[] $columns Array structure of the field name to retrieve.
	 * 
	 * @return mixed[]|bool Returns the database record or false.
	 */
	function retrieve_by_pkey($idField, $columns=NULL) {

		$results = array();
		// Load  the db library
		$this->load->database();
		if ($columns == NULL) {
			$columns = $this->columns;
		}
		$query = $this->db->query('SELECT `' . implode('`,`',$columns) . '` FROM ' . $this->table .  ' WHERE ' . $this->primaryKey . ' = "'.  $idField . '" LIMIT 1');

		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			//Created the element entries for the result array
			for($j = 0;$j<count($columns);$j++) {
				$query_results[$columns[$j]] = $row[$columns[$j]];
			}
			$results = $query_results;
		} else {
			$results = false;
		}
		return $results;
	}
        
        function result($query) {
                if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {      // Go through the result set
				// Build up a list for each column from the database and place it in
				// ...the result set
				for($j = 0;$j<count($columns);$j++) {
					$query_results[$columns[$j]] = $row[$columns[$j]];
				}
				//Set the array key to be the primary key or an ascending number
				if ($ArrayTyp == 'CHRONOLOGIC') {
					$results[] = $query_results;
				} else {
					$results[$query_results[$this->primaryKey]] = $query_results;
				}
			}

		}
		return $results;
        }

	/**
	 * Adds a new database record
	 * 
	 * @param mixed[] $data Array structure of the new record. The keys must correspond to the table field names.
	 * 
	 * @return int Returns the last inserted primary key.
	 */
	function add($data) {
                //modify escape chars
                $data = str_replace("'","`",$data);
		// Build up the SQL query string                
		$sql = $this->db->insert_string($this->table, $data);

		//Logging
                $this->log("add", $sql);
       		
                $query = $this->db->query($sql);
                /*if (assert_sql_equals($this->table, $data, 1,array($this->primaryKey)) == false) {
			return false;
		}*/
                return $this->db->insert_id();
	}

	/**
	 * Modifies a database record
	 * 
	 * @param int $keyvalue Primary key of the record to modify
	 * @param mixed[] $data Array structure of the record part to modify. The keys must correspond to the table field names.
	 * 
	 * @return bool Returns true if modifing was successful else false.
	 */
	function modify($keyvalue, $data) {
                
                //modify escape chars
                $data = str_replace("'","`",$data);
		// Build up the SQL query string
		$where = $this->primaryKey . ' = "' . $keyvalue . '"';
		$sql = $this->db->update_string($this->table, $data, $where);
                
		$query = $this->db->query($sql);
                
                //Logging
                $this->log("modify", $sql);
		return assert_sql_equals($this->table, $data, 1);
	}

        
	function modify_by_attribute($filters, $data) {
                
                //modify escape chars
                $data = str_replace("'","`",$data);
		// Build up the SQL query string
		//$where = $attribute . ' = "' . $attribute_value . '"';
                if ($filters) {
			if (is_string($filters)) {
				$where_clause = $filters;
			}
			elseif (is_array($filters)) {
				// Build your filter rules
				if (count($filters) > 0) {
					foreach ($filters as $field => $value) {
						$filter_list[] = " $field = '$value' ";
					}
					$where_clause = ' ' . join(' AND ', $filter_list );
				}
			}

		}
		$sql = $this->db->update_string($this->table, $data, $where_clause);
                
		$query = $this->db->query($sql);
                
                //Logging
                $this->log("modify_by_attribute", $sql);
		return assert_sql_equals($this->table, $data, 1);
	}
	/**
	 * Deletes a database record
	 * 
	 * @param int $idField Primary key of the record to delete
	 * 
	 * @return bool Returns true if deletion was successful else false.
	 */
	function delete_by_pkey($idField) {
                $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = "' . $idField . '" ';
		$query = $this->db->query($sql);
		
		//Logging
                $this->log("delete", $sql);
		return assert_sql_equals($this->table, array($this->primaryKey => $idField), 0);
	}
        
        function delete_by_condition($filters) {
                if ($filters) {
			if (is_string($filters)) {
				$where_clause = $filters;
			}
			elseif (is_array($filters)) {
				// Build your filter rules
				if (count($filters) > 0) {
					foreach ($filters as $field => $value) {
						$filter_list[] = " $field = '$value' ";
					}
					$where_clause = ' ' . join(' AND ', $filter_list );
				}
			}

		}
                $sql = 'DELETE FROM ' . $this->table . ' WHERE ' . $where_clause;
		$query = $this->db->query($sql);
		
		//Logging
                $this->log("delete", $sql);
		return assert_sql_equals($this->table, $filters , 0);
	}
	/**
	 * Writes a log entry
	 * 
	 * @param int $idField Primary key of the record to delete
	 * 
	 * @return int Returns the last inserted primary key.
	 */
         function log($action, $sql) {
            // Build up the SQL query string
            $data = array();
            
            $data["User"] =  $this->session->userdata('Benutzer');
            $data["Table"] = $this->table;
            $data["Action"] = $action;
            $data["SQL"] = $sql;
            $data["Date"] = date("Y-m-d H:i:s");
            $data["IP"] = $_SERVER["REMOTE_ADDR"];

            $sql = $this->db->insert_string("core_log", $data);

            $query = $this->db->query($sql);
            if (assert_sql_equals("core_log", $data, 1,array($this->primaryKey)) == false) {
		return false;
            }
            return $this->db->insert_id();
         }
        
}

?>
