# Longboard City

Finds best longboard rides in city by cross referencing public transport map and location elevation.

Use case:
1. Find route to drive using current tool
2. Buy public transport ticket
3. Drive to start location
4. Cruise down the road till station
5. Drive up with transport
6. GOTO 4

## Uses

+ [Google Elevation API][1]
+ [General Transit Feed Specification Reference][2]

## Install

+ Create account in google and then go to [Google Developers Console][4]
+ Get [Google Elevation API][1] key.
+ Download your city gtfs file from [Public Feeds][3]
+ Uncompress and put it in data/gtfs/{YOUR_CITY_NAME}
+ Create file etc/config.local.php # cp etc/config.php etc/config.local.php
+ Edit newly created file and fill null values.
⋅⋅* Add elevation api key.
⋅⋅* In gtfs->city set your {YOUR_CITY_NAME}
⋅⋅* Fill values under filter key.

Then install php, update dependencies and execute script approximately like so:

```
apt-get install php5-cli
php composer.phar update
php index.php | less
```

## Results

Result should give you list with routes that could (hopefully) be drivable using your longboard.


[1]: https://developers.google.com/maps/documentation/elevation/
[2]: https://developers.google.com/transit/gtfs/reference
[3]: https://code.google.com/p/googletransitdatafeed/wiki/PublicFeeds
[4]: https://console.developers.google.com/project