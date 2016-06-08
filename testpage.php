<?php
$git_url = "https://github.com/rakesh635/spring-petclinic.git";
$gitrepo_explode = explode("/",$git_url);
echo "https://api.github.com/repos/".$gitrepo_explode[3]."/".chop($gitrepo_explode[4],'.git')."/contents";
$ch = curl_init();
/*curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/".$gitrepo_explode[3]."/".chop($gitrepo_explode[4],'.git')."/contents");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_VERBOSE, "false"); 
//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($ch, CURLOPT_HEADER, 1);
//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
//curl_setopt($ch, CURLOPT_USERPWD, "rakesh635:12bluestars");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "authorization: Basic cmFrZXNoNjM1OjEyYmx1ZXN0YXJz",
    "cache-control: no-cache",
    "postman-token: 3fb37fdd-e0d5-ee2b-5dff-ffcaf0528d43"
  ));*/
  
curl_setopt_array($ch, array(
CURLOPT_URL => "https://api.github.com/repos/rakesh635/spring-petclinic/contents",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "GET",
CURLOPT_HTTPHEADER => array(
"authorization: Basic cmFrZXNoNjM1OjEyYmx1ZXN0YXJz",
"cache-control: no-cache",
"postman-token: 3fb37fdd-e0d5-ee2b-5dff-ffcaf0528d43"
),
));
  
//curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$dataoutput = curl_exec($ch);
$dataoutput1 = json_decode($dataoutput,true);
curl_close($ch);
echo("<pre>");print_r($dataoutput1);echo("</pre>");
echo $dataoutput;




?>