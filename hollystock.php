<?php
/**
 * Plugin Name: HollyStock Celebrities
 * Plugin URI: http://www.hollystock.com/wordpress
 * Description: List the most popular celebrities from HollyStock.com along with their current HollyStock price.  You can also retrieve a current list of today's celebrity birthdays.
 * Version: 1.0
 * Author: HollyStock.com
 * Author URI: http://www.hollystock.com
 * License: GPL2
 */
add_shortcode('hollystock_topCelebs', 'hollystock_getTopCelebs');
add_shortcode('hollystock_birthdays', 'hollystock_getBirthdays');


/**
 * Retrieve Top Celebs from HollyStock
 * Will echo out a table of the top 10 celebrities
**/
function hollystock_getTopCelebs(){

	//-- Get the list in XML (can use JSON as well but not all WP users have PHP 5+) --
	$data = wp_remote_get('http://www.hollystock.com/developers/export/XML?limit=10');
	
	//-- Grab the top 10 list. We're not doing anything with the exceptions at this point. --
	try { $doc = DOMDocument::loadXML($data['body']); } catch (Exception $e) { unset($doc); }
	if ($doc){
		//-- Yeah, it loaded.  Parse and echo it out. --
		$output = array();//create output and dump into array.  Will implode and echo at the end
		$output[] = "<style type='text/css'>
				#hollystockTop10 {border-collapse:collapse;background-color:#ffffff;font-family:arial,helvetica,sans-serif;}
				#hollystockTop10Header {padding:5px;font-weight:bold;text-align:center;font-size:120%;}
				.hollystockCell {padding:5px;border-bottom:1px solid #ccc;}
			</style>";
		$output[] = "<table id='hollystockTop10' width='100%' border='0' align='center' cellpadding='5' cellspacing='0'>";
		$output[] = "<tbody>";
		$output[] = "<tr><td id='hollystockTop10Header' colspan='3'>Top 10 Celebrities from HollyStock.com</td></tr>";
		$root = $doc->getElementsByTagName('CelebrityValues')->item(0);
		foreach ($root->getElementsByTagName('Celebrity') AS $celeb){
			$celebId = $celeb->getElementsByTagName('celebId')->item(0)->nodeValue;
			$name = $celeb->getElementsByTagName('name')->item(0)->nodeValue;
			$price = $celeb->getElementsByTagName('price')->item(0)->nodeValue;
			$output[] = "<tr><td class='hollystockCell'><a href=\"" . hollystock_getCelebURL($name, $celebId) . "\" target=\"_blank\" title=\"$name\"><img border=\"0\" src=\"http://www.hollystock.com/images/celebs/thumb/$celebId.jpg\" alt=\"$name\" title=\"$name\" /></a></td>";
			$output[] = "<td class='hollystockCell'><a href=\"" . hollystock_getCelebURL($name, $celebId) . "\" target=\"_blank\" title=\"$name\">$name</a></td>";
			$output[] = "<td class='hollystockCell'>\$" . number_format($price) . "</td></tr>";
		}
		$output[] = "</tbody></table>";
		$output = @implode("\n", $output);
		echo $output;
	}else{
		//-- Couldn't load the XML doc --
		echo "<p>Top 10 List Not Available</p>";
	}

}


/**
 * Retrieve celebrity birthdays from HollyStock
 * Will echo out a table of celebrity birthdays
**/
function hollystock_getBirthdays(){

	//-- Get the list in XML (can use JSON as well but not all WP users have PHP 5+) --
	$data = wp_remote_get('http://www.hollystock.com/developers/birthdays/XML');
	
	//-- Grab the list. We're not doing anything with the exceptions at this point. --
	try { $doc = DOMDocument::loadXML($data['body']); } catch (Exception $e) { unset($doc); }
	if ($doc){
		//-- Yeah, it loaded.  Parse and echo it out. --
		$output = array();//create output and dump into array.  Will implode and echo at the end
		$output[] = "<style type='text/css'>
				#hollystockBday {border-collapse:collapse;background-color:#ffffff;font-family:arial,helvetica,sans-serif;}
				#hollystockBdayHeader {padding:5px;font-weight:bold;text-align:center;font-size:120%;}
				.hollystockCell {padding:5px;border-bottom:1px solid #ccc;}
			</style>";
		$output[] = "<table id='hollystockBday' width='100%' border='0' align='center' cellpadding='5' cellspacing='0'>";
		$output[] = "<tbody>";
		$output[] = "<tr><td id='hollystockBdayHeader' colspan='4'>Today's Celebrity Birthdays</td></tr>";
		$root = $doc->getElementsByTagName('Birthdays')->item(0);
		foreach ($root->getElementsByTagName('Celebrity') AS $celeb){
			$celebId = $celeb->getElementsByTagName('celebId')->item(0)->nodeValue;
			$name = $celeb->getElementsByTagName('name')->item(0)->nodeValue;
			$price = $celeb->getElementsByTagName('price')->item(0)->nodeValue;
			$age = $celeb->getElementsByTagName('age')->item(0)->nodeValue;
			$havePic = (int)$celeb->getElementsByTagName('havePic')->item(0)->nodeValue;
			if ($havePic > 0){$pic = $celebId . '.jpg';}else{$pic = 'default.gif';}
			$output[] = "<tr><td class='hollystockCell'><a href=\"" . hollystock_getCelebURL($name, $celebId) . "\" target=\"_blank\" title=\"$name\"><img border=\"0\" src=\"http://www.hollystock.com/images/celebs/thumb/$pic\" alt=\"$name\" title=\"$name\" /></a></td>";
			$output[] = "<td class='hollystockCell'><a href=\"" . hollystock_getCelebURL($name, $celebId) . "\" target=\"_blank\" title=\"$name\">$name</a></td>";
			$output[] = "<td class='hollystockCell'>Age: $age</td>";
			$output[] = "<td class='hollystockCell'>\$" . number_format($price) . "</td></tr>";
		}
		$output[] = "</tbody></table>";
		$output = @implode("\n", $output);
		echo $output;
	}else{
		//-- Couldn't load the XML doc --
		echo "<p>Birthday List Not Available</p>";
	}

}


/**
 * Utility to create the HollyStock celebrity page URL
 *
 * @param string $name The celebrity's name
 * @param int $celebId The HollyStock celebrityId for this celeb 
**/
function hollystock_getCelebURL($name, $celebId){
	$celebId = (int)$celebId;
	$name = str_replace(' ', '-', stripslashes($name));
	$name = str_replace(array("'", "\"", "/"), "", $name);
	$url = 'http://www.hollystock.com/celebrity/' . $celebId . '/' . $name;
	return $url;
}

/* End of File */