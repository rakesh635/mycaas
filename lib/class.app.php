<?php
class appClass
{
	public function mysqlconnect($servername, $username, $password, $dbname='')
	{
		$conn = mysqli_connect($servername, $username, $password, $dbname);
		if (!$conn) {
				die("Connection failed: " . mysqli_connect_error());
		}
		return $conn;
	}

	public function applist()
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$sql = "SELECT app_id,app_name,git_url,git_url,status,created_date,updated_date,completed_date FROM application";
		$result = $dbObj->query($sql);
		$resultArray = array();
		if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
						array_push($resultArray,array("app_id"=>$row["app_id"],"app_name"=>$row["app_name"],"git_url"=>$row["git_url"],"status"=>$row["status"],"created_date"=>$row["created_date"],"updated_date"=>$row["updated_date"],"completed_date"=>$row["completed_date"]));
				}
		}
		$dbObj->close();
		return $resultArray;
	}
	
	public function appadd()
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$cur_datetime = date('Y-m-d h:i:s');
		$sql = "INSERT INTO application (app_name, git_url, git_uname, git_pswd, created_date) VALUES ('".$_POST['app_name']."', '".$_POST['git_repo']."', '".$_POST['git_uname']."', '".$_POST['git_pswd']."', '".$cur_datetime."')";

		if($dbObj->query($sql))
		{
			$appid = $dbObj->insert_id;
			/* Create a new connection */
			$cnn = new AMQPConnection();

			// set the hostname
			$cnn->setHost('192.168.54.193');
			$cnn->setLogin('test');
			$cnn->setPassword('test');
			$cnn->connect();

			/* Check that connection is working */
			if (!$cnn->isConnected()) {
					echo "Connected to the broker in git verify \o/";
			}

			$cnnChannel = new AMQPChannel($cnn);
			$cnnExchange = new AMQPExchange($cnnChannel);

			$cnnExchange->setName('gitverify');
			$cnnExchange->setType(AMQP_EX_TYPE_DIRECT);
			$cnnExchange->setFlags(AMQP_DURABLE);
			$cnnExchange->declare();

			$cnnQueue1 = new AMQPQueue($cnnChannel);
			$cnnQueue1->setName('gitverify_queue');
			$cnnQueue1->setFlags(AMQP_DURABLE);
			$cnnQueue1->declare();
			$cnnQueue1->bind('gitverify', 'dco.marker');
			$cnnExchange->publish($appid, 'dco.marker');
			return '1';
		}
		else
		{
			echo ("Error Inserting Application data");
			die();
		}
	}
	
	public function verifygit($appId)
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$sql = "SELECT app_id,git_url,git_uname,git_pswd FROM application where app_id=".$appId;
		$result = $dbObj->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$gitrepo_explode = explode("/",$row['git_url']);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/".$gitrepo_explode[3]."/".chop($gitrepo_explode[4],'.git')."/contents");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, "false");
			curl_setopt($ch, CURLOPT_USERAGENT, "test");
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $row['git_uname'].":".$row['git_pswd']);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			$dataoutput = curl_exec($ch);
			$dataoutput = json_decode($dataoutput,true);
			curl_close($ch);

			if(isset($dataoutput['message']) && $dataoutput['message']=="Not Found")
			{
					return "Git User Credential or Repo URL is wrong";
			}

			foreach($dataoutput as $repofiles)
			{
					if($repofiles['name']=='pom.xml')
					{
							return "true";
					}
			}
			return "No pom.xml for maven build available in repo root directory";
		}
	}
	 public function builderror($app_id,$error_msg)
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$cur_datetime = date('Y-m-d h:i:s');
		$sql = "UPDATE application set status=4, iffailuremsg='".$error_msg."' where app_id=".$app_id;
		$sql1 = "DELETE FROM application_status where app_id=".$app_id;
		if($dbObj->query($sql) && $dbObj->query($sql1))
		{
				return true;
		}
	}

	public function buildstatusupdate($app_id,$field)
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$cur_datetime = date('Y-m-d h:i:s');
		$sql = "UPDATE application_status set ".$field."=1 where app_id=".$app_id;

		if($dbObj->query($sql))
		{
				return true;
		}
	}

	public function cicredentialadd($app_id)
	{
		$dbObj = $this->mysqlconnect("localhost","root","root","testdb");
		$sql = "SELECT app_id,app_name,git_url,git_uname,git_pswd FROM application where app_id=".$appId;
		$result = $dbObj->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			if($this->curlcredential($row['git_uname'],$row['git_pswd'],$row['app_name'],$row['app_id']) == 200)
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "http://192.168.54.205:8080/credential-store/domain/_/credential/cc$appId/");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_VERBOSE, "false");
				curl_setopt($ch, CURLOPT_USERAGENT, "test");
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_USERPWD, $row['git_uname'].":".$row['git_pswd']);
				curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				$dataoutput = curl_exec($ch);
				$dataoutput = json_decode($dataoutput,true);
				curl_close($ch);
			}	
		}
	}
	
	public function curlcredential($gitname,$gitpswd,$appname,$appid){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://192.168.54.205:8080/credential-store/domain/_/createCredentials");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, "false");
		curl_setopt($ch, CURLOPT_USERAGENT, "test");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "admin:admin");
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json'                                                                    
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'json={"": "0", "credentials": {"scope": "GLOBAL","username": "'.$gitname.'", "password": "'.$gitpswd.'", "description": "'.$appname.'" , "id": "c'.$appid.'", "stapler-class": "com.cloudbees.plugins.credentials.impl.UsernamePasswordCredentialsImpl"}}');
		$dataoutput = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE)
		curl_close($ch);
		return $http_status;
		
	}
}
?>
