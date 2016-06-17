# geomap

uniquewriter.php goes through address.txt and maps addresses, excluding blank addresses and PO boxes, to a 2D array 
organized by zip code and address string. All street names are cleaned with the same rules. The results
are put into addressUnique.txt. Most of the cleansing is replacing common street types with consistent names, 
such as "ROAD" with "RD".

example: array[78302][3940 WestLake Ln] is an unique address.

binglocwriter.php calls the Bing api over and over again with these unique addresses, cities, states, and zip codes.
There are instances where there is no city, state, or zip code. A cron job was set up to do this task, and save 
backups every 15000 calls. The Long and Lat are put into addressLocUnique.txt. There were about 500 api 
failures in around 100,000 unique api calls, in around 200,000 valid addresses in under 
290,000 original lines in address.txt

A final round of cleansing of addresses commences, based on the locations that failed the Bing api search. 
-Various special characters and spaces are replaced to be url friendly.
-Countries outside of the US have either an unusual zip code, or none at all. Seperate Bing calls were made
to properly format the Canadian are code, and then other countries who did not have a zip code.
Addresses were also temporarily cleansed as to be ignored if the country was not the one currently being called.
-Several places had unusual location signifiers that did not change the latitude or longitude but made it
so the api could not find the address, such as "A AND B" or extra numbers. These were removed, and the 
api called again.
-If the Bing api could not find the addresses, we called the Google api instead. The Google api is more flexible
with address formatting, and is more likley to return a result, but for improperly formatted addresses is
more likley to return a wrong result as well, while Bing would tell you that it failed. 
We used Bing since it was more strict, and accurate in this specific situation, and had a 
higher limit (100,000+ calls for each account).
-There were extra additional cleansing steps we could have taken at the beginning but did not see, such as
"BOULEVARD" and "BLVD" both exsisting. These did not affect longitude and latitude, however, so we simply
cleaned the addresses.
-There were 22 addresses that we could not get the api to recognize automatically, no matter how much cleaning
we did, so we had to enter them in manually.
-There were a few addresses that the api could not recognize no matter what, such as "20 miles from the airport"
given for a street name. We manually entered the best approximate lat and long for those.

There were also a few human mistakes, such as zip code "87592" instead of the correct "77592" being entered, 
but we could not figure out how to automate detection of things such as this.
