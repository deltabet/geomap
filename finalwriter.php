<?php
  $longAddr = array(" LANE ", " PARKWAY ", " PKWY ", " DRIVE ", " ROAD ",
     " AVENUE ", " STREET "," FREEWAY ", " NORTH ",
      " SOUTH ", " WEST ", " EAST ", ".");
  $shortAddr = array(" LN ", " PKY ", " PKY ", " DR ", " RD ",
      " AVE ", " ST ", " FWY ", " N ", " S ", " W ", " E ", "");
  $zipArr = array();
  $dupCt = 0;
  $uniqueCt = 0;
  $addresses = fopen("address.txt", "r") or die("can't open file");
  $unique = fopen("addressLocUnique.txt", "r") or die("can't open addresslocunique");
  $writeFile = fopen("address_new.txt", "w") or die ("can't open new file");
  //skip first line 
 fgets($addresses);
 while(!feof($addresses)){
   //for ($i = 0; $i < 150000; $i += 1){
    $curline = fgets($addresses);
    //adding these after makes array indexes consistent
    $curline2 = $curline . "||";
    $strArr = explode("|", $curline2);
    //PO BOX addresses no number, so skipped
    if (strcmp($strArr[1], "") != 0){
      //adding a space before and after makes replacements easiser
      $road = " " . $strArr[2]  . " ";
      $road = str_replace($longAddr, $shortAddr, $road);
      $roadArr = explode(" ", $road);
      $road = "";
      for ($x = 0; $x < count($roadArr); $x++){
        if (strpos($roadArr[$x], "#") === false){
	  $road .= $roadArr[$x] . " ";
        }
      }
      //cut off space at end
      $road = substr($road, 0, -2);
      //cut off zip codes
      $zipString = substr($strArr[8], 0, 5);
      $zipNum = intval($zipString);
      //$road has space in front already
      $addrKey = $strArr[1] . $road;
      $curLong = "0";
      $curLat = "0";
      $canWrite = true;
      if (array_key_exists($zipNum, $zipArr)){
	if (array_key_exists($addrKey, $zipArr[$zipNum])){
	  //skip, address already exsists
	  $dupCt += 1;
          $curLongLat = explode(" ", $zipArr[$zipNum][$addrKey]);
          $curLong = $curLongLat[0];
          $curLat = $curLongLat[1];
          $canWrite = false;
        }
        else{
          //store lat and long
          $curLong = "" . $uniqueCt;
          $curLat = "-" . $uniqueCt;
	  $zipArr[$zipNum][$addrKey] = $curLong . " " . $curLat;
          $uniqueCt += 1;
        }
      }
      else{ //new zip code
        $zipArr[$zipNum] = array();
        //store lat and long
	$curLong = "" . $uniqueCt;
        $curLat = "-" . $uniqueCt;
        $zipArr[$zipNum][$addrKey] = $curLong . " " . $curLat;
        $uniqueCt += 1;
      }
    }
  }
  fclose($addresses);
  //get longs and lats
  while(!feof($unique)){
    $curline = fgets($unique);
    $strArr = explode("|", $curline);
    $long = $strArr[2];
    $lat = $strArr[3];
    $zip = intval($strArr[0]);
    $addr = $strArr[1];
    $zipArr[$zip][$addr] = $long . "|" . $lat;
  }
  fclose($unique);
   $addresses = fopen("address.txt", "r") or die("can't open file");
  //skip first line
 fgets($addresses);
 while(!feof($addresses)){
    $curline = fgets($addresses);
    //adding these after makes array indexes consistent
    $curline2 = $curline . "||";
    $strArr = explode("|", $curline2);
    //PO BOX addresses no number, so skipped
    if (strcmp($strArr[1], "") != 0){
      //adding a space before and after makes replacements easiser
      $road = " " . $strArr[2]  . " ";
      $road = str_replace($longAddr, $shortAddr, $road);
      $roadArr = explode(" ", $road);
      $road = "";
      for ($x = 0; $x < count($roadArr); $x++){
        if (strpos($roadArr[$x], "#") === false){
          $road .= $roadArr[$x] . " ";
        }
      }
      //cut off space at end
      $road = substr($road, 0, -2);
      //cut off zip codes
      $zipString = substr($strArr[8], 0, 5);
      $zipNum = intval($zipString);
      //$road has space in front already
      $addrKey = $strArr[1] . $road;
      $merged = $strArr[1] . "+" . $strArr[2];
      //addr2, 3, city, county, state may not be there
      if (strcmp($strArr[3], "") != 0){
        $merged .= "+" . $strArr[3];
      }
      if (strcmp($strArr[4], "") != 0){
        $merged .= "+" . $strArr[4];
      }
      if (strcmp($strArr[5], "") != 0){
        $merged .= "+" . $strArr[5];
      }
      if (strcmp($strArr[7], "") != 0){
        $merged .= "+" . $strArr[7];
      }
      if (strcmp($strArr[8], "") != 0){
        $merged .= "+" . $strArr[8];
      }
      $merged = str_replace(" ", "+", $merged);
      $writeString = str_replace("\n", "", $curline . "|" . 
        $merged . "|" . $addrKey . "|" . $zipArr[$zipNum][$addrKey]);
      fwrite($writeFile, $writeString . "\n");
    }
  }
  fclose($addresses);
  fclose($writeFile);
  echo "num dups: " . $dupCt . "\n";
  echo "num unique: " . $uniqueCt . "\n";
?>
