<?php
$statuslist = array('AC');
function getStats($acctlist, $problist)
{
    $res = array();
    foreach ($acctlist as $acct) {
        $data = file_get_contents('https://zerojudge.tw/User/V1.0/Accepted?account=' . $acct);
        if ($data) {
            $data = json_decode($data, true);
        }
        foreach ($problist as $prob) {
            if (in_array($prob, $data['accepted'])) {
                $stats = 'AC';
            } else {
                $stats = 'NT';
            }
            $res[$acct]["res"][$prob] = $stats;
        }
    }
    return $res;
}
$userlist = array();
$temp = file_get_contents("user.txt");
$temp = explode("\r\n", $temp);
foreach ($temp as $temp2) {
    $temp2 = explode(",", $temp2);
    if (is_numeric($temp2[1])) {
        $temp2[1] = "_" . $temp2[1];
    }

    @$userlist[$temp2[1]] = array("name" => $temp2[0]);
}
$acct = array();
foreach ($userlist as $index => $temp) {
    $acct[] = $index;
    @$userlist[$index]["res"] = array();
    @$userlist[$index]["total"] = array();
    @$userlist[$index]["count"] = array("AC" => 0, "NT" => 0);
}
$prob = file_get_contents("prob.txt");
$prob = explode("\r\n", $prob);
$res = getStats($acct, $prob);
foreach ($res as $acctname => $acct) {
    $userlist[$acctname]["res"] = $acct["res"];
    $userlist[$acctname]["total"] = $acct["total"];
    foreach ($acct["res"] as $status) {
        @$userlist[$acctname]["count"][$status]++;
    }
}
foreach ($userlist as $key => $row) {
    $sort_count_AC[$key] = $row["count"]["AC"];
}
array_multisort(
    $sort_count_AC, SORT_DESC,
    $userlist, SORT_ASC
);
?>
<html>
<head>
	<title>Zerojudge-Status</title>
	<meta charset="UTF-8">
	<style>
	.AC {color: #00AA00;}
	</style>
</head>
<body>
排名原則：題單AC較多<br>
<table class=MsoTableGrid border=1 cellpadding=3 style="border-collapse:collapse;border:none"><tr>
<td>USER</td><td>LINK</td><td>NAME</td><td>AC</td>
<?php
foreach ($prob as $temp) {
    echo '<td><a href="http://zerojudge.tw/ShowProblem?problemid=' . $temp . '" target="_blank">' . $temp . '</a></td>';
}
echo '</tr>';
foreach ($userlist as $index => $user) {
    if ($index[0] == "_") {
        $index = substr($index, 1);
    }

    echo '<tr>';
    echo '<td>' . $index . '</td>';
    echo '<td><a href="http://zerojudge.tw/UserStatistic?account=' . $index . '" target="_blank">統計</a> <a href="http://zerojudge.tw/Submissions?account=' . $index . '" target="_blank">動態</a></td>';
    echo '<td>' . $user["name"] . '</td>';
    foreach ($statuslist as $status) {
        echo '<td><a href="http://zerojudge.tw/Submissions?account=' . $index . '&status=' . $status . '" target="_blank">' . $user["count"][$status] . '</a></td>';
    }
    foreach ($prob as $temp) {
        echo '<td>';
        if ($user["res"][$temp] != "NT") {
            echo '<a class="' . $user["res"][$temp] . '" href="http://zerojudge.tw/Submissions?problemid=' . $temp . '&account=' . $index . '" target="_blank">' . $user["res"][$temp] . '</a>';
        }

        echo '</td>';
    }
    echo '</tr>';
}
?>
</body>
</html>