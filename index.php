<?php
ini_set("display_errors",1);
function httpRequest( $url , $post = null , $usepost =true )
{
	if( is_array($post) )
	{
		ksort( $post );
		$post = http_build_query( $post );
	}
	
	$ch = curl_init();
	curl_setopt( $ch , CURLOPT_URL , $url );
	curl_setopt( $ch , CURLOPT_ENCODING, "UTF-8" );
	if($usepost)
	{
		curl_setopt( $ch , CURLOPT_POST, true );
		curl_setopt( $ch , CURLOPT_POSTFIELDS , $post );
	}
	curl_setopt( $ch , CURLOPT_RETURNTRANSFER , true );
	curl_setopt ($ch , CURLOPT_COOKIEFILE, "temp.txt" );
	curl_setopt ($ch , CURLOPT_COOKIEJAR , "temp.txt" );
	
	$data = curl_exec($ch);
	curl_close($ch);
	if(!$data)
	{
		return false;
	}
	return $data;
}
$statuslist=array("AC","WA","TLE","MLE","OLE","RE","CE");
function Login(){
	require("config.php");
	$cont = httpRequest('zerojudge.tw/Login',null,false);
        preg_match('/name="token" value="([^"]+)/',$cont,$res);
        $token = $res[1];
        $cont = httpRequest('zerojudge.tw/Login',array(
                'account' => $config['zj_id'] ,
                'passwd'  => $config['zj_pwd'] ,
                'returnPage' => '/Index' ,
                'token'   => $token
        ));
	return $cont;
}
function getStats($acctlist,$problist){
	global $statuslist;
	Login();
	$res=array();
	foreach ($acctlist as $acct){
		$cont = httpRequest("zerojudge.tw/UserStatistic?account=".($acct[0]=="_"?substr($acct,1):$acct),false,false);
		$cont = str_replace(array("\r\n","\t","  "),"",$cont);
		foreach ($statuslist as $status){
			preg_match('/status='.$status.'">(.*?)<\/a>/',$cont,$temp);
			$res[$acct]["total"][$status]=$temp[1];
		}
		foreach ($problist as $prob){
			$start=strpos($cont,"?problemid=".$prob);
			$end  =strpos($cont,">".$prob."</a>");
			$html =substr($cont,$start,$end-$start);
			if(strpos($html,'"acstyle"')){
				$stats = "AC";
			} else if(strpos($html,'color: #666666; font-weight: bold;')){
				$stats = "NA";
			} else if(strpos($html,'color: #666666')) {
				$stats = "NT";
			} else {
				$stats = "ERR";
			}
			$res[$acct]["res"][$prob]=$stats;
		}
	}
	return $res;
}
$userlist=array();
$temp=file_get_contents("user.txt");
$temp=str_replace(" ","\t",$temp);
$temp=explode("\r\n",$temp);
foreach($temp as $temp2){
	$temp2=explode("\t",$temp2);
	if(is_numeric($temp2[1]))$temp2[1]="_".$temp2[1];
	@$userlist[$temp2[1]]=array("name"=>$temp2[0]);
}
$acct=array();
foreach ($userlist as $index => $temp){
	$acct[]=$index;
	@$userlist[$index]["res"]=array();
	@$userlist[$index]["total"]=array();
	@$userlist[$index]["count"]=array("AC"=>0,"NA"=>0,"NT"=>0,"ERR"=>0);
}
$prob=file_get_contents("prob.txt");
$prob=explode("\r\n",$prob);
$res=getStats($acct,$prob);
foreach ($res as $acctname => $acct){
	$userlist[$acctname]["res"]=$acct["res"];
	$userlist[$acctname]["total"]=$acct["total"];
	foreach ($acct["res"] as $status){
		@$userlist[$acctname]["count"][$status]++;
	}
}
/*function cmp($a, $b) { 
    if ($a["count"]["AC"] == $b["count"]["AC"]){
        if ($a["count"]["NA"] == $b["count"]["NA"]){
			 if ($a["total"]["AC"] == $b["total"]["AC"]){
				return 0;
			}
			return ($a["total"]["AC"] < $b["total"]["AC"] ? 1 : -1);
		}
		return ($a["count"]["NA"] < $b["count"]["NA"] ? 1 : -1);
    }
    return ($a["count"]["AC"] < $b["count"]["AC"] ? 1 : -1);
}*/
foreach ($userlist as $key => $row){
    $sort_count_AC[$key]=$row["count"]["AC"];
    $sort_count_NA[$key]=$row["count"]["NA"];
    $sort_total_AC[$key]=$row["total"]["AC"];
    $sort_total_CE[$key]=$row["total"]["CE"];
    $sort_total_WA[$key]=$row["total"]["WA"];
}
array_multisort(
$sort_count_AC,SORT_DESC,
$sort_count_NA,SORT_ASC,
$sort_total_AC,SORT_DESC,
$sort_total_CE,SORT_ASC,
$sort_total_WA,SORT_ASC,
$userlist 
);
consolelog($userlist );
//uasort($userlist, 'cmp');
?>
<style>
.AC {
	color: #00AA00;
}
.NA {
	color: #FF0000;
}
.NT {
	color: #FFFFFF;
}
</style>
排名原則：題單AC較多、題單NA較少、全部AC較多、全部CE較少、全部WA較少<br>
<table class=MsoTableGrid border=1 cellpadding=3 style="border-collapse:collapse;border:none"><tr>
<td>USER</td><td>LINK</td><td>AC</td><td>WA</td><td>TLE</td><td>MLE</td><td>OLE</td><td>RE</td><td>CE</td><td>NAME</td><td>AC</td><td>NA</td>
<?php
foreach ($prob as $temp){
	echo '<td><a href="http://zerojudge.tw/ShowProblem?problemid='.$temp.'" target="_blank">'.$temp.'</a></td>';
}
echo '</tr>';
foreach ($userlist as $index => $user){
	if($index[0]=="_")$index=substr($index,1);
	echo '<tr>';
	echo '<td>'.$index.'</td>';
	echo '<td><a href="http://zerojudge.tw/UserStatistic?account='.$index.'" target="_blank">統計</a> <a href="http://zerojudge.tw/Submissions?account='.$index.'" target="_blank">動態</a></td>';
	foreach ($statuslist as $status){
		echo '<td><a href="http://zerojudge.tw/Submissions?account='.$index.'&status='.$status.'" target="_blank">'.$user["total"][$status].'</a></td>';
	}
	echo '<td>'.$user["name"].'</td>';
	echo '<td>'.$user["count"]["AC"].'</td>';
	echo '<td>'.$user["count"]["NA"].'</td>';
	foreach ($prob as $temp){
		echo '<td>';
		if($user["res"][$temp]!="NT")echo '<a class="'.$user["res"][$temp].'" href="http://zerojudge.tw/Submissions?problemid='.$temp.'&account='.$index.'" target="_blank">'.$user["res"][$temp].'</a>';
		echo '</td>';
	}
	echo '</tr>';
}
?>
