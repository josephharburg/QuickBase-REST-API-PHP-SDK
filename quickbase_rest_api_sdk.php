<?php
 /*
 Title : QuickBase 2022 PHP REST API SDK
 Author : Joseph Harburg (josephharburg@gmail.com)
 Description : The QuickBase PHP SDK is a class for very basic interaction with the QuickBase REST API in 2022.
 The QuickBase REST API is documented here:
 https://developer.quickbase.com/

*/

 // ini_set('display_errors', 'on'); // ini setting for turning on errors
 Class QuickBaseRestApi {
  public $user_token 	= '';	// Valid user token
  public $app_token 	= ''; //Valid app token. Required.
  public $base_url    	= "https://api.quickbase.com/v1"; //The current base url
  public $realm 	= ''; //Quickbase realm string BEFORE .quickbase.com
  public $user_agent 	= ''; //User agent

  public function __construct($user_token='', $app_token = '', $realm = '', $user_agent = '', $access_token = '') {
    if($user_token) $this->user_token = $user_token;

    if($app_token) $this->app_token = $app_token;

    if($realm) $this->realm = $realm . '.quickbase.com';

    if($user_agent) $this->user_token = $user_token;
		
  }

   /**
  * Method to make the request to QuickBase API
  *
  * @param string $type_of_request The type of http request
  * @param string $endpoint The enpoint to request. Required
  * @param string $body The correctly formatted data for posting. Optional unless Using POST
  *
  * @return mixed $response
  */


  //See https://developer.quickbase.com/ for actions and endpoints
  private function make_api_request($type_of_request = 'GET', $endpoint, $body){
    $url = $this->base_url . $endpoint;
    $header_token = "QB-USER-TOKEN " . $this->user_token;
    $headers = array(
    "QB-Realm-Hostname: $this->realm",
    "User-Agent: QuickBaseRestApiApp",
	  "Authorization:". $header_token,
    "Content-Type: application/json",
  );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    if($type_of_request == 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $response = curl_exec($ch);

    //This catches errors with the cURL request and logs them. Change the executable code to fit your error logging procedures
    if(curl_errno($ch)){
      error_log("There was an error with the QuickBaseRestApi call/n". "The HTTP Error Code recieved was: ".curl_errno($ch));
    }

    return $response;
  }

  /*--------------------------------------------
                    APP METHODS
  ---------------------------------------------*/
  /**
  * Get an app
  *
  * @see https://developer.quickbase.com/operation/getApp
  *
  * @param string $app_id Required.
  *
  * @return mixed $result
  */

  public function get_an_app($app_id){
    $endpoint = "/apps/$app_id";
    $result = $this->make_api_request("GET", $endpoint);
    return $result;
  }




  /*--------------------------------------------
                    TABLE METHODS
  ---------------------------------------------*/
  /**
  * Get a table from your app
  *
  * @see https://developer.quickbase.com/operation/getTable
  *
  * @param string $table_id Required.
  * @param string $app_id Required.
  *
  * @return mixed $result
  */

  public function get_a_table($table_id, $app_id){
    $endpoint = "/tables/$table_id?appId=$app_id";
    $result = $this->make_api_request("GET", $endpoint);
    return $result;
  }


  /**
  * Method to create a table
  *
  * @see https://developer.quickbase.com/operation/createTable
  *
  * @param string $app_id Required.
  * @param string $add_table_data See below and documentation link above.Required.
  *    $update_data = array(
  *     "name": (string) Table name
  *     "description": (string) Table Description
  *     "singleRecordName": (string) Record name
  *     "pluralRecordName": (string) Plural Record Name
  *    );
  *
  * @return mixed $result
  */

  public function create_a_table($app_id, $add_table_data){
    $endpoint = "/tables?appId=$app_id";
    $body = json_encode( $add_table_data );
    $result = $this->make_api_request("POST", $endpoint, $body);
    return $result;
  }


  /**
  * Method to update a table
  *
  * @see https://developer.quickbase.com/operation/updateTable
  *
  * @param string $table_id Required.
  * @param string $app_id Required.
  * @param string $update_table_data See below and documentation link above.Required.
  *    $update_data = array(
  *     "name": (string) Table name
  *     "description": (string) Table Description
  *     "singleRecordName": (string) Record name
  *     "pluralRecordName": (string) Plural Record Name
  *    );
  *
  * @return mixed $result
  */

  public function update_a_table($table_id, $app_id, $update_table_data){
    $endpoint = "/tables/$table_id?appId=$app_id";
    $body = json_encode( $update_table_data );
    $result = $this->make_api_request("POST", $endpoint, $body);
    return $result;
  }

  /*--------------------------------------------
                    REPORT METHODS
  ---------------------------------------------*/

  /**
  * Get all reports from a table
  *
  * @see https://developer.quickbase.com/operation/getTableReports
  *
  * @param string $table_id Required.
  *
  * @return mixed $result
  */

  public function get_reports_for_a_table($table_id){
    $endpoint = "/reports?tableId=$table_id";
    $result = $this->make_api_request("GET", $endpoint);
    return $result;
  }

  /**
  * Get a single report
  *
  * @see https://developer.quickbase.com/operation/getReport
  *
  * @param string $report_id Required.
  * @param string $table_id Required.
  *
  * @return mixed $result
  */

  public function get_single_report( $report_id ,$table_id ){
    $endpoint = "/reports/$report_id?tableId=$table_id";
    $result = $this->make_api_request("GET", $endpoint);
    return $result;
  }


    /*--------------------------------------------
                      RECORD METHODS
    ---------------------------------------------*/

    /**
    * Make a query for record data
    *
    * @see https://developer.quickbase.com/operation/runQuery
    *
    * @param string $table_id The table to query. Required
    * @param array  $select Array of field ids. Required
    * @param string $where A Quickbase query language formatted bracket enclosed string see documentation link above. Required
    *   $where = {3.CT.'string'}
    * @param array $sort_by A multidimensional array correctly formatted see below. See documentation link above. Optional
    *    $sort_by = array(
    *       array(
    *         "fieldId" => (int|string) The field id to sort by.
    *         "order" => "ASC|DESC" (string) which order parameter.
    *       ) ...add as many sorting parameters as allowed
    *     )
    *
    * @param array $group_by A multidimensional array correctly formatted. See documentation link above. Optional
    *    $group_by = array(
    *       array(
    *         "fieldId" => (int|string) The field id to group. Required
    *         "grouping" => "ASC|DESC|equal values" (string) which grouping. Required
    *       )
    *     )
    *
    * @param array $options An array of options. See documentation link above. Optional
    *    $options = array(
    *         "skip" => (int) Number of records to skip. Optional
    *         "compareWithAppLocalTime", => (bool) See documentation. Optional
    *         "top" => (bool) Number of records to display. Optional
    *     )
    *
    * @return mixed $result
    */

    public function query_for_data($table_id, $select, $where, $sort_by = '', $group_by = '', $options = ''){
      $endpoint = "/records/query";
      $select = json_encode( $select );
      $where = ($where) ? $where: '';
      $sort_by = ($sort_by) ? json_encode( $sort_by ): "[{}]";
      $group_by = ($group_by) ? json_encode( $group_by ): "[{}]";
      $options = ($options) ? ',"options":'.json_encode( $options ): "";
      $body = "{ \"from\": \"$table_id\",\"select\": $select,\"where\" : \"$where\",\"sortBy\": $sort_by,\"groupBy\": $group_by $options }";
      $result = $this->make_api_request("POST", $endpoint, $body);
      return $result;
    }

    /**
    * Update or create record(s)
    *
    * @see https://developer.quickbase.com/operation/upsert
    *
    * @param string $table_id
    * @param array $values_to_update a multidimensional array see below
    *     $values_to_update = array(
    *       array(
    *          (string) table primary key field id in quotes. Required =>
    *                    array("value" => (int|string) primary key id to update or new id. Required),
    *          (string) field id value in quotes =>
    *                    array("value" => (int|string) value for field),
    *          (string) field id value in quotes =>
    *                    array("value" => (int|string) value for field),
    *            ... put as may key value pairs that you need
    *        ),
    *       array(
    *           (string) another table primary key field id in quotes =>
    *                  array("value" => (int|string) another record primary key id),
    *           (string) field id value in quotes =>
    *                  array("value" => (int|string) value for field),
    *        ),
    *        ... put as many records as you need
    *     );
    * @param array $fields_to_return A list of field ids to return after update. Optional
    *
    * @return mixed $result
    */

    public function update_or_create_records($table_id, $values_to_update, $fields_to_return = array(3)){
      $endpoint = "/records";
      $data = json_encode( $values_to_update );
      $fields_to_return = json_encode( $fields_to_return );
      $body = "{
        \"to\": \"$table_id\",
        \"data\": $data,
        \"fieldsToReturn\": $fields_to_return
      }";
      $result = $this->make_api_request("POST", $endpoint, $body);
      return $result;
    }
}
?>
