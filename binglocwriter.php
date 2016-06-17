<?php
  if (array_key_exists(1, $argv) == false || array_key_exists(2, $argv) == false){
    echo "need two args";
    die();
  }
  $start = intval($argv[1]);
  $end = intval($argv[2]);
  $addresses = fopen("/var/www/html/taiyi/geomap/addressUnique.txt", "r") or die("can't open file");
  $writeFile = fopen("/var/www/html/taiyi/geomap/addressLocUnique.txt", "a") or die ("can't open new file");
 //call x nums of api
   $i = 0;
  //skip already processed lines
  while ($i < $start && !feof($addresses)){
    fgets($addresses);
    $i += 1;
  }

  while($i < $end && !feof($addresses)){
    $curline = fgets($addresses);
    //adding these after makes array indexes consistent
    $curline2 = $curline . "||";
    $strArr = explode("|", $curline2);
    $zipString = $strArr[0];
    $addr = $strArr[1];
    $city = "";
    $state = "";
    $url =  "http://dev.virtualearth.net/REST/v1/Locations/US/";
    //addresses and cities need space processing, replace w/ %20 or something
    if (strcmp($strArr[9], "") != 0){
       $state = str_replace(" ", "%20", $strArr[9]);
       $url .= $state . "/";
    }
    $url .= $zipString . "/";
    if (strcmp($strArr[7], "") != 0){
      $city = str_replace(" ", "%20", $strArr[7]);
      $url .= $city . "/";
    }
    $url .= str_replace(" ", "%20", $addr);
    $url .= "?o=xml&key=AsqSCM-MJ_eiT2asMVkAKILuUxnMBKDBSJhOzyPo3EBVg9_-gXHTydmpimtX4T9l";
    $xmlstring = @file_get_contents($url);
    if ($xmlstring === false){
      $long = "fail1";
      $lat = "fail1";
    }
    else{ 
      $xml = simplexml_load_string($xmlstring) or die("Cannot read data");
      if (strcmp($xml->ResourceSets->ResourceSet->EstimatedTotal , "0") == 0){
	$long = "fail2";
        $lat = "fail2";
      }
      else{
    	$long = substr($xml->ResourceSets->ResourceSet->Resources->Location->Point->Longitude, 0, 7);
    	$lat = substr($xml ->ResourceSets->ResourceSet->Resources->Location->Point->Latitude, 0, 7);
      }
    }
    fwrite($writeFile, $zipString . "|" . $addr . "|" . $long . "|" . $lat . "\n");
    $i += 1;
  }
  fclose($addresses);
  fclose($writeFile);
  echo "Num ended: " . $i;
?>
