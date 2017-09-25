<?php
include_once("$_SERVER[DOCUMENT_ROOT]/appconstants.php");

/*
 * Function: Connectdb() 
 * Description: Establish a connection to the database.
 */

function Connectdb() {
    include 'mysqlconstants.php';
    $db_handle = mysqli_connect(DATABASE_HOST, DATABASE_USER_NAME, DATABASE_PASSWORD);
    if (!$db_handle) {
        die('Error!!! Could not connect to database' . mysqli_error($db_conn));
    }
    //$db_handle->set_charset("utf8");
    /* Store the handle in a session variable. */
    $_SESSION['dbHandle'] = $db_handle;
    return("connected");
}

/*
 * Function: Opendb()
 * Description: Open a connection to the database.
 */

function Opendb() {
    include 'mysqlconstants.php';
    $db_handle = $_SESSION['dbHandle'];
    if (!$db_handle) {
        die('Error!! Database not connected' . mysqli_error($db_conn));
    } /* if */
    $error_status = mysqli_select_db($db_handle, DATABASE_NAME);
    if (!$error_status) {
        die('Error!! Could not open database' . mysqli_error($db_conn));
    } /* if */
    return ("opened");
}

/*
 * Function read data with particular condition
 * @variables:  $tab_name = table name
 *              $cond = condition to fetch the data
 *              $select = by default *, otherwise given column or columns
 * @return:     associative array with as key=>value pair
 */
function fetch($tab_name='',$cond='',$select='*'){
    include 'mysqlconstants.php';
    if($tab_name){
        $sql_query = "SELECT $select FROM $tab_name WHERE 1=1";
        if($cond){
            $sql_query.= $cond;
        }
        $query_result = mysqli_query($db_conn,$sql_query);
        if(!$query_result){
            die('Data fetching error: '.mysqli_error($db_conn));
        }
        $results = array();
        while($row = mysqli_fetch_assoc($query_result)) {
            $results[] = $row;
        }
        return $results;
    }else{
        $results=array('error'=>'Select any table first');
        return $results;
    }
}

/*
 * Insert into table query
 * @variables:  $tab_name = table name
 *              $data_arr = array of column name of table and value of particular column i.e. array(column=>value)
 * @return:     insert id
 */
function insert($tab_name,$data_arr=array()){
    include 'mysqlconstants.php';
    if($tab_name){
        if($data_arr){
            $columns = '';
            $values = '';
            foreach ($data_arr as $key => $value) {
                $columns .= $key.',';
                $values .= "'".$value."',";
            }
            $columns = substr($columns, 0, -1);
            $values = substr($values, 0, -1);
            $sql_query = "INSERT INTO $tab_name ($columns)VALUES ($values)";
            $query_result = mysqli_query($db_conn,$sql_query);
            if($query_result){
                $last_id = mysqli_insert_id($db_conn);
                return $last_id;
            }else{
                 die('Data inserting error: '.mysqli_error($db_conn));
            }
        }
        $results=array('error'=>'Data is missing');
        return $results;
    }else{
        $results=array('error'=>'Select any table first');
        return $results;
    }
}

/*
 * Update data of a table
 * @variables:      $tab_name = table name
 *                  $data_arr = array of column name of table and value of particular column i.e. array(column=>value)
 *                   $cond = condition to fetch the data
 * @return:     true on success or false
 */
function update($tab_name,$data_arr=array(),$cond){
    include 'mysqlconstants.php';
    if($tab_name){
        $sql_query = "UPDATE $tab_name SET ";
        foreach ($data_arr as $key => $value) {
            $sql_query .= $key."='".$value."',";
        }
        $sql_query = substr($sql_query, 0, -1);
        $sql_query.=" WHERE 1=1";
        if($cond){
            $sql_query.= $cond;
        }
        $query_result = mysqli_query($db_conn,$sql_query);
        if(!$query_result){
            die('Data updating error: '.mysqli_error($db_conn));
        }
        return $query_result;
    }else{
        $results=array('error'=>'Select any table first');
        return $results;
    }
}

/*
 * Count no of row with given condition
 * @variables:      $tab_name = table name
 *                  $cond = condition to count the data
 * @return:         no of row           
 */
function count_row($tab_name,$cond=''){
    include 'mysqlconstants.php';
    if($tab_name){
        $sql_query = "SELECT * FROM $tab_name WHERE 1=1";
        if($cond){
            $sql_query.= "$cond";
        }
        $query_result = mysqli_query($db_conn,$sql_query);
        $row_count=mysqli_num_rows($query_result);
        return $row_count;
    }else{
        $results=array('error'=>'Select any table first');
        return $results;
    } 
}


/*
 * Function delete data with particular condition
 * @variables:  $tab_name = table name
 *              $cond = condition to fetch the data
 * @return:     true or false
 */
function delete($tab_name='',$cond=''){
    include 'mysqlconstants.php';
    if($tab_name){
        $sql_query = "DELETE FROM $tab_name WHERE 1=1";
        if($cond){
            $sql_query.= "$cond";
        }
        $query_result = mysqli_query($db_conn,$sql_query);
        if(!$query_result){
            die('Data fetching error: '.mysqli_error($db_conn));
        }
        $no_of_rows = mysqli_affected_rows($db_conn);
        return $no_of_rows;
    }else{
        $results=array('error'=>'Select any table first');
        return $results;
    }
}
