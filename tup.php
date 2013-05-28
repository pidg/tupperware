<pre><?php

/*

	Tupperware by @tarasyoung

	Extracts all active Twitter followers from ThinkUp's database and
	outputs their details as a table

*/


// +- Settings ---------------------------------------------------------------+

  $dbhost="localhost";	// MySQL server
  $dbuser="taras";		// MySQL username
  $dbpass="password";	// MySQL password
  $dbname="taras";		// MySQL database
  $prefix = "tu";		// ThinkUp table prefix (e.g. tu_users would be "tu")
  $user = "tarasyoung";	// Your Twitter username

// +--------------------------------------------------------------------------+

function create_table($arr)
{
	// Creates a table from an array

	// Header row
	$keys = array_keys($arr[0]);
	foreach ( $keys as $k ) $t = $t . "  <td>$k</td>\n";
	echo "<table>\n <tr>\n$t </tr>\n";

	// Output all rows
	foreach ( $arr as $a )
	{
		$t="";
		foreach ( $keys as $k ) $t = $t . "  <td>" . $a[$k] . "</td>\n";
		echo " <tr>$t </tr>\n";
	}

	echo "</table>\n";
}

function opendb()
{ 
	// Open MySQL database
	global $conn, $dbhost, $dbuser, $dbpass, $dbname;
	$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die("Fail");
	mysql_select_db($dbname);
}

function closedb() { global $conn; mysql_close($conn); }

function query($query)
{
	// Performs a query on the database
	opendb();
		$result  = mysql_query($query) or die("Fail");
		while($row = mysql_fetch_assoc($result)) $returned_data[] = $row;
	closedb();

	return $returned_data;
}

$conn='';

// Find 'me' based on $user:
$q = query("SELECT * FROM " . $prefix . "_users WHERE user_name LIKE '$user';") or exit("User not found.");
$me = $q[0];

// Find everyone who follows that user:
$everyone = query("SELECT * FROM " . $prefix . "_follows WHERE active = '1' AND user_id = '" . $me["user_id"] . "';") or exit("No active followers found.");

// Cycle through the results and get each follower's details:
foreach ( $everyone as $person )
{
	$q = query("SELECT * FROM " . $prefix . "_users WHERE user_id LIKE '" . $person["follower_id"] . "';");
	$follower = $q[0];

	if ( $follower["user_name"] )
	{
		$followers[]["name"] = $follower["full_name"];
		$c = count($followers)-1;
		$followers[$c]["twitter"] = $follower["user_name"];
		$followers[$c]["pic"] = $follower["avatar"];
		$followers[$c]["location"] = $follower["location"];
		$followers[$c]["desc"] = $follower["description"];
		$followers[$c]["url"] = $follower["url"];
		$followers[$c]["followers"] = $follower["follower_count"];
		$followers[$c]["posts"] = $follower["post_count"];
		$followers[$c]["last"] = $follower["last_updated"];		
	}

}

// Output a table of followers and their details:
create_table($followers);
