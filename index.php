<?php
/*	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
*/

	// Subject scanner!

	if (!isset($_GET['subject'])) {
		$_GET['subject'] = "Standard";
	}
	$subject = $_GET["subject"];


	$subjects = "";
	$scanfile = file_get_contents("./help.bot");

	$rows = explode("\n", $scanfile);
	array_shift($rows);

	foreach($rows as $row => $data) {
		if (stristr($data, "%subject ")) {
			$subjects .= substr($data, 9);
			$subjects .= ",";
		}
	}

	$txt_file = file_get_contents("./help.bot");

	$rows = explode("\n", $txt_file);
	array_shift($rows);

	$show = false;

function str_replace_first($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function length_sort($a,$b){
    return strlen($b)-strlen($a);
}

function getLevel($command) {
	
	$scanfile = file_get_contents("./access.list");

	$rows = explode("\n", $scanfile);
	array_shift($rows);

	// var_dump($scanfile);

	foreach($rows as $row => $data) {
		if (stristr($data, $command)) {
			return substr($data, 21);
		}
	}

	return "&lt;level&gt;";
}

$subjectsarray = explode(",", $subjects);
usort($subjectsarray, 'length_sort');

// var_dump($subjectsarray);

$subjects = "";


foreach($subjectsarray as $row => $data) {
	// print($data . "..");
	$data = str_replace(" ", "", $data);
	$subjects .= $data . ",";

}

print("
<!DOCTYPE html>
<html lang=\"en\">
<head>
<title>IncuBot online help by Zokum</title>
<link rel=\"stylesheet\" media=\"screen\" href=\"/zokum/blogg/irc.css\">
<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
</head>
<body><pre>");

if ($subject == "!showaccess") {

	$current = 0;
	$next = 0;

	do {
		$current = $next;
		$next = 999;

		$scanfile = file_get_contents("./access.list");
		$rows = explode("\n", $scanfile);
		array_shift($rows);

		$nl = 0;

		if ($current > 0) {
			print("\n");
		}
		if ($current != 999) {
			printf("\n<span class=grey-inverse> Userlevel %-3d                                                  </span>\n", $current);
		}

		foreach($rows as $row => $data) {

			$f = substr($data, 0, 1);

			if ($f == "#") {
				continue;
			} 

			$array = explode(' ', $data);

			$first = true;
			foreach($array as $str){
				if (strlen($str) > 0) {
					if ($first) {
						$first = false;
						$command = $str;
					} else {
						//print($str . "j");
						if ($str > $current) {
							//print("hmm");
							if ($str < $next) {
								$next = $str;
								//print("new $next $current|");
							}
						}
					}
				}
	
			}			

			$pos = strpos($data, " $current");
			if ($pos === false) {
				//print ("p $pos, $data, $current\n");
				continue;
			}

			if ($nl % 4 == 0) {
				if ($nl != 0) {
					print("\n");
				}
			}

			$nl++;
	
			printf("<a class=\"incu-link\" href=\"?subject=$command\">%-17.17s</a>", $command);

		}
	} while ($next != $current);

	print("\n");

} else {


	foreach($rows as $row => $data) {

		// Filter comments
		$f = substr($data, 0, 1);
		if ($f == "#") {
			// printf("f var $f");
			continue;
		}

		if (strstr($data, "%end")) {
			if ($show) {
				break;
			}
		} else if (stristr($data, "%subject $subject")) {
			$show = true;
			continue;
		} else if (stristr($data, "%subject")) {
			continue;
		}


		if ($show == false) {
			continue;
		}

		// fix for inverse
		$data = str_replace("", "\\i", $data);


		// $data = str_replace("\\l", "&lt;level&gt;", $data);

		if (strstr($data, "\\l")) {
			$level = getLevel($subject);
			$data = str_replace("\\l", $level, $data );
		}
		$data .= "\n";
		$data = str_replace(" \n", "\n", $data);
		$data = str_replace("\n", "", $data);


		$data = str_replace("\\n", "Mikribot", $data);
		$data = str_replace("\\v", "Mikribot (v$: web-help)", $data);
		$data = str_replace("\\\\", "\\", $data);

		// $data = str_replace("<topic>","&lt;topic&gt;", $data);
		$data = str_replace("<", "&lt;", $data);
		$data = str_replace(">", "&gt;", $data);


		$invert = false;
		$bright = false;
		$inverseLine = strstr($data, "\\i");

		for ($i = 0; $i != 3; $i++) {

			if ($i == 0) {
				$token = "\\i";
				$on = "<span class=grey-inverse>";
				$off = "</span>";
			} else if ($i == 1) {
				$token = "\\b";
				$on = "<span class=white>";
				$off = "</span>";				
			} else if ($i == 2) {
				$token = "\\u";
				$on = "<span class=underline>";
				$off = "</span>";
			}


			do {
				$more = false;
				$dataold = $data;

				if ($invert == false) {
					$data = str_replace_first($token, $on, $data);
				} else {
					$data = str_replace_first($token, $off, $data);
				}

				if ($dataold != $data) {
					$more = true;
					$invert = !$invert;
				}

			} while ($more);
		}



		// Linkify!

		// add newline, this fixes an edge case
		$data .= "\n";

		if (($inverseLine == false) && (!stripos($data, "usage") ) ) {


			$ary = explode(',', $subjects);

			foreach($ary as $str){

				$pos = false;
				$offset = 0;

				$pos = stripos($data, "$str");

				if ($pos !== 0) {
					$pos = false;
				}
				
				if (!$pos) {
					$pos = stripos($data, ">$str");
					if ($pos) {
						$offset = 1;
					}
				}

				if (strlen($data) != ($pos + strlen($str))) {
					// $pos = false;
				} 
				
				if ($pos) {
					// print("!!");
				}

				

				if ($pos === false) {
					$pos = stripos($data, "$str   ");
					if ($pos) {
						// print($str);
					}
				}

				if (!$pos) {
					$pos = stripos($data, "$str  <");
				}

				if (!$pos) {
					$pos = stripos($data, "$str <");
				}
				
				if (!$pos) {
					$pos = stripos($data, "> $str");
					if ($pos) {
						$offset = 2;
					}
				}


				if (!$pos) {
					$pos = stripos($data, "   $str");
					if ($pos) {
						$offset = 3;
					}
				}

				if (!$pos) {
					$pos = stripos($data, ", $str");
					if ($pos) {
						$offset = 2;
					}
				}

				if (!$pos) {
					$pos = stripos($data, "$str,");
				}

				if (stripos($data, "!$str")) {
					$pos = false;
				}

				if (stripos($data, "Mikribot")) {
					$pos = false;
				}
				
				if (stripos($data, "iC Bot")) {
 					$pos = false;
				}

				if ($str == "Do") {

					if (stripos($data, "to do")) {
	 				       $pos = false;
					}
				}
	

				$hyphen = stripos($data, "$str-");

				if ($hyphen == $pos ) {
					$pos = false;
				}

				if (stripos($data, "\">$str")) {
					$pos = false;
				}

				if ($pos !== false) {
				
					$char = substr($data, $pos + $offset - 1, 1);

					if ($pos > 0) {
						if (($char != ' ') && ($char != '>'))  {
							$pos = false;
						}
					}

					// print("$char");

				}


				if ($pos !== false) {

					if (strlen($str) > 0) {

						$line = substr($data, 0, $pos + $offset);

						$line .= "<a class=\"incu-link\" href=\"?subject=$str\">";	
						$line .= substr($data, $pos + $offset, strlen($str));
						$line .= "</a>";
						$line .= substr($data, $pos + $offset + strlen($str));

						// $data = str_replace($str, "<a class=\"incu-link\" href=\"?subject=$str\">$str</a>", $data);
						$data = $line;

					}

					// break;
				}
				// print("ord $str");
			}
		}

		if ($show) {
			$lineLength = strlen(strip_tags($data)) - 1;


			$lineLength -= 3 * substr_count($data, "&lt;");
			$lineLength -= 3 * substr_count($data, "&gt;");


			if ($lineLength > 65) {
				print("<span class=\"red\">");
			}

			print($data);

			if ($lineLength > 65) {
				print("</span>");
			}
		}
	}
}
/*

   <a class="incu-link" href="?subject=FORK">fork</a>            <a class="incu-link" href="?subject=rehash">rehash</a>          <a class="incu-link" href="?subject=Do">do</a>
 */
print("<form method=\"get\"><input name=\"subject\" size=64 placeholder=\"$ keyword\" type=\"text\"/></form>");
print("\n<a href=\"?Index\">Index</a> - <a href=\"?subject=Commands\">Basic stuff</a> - <a href=\"?subject=Levels\">Levels</a> - <a href=\"?subject=Privileged\">Privileged</a> - <a href=\"?subject=!showaccess\">All Commands</a> - <a href=\"index.phps\">Source</a>");
print("</pre>\n</html>\n");
?>
