<?php
  $longAddr = array(" LANE ", " PARKWAY ", " DRIVE ", " ROAD ",
     " AVENUE ", " STREET "," FREEWAY ");
  $shortAddr = array(" LN ", " PKY ", " DR ", " RD ",
      " AVE ", " ST ", " FWY ");
  $fail1Ct = 0;
  $fail2Ct = 0;
  $first = false;
  $lineCt = 0;
  $addresses = fopen("addressLocUnique.txt", "r") or die("can't open file");
  $writer = fopen("fix.txt", "w") or die("can't write to file");
  $long = "";
  $lat = "";
  while(!feof($addresses)){
    $curline = fgets($addresses);
    $strArr = explode("|", $curline);
    $newAddr = $strArr[1];
    //trim space at end
    if (strcmp(" ", substr($newAddr, -1)) == 0){
      $newAddr = substr($newAddr, 0, -1);
    }
    //remove "&"
    $newAddr = str_replace("&", "", $newAddr);
    //remove ":"
    $newAddr = str_replace(":", "", $newAddr);
    //urlencode
    $newAddr = urlencode($newAddr);
    if (strcmp($strArr[2], "fail2") == 0){
       $url =  "http://dev.virtualearth.net/REST/v1/Locations/";
      //zip code, canada has spaces
      //$url .= str_replace(" ", "%20", $strArr[0]) . "/";
      $url .= str_replace(" ", "%20", $newAddr);
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
      fwrite($writer, $strArr[0] . "|" . $strArr[1] . "|" . $long . "|" . $lat . "\n");

    }
    else{
      fwrite($writer, $curline);
    }
    /*if (strcmp($strArr[2], "fail2") == 0){
	$fail2Ct += 1;
        if ($first == false){
	  echo $lineCt . " " . $curline . "\n";
          //$first = true;
        }
    }
    $lineCt += 1;*/
  }
  fclose($writer);
  fclose($addresses);
?>
