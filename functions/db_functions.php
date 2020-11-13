<?php
	include("constants.php");
	require_once("db_connect.php");
	require_once("../vendor/emcconville/google-map-polyline-encoding-tool/src/Polyline.php");
	class DbQueries extends DbConnect
	{
		
		function __construct()
		{
			$this->connect();
		}

		public function secure_database($value){
			$value = mysqli_real_escape_string($this->conn, $value);
			return $value;
		}

		public function unique_id_generator($data){
			$data = $this->secure_database($data);
			$newid = md5(uniqid().time().rand(11111,99999).rand(11111,99999).$data);
			return $newid;
		}

		public function check_row_exists_by_one_param($table,$param,$value){
			$table = $this->secure_database($table);
			$param = $this->secure_database($param);
			$value = $this->secure_database($value);
			$sql = "SELECT * FROM `$table` WHERE `$param` = '$value'";
			$query = mysqli_query($this->conn, $sql);
			$num = mysqli_num_rows($query);
			if($num > 0 ){
				return true;
			}else{
				return false;
			}
		}

		public function get_rows_from_one_table($table){
        
        $table = $this->secure_database($table);
        $sql = "SELECT * FROM `$table` ORDER BY `date_created` DESC";
        $query = mysqli_query($this->conn, $sql);
        $num = mysqli_num_rows($query);
       if($num > 0){
            while($row = mysqli_fetch_array($query)){
                $row_display[] = $row;
                }
            return $row_display;
          }
          else{
             return null;
          }
		}

		public function get_one_row_from_one_table($table,$param,$value){
	        $table = $this->secure_database($table);
	        $param = $this->secure_database($param);
	        $value = $this->secure_database($value);
	        $sql = "SELECT * FROM `$table` WHERE `$param` = '$value'";
	        $query = mysqli_query($this->conn, $sql);
	        $num = mysqli_num_rows($query);
			if($num > 0){
				$row = mysqli_fetch_array($query);
				return $row;
			}
			else{
				return null;
			}
		}

		public function get_rows_from_one_table_by_id($table,$theid,$idvalue){
        $table = $this->secure_database($table);
        $theid = $this->secure_database($theid);
        $idvalue = $this->secure_database($idvalue);
        $sql = "SELECT * FROM `$table` WHERE `$theid`='$idvalue'";
        $query = mysqli_query($this->conn, $sql);
        $num = mysqli_num_rows($query);
       	if($num > 0){
            while($row = mysqli_fetch_array($query)){
               $row_display[] = $row;
            }
            return $row_display;
        }
          else{
            return null;
          }
		}

		public function update_with_one_param($table,$param,$value,$new_value_param,$new_value){
			$table = $this->secure_database($table);
			$value = $this->secure_database($value);
			$param = $this->secure_database($param);
			$new_value_param = $this->secure_database($new_value_param);
			$new_value = $this->secure_database($new_value);

			$sql = "UPDATE `$table` SET `$new_value_param`='$new_value' WHERE `$param` = '$value'";
			$query = mysqli_query($this->conn, $sql)or die(mysqli_error($this->conn));

			if(mysqli_affected_rows($this->conn)){
				return true;
			}
			else{
				return false;
			}
		}

		
		public function returnResponse($code, $data, $response=null){
			//header("content-type: application/json");
			$response = json_encode(['response'=>["status"=>$code, "message"=>$data, "data"=>$response]]);
			echo $response;
		}

		public function validateParameter($fieldName, $value, $dataType, $required=true){

			if($required == true && empty($value) == true){
				$this->returnResponse(VALIDATE_PARAMETER_REQUIRED, $fieldName." parameter is required.");
			}
			else{
				switch ($dataType) {
					case BOOLEAN:
						if(!is_bool($value)){
							$this->returnResponse(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ".$fieldName);
						}
						break;

					case INTEGER:
						if(!is_numeric($value)){
							$this->returnResponse(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ".$fieldName);
						}
						break;

					case STRING:
						if(!is_string($value)){
							$this->returnResponse(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ".$fieldName);
						}
						break;
					
					default:
						$this->returnResponse(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ".$fieldName);
						break;
				}
			}
			return $value;


		}

		public function generate_promo_code($event_venue, $amount, $radius, $code_validity_duration){
			session_start();	
			$event_venue = $this->secure_database($event_venue);
			$amount = $this->secure_database($amount);
			$radius = $this->secure_database($radius);
			$code_validity_duration = $this->secure_database($code_validity_duration);
			$unique_id = $this->unique_id_generator($event_venue.$amount);
			$check = $this->check_row_exists_by_one_param('promo_code','event_venue',$event_venue, 'amount', $amount);
			$code = "SAFE_BODA".rand(0000, 9999);
			if($event_venue == "" || $amount == "" || $radius == "" || $code_validity_duration == ""){
			    $this->returnResponse(VALIDATE_PARAMETER_REQUIRED, "Empty field(s) found");
			}
			else if($check === true){
		    	$this->returnResponse(CODE_EXISTS, "Code already generated from this event");
		  	}
			else{
				$insert_code = "INSERT INTO promo_code SET `unique_id` = '$unique_id', `code` = '$code', `event_venue` = '$event_venue', `amount` = '$amount', `radius` = '$radius', `code_validity_duration` = '$code_validity_duration', `date_created` = now()";
				$insert_code_query = mysqli_query($this->conn, $insert_code) or die(mysqli_error($this->conn));
			    if($insert_code_query){
			      $_SESSION['code'] = $code;
			      $_SESSION['start'] = time();
			      $_SESSION['expire'] = $_SESSION['start'] + (60*$code_validity_duration);
			      $this->returnResponse(SUCCESS_RESPONSE, "Code generated successfully", $code);
			    }
			    else{
			    	http_response_code(400);
					$this->returnResponse(DB_ERROR, "Error generating code");
				}
			}
		}
		public function get_active_code(){
			$data = [];
			$get_active_codes = $this->get_rows_from_one_table_by_id('promo_code', 'active_status', 1);
			array_push($data, $get_active_codes);
			if($get_active_codes == null){
				$this->returnResponse(DB_ERROR, "No records found");
			}
			else{
				$this->returnResponse(SUCCESS_RESPONSE, "Active codes generated successfully", $data);
			}
		}

		public function get_all_code(){
			$data = [];
			$get_all_code = $this->get_rows_from_one_table('promo_code');
			array_push($data, $get_all_code);
			if($get_all_code == null){
				http_response_code(400);
				$this->returnResponse(DB_ERROR, "No records found");
			}
			else{
				$this->returnResponse(SUCCESS_RESPONSE, "All codes generated successfully", $data);
			}
		}

		public function deactivate_code($code){
			$code = $this->secure_database($code);
			if($code == ""){
			    $this->returnResponse(VALIDATE_PARAMETER_REQUIRED, "Empty field(s) found");
			}else{
				$sql = $this->update_with_one_param('promo_code','code',$code,'active_status', 0);
				if($sql){
					$this->returnResponse(SUCCESS_RESPONSE, "Code deactivated successfully");
				}
				else{
					http_response_code(400);
					$this->returnResponse(DB_ERROR, "Error configuring code");
				}
			}
		}

		public function configure_radius($code, $radius){
			$code = $this->secure_database($code);
			if($code == ""){
			    $this->returnResponse(VALIDATE_PARAMETER_REQUIRED, "Empty field(s) found");
			}else{
				$update_code = "UPDATE promo_code SET `radius` = '$radius',`date_created` = now() WHERE `code` = '$code'";
				$update_code_query = mysqli_query($this->conn, $update_code) or die(mysqli_error($this->conn));
				if(mysqli_affected_rows($this->conn)){
					$this->returnResponse(SUCCESS_RESPONSE, "Radius configured successfully");
				}
				else{
					http_response_code(400);
					$this->returnResponse(DB_ERROR, "Error configuring code");
				}
			}
		}

		public function calcluate_geocode($address){
			$curl = curl_init();
			$data = [];
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://maps.googleapis.com/maps/api/geocode/json?address=".$address."&key=AIzaSyCWeP0XSZ6oIJMsIlhnlaWHx_HEudG_P3M",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			));

			$response = curl_exec($curl);

			curl_close($curl);
			// echo $response;
			$decode_response = json_decode($response, true);
			foreach ($decode_response['results'] as  $value) {
				// $result = [
				// 	"latitude"=>$value['geometry']['location']['lat'],
				// 	"longitude"=>$value['geometry']['location']['lng']
				// ];
				$latitude = $value['geometry']['location']['lat'];
				$longitude = $value['geometry']['location']['lng'];
				//array_push($data, $result);
			}
			return json_encode(["status"=>200, "data"=>["latitude"=>$latitude, "longitude"=>$longitude]]);
		}

		public function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {  
		  $earth_radius = 6371;

		  $dLat = deg2rad($latitude2 - $latitude1);  
		  $dLon = deg2rad($longitude2 - $longitude1);  

		  $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);  
		  $c = 2 * asin(sqrt($a));  
		  $d = $earth_radius * $c;  

		  return $d;  
		}


		public function check_promo_code($code, $origin, $destination){
			session_start();
			$code = $this->secure_database($code);
			$origin = $this->secure_database($origin);
			$destination = $this->secure_database($destination);
			$origin = preg_replace('/\s+/', '+', $origin);
			$destination = preg_replace('/\s+/', '+', $destination);
			$get_event_address = $this->get_one_row_from_one_table('promo_code', 'code', $code);
			$event_venue = preg_replace('/\s+/', '+', $get_event_address['event_venue']);
			$radius = $get_event_address['radius'];
			if(!isset($_SESSION['code'])){
				http_response_code(400);
				$this->returnResponse(DB_ERROR, "Code expired");
				exit();
			}
			else if($get_event_address['active_status'] == 0){
				http_response_code(400);
				$this->returnResponse(DB_ERROR, "Code has been deactivated");
			}
			else{
				$now = time();
				if ($now > $_SESSION['expire']) {
		            session_destroy();
		            http_response_code(400);
		            $this->returnResponse(DB_ERROR, "Code expired");
		            exit();
		        }
		        else{
		        	$calculate_geocode = $this->calcluate_geocode($origin);
		        	$decode_calculate_geocode = json_decode($calculate_geocode, true);
		        	$latitude1 = $decode_calculate_geocode['data']['latitude'];
		        	$longitude1 = $decode_calculate_geocode['data']['longitude'];


		        	$calculate_geocode1 = $this->calcluate_geocode($destination);
		        	$decode_calculate_geocode1 = json_decode($calculate_geocode1, true);
		        	$latitude2 = $decode_calculate_geocode1['data']['latitude'];
		        	$longitude2 = $decode_calculate_geocode1['data']['longitude'];

		        	$calculate_geocode2 = $this->calcluate_geocode($event_venue);
		        	$decode_calculate_geocode2 = json_decode($calculate_geocode2, true);
		        	$latitude3 = $decode_calculate_geocode2['data']['latitude'];
		        	$longitude3 = $decode_calculate_geocode2['data']['longitude'];

		        	$get_distance1 = $this->getDistance($latitude1, $longitude1, $latitude3, $longitude3);
		        	$get_distance1;

		        	$get_distance2 = $this->getDistance($latitude2, $longitude2, $latitude3, $longitude3);
		        	//echo $get_distance2;

					$points = array(
						array($latitude1, $longitude1),
						array($latitude2, $longitude2)
					);

					$encoded = Polyline::encode($points);

		        	if($get_distance1 <= $radius || $get_distance2 <= $radius){
		        		$encoded_polyline = ["polyline"=>$encoded];
		        		array_push($get_event_address, $encoded_polyline);
		        		$this->returnResponse(SUCCESS_RESPONSE, "Promo code is valid", $get_event_address);
		        		//return json_encode(["status"=>200, "msg"=>"Promo code is valid", "data"=>$get_event_address]);
		        	}
		        	else{
		        		http_response_code(400);
		        		$this->returnResponse(DB_ERROR, "Promo code is invalid");
		        		//return json_encode(["status"=>400, "msg"=>"Promo code is invalid"]);
		        	}
		        }
		    }
		}
	}
?>