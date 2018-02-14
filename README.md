# xgrower
Smartphone-controlled automation device for indoor growers.

This project started in 2013 and was abandoned in 2014.
A working demo video is available here: https://www.youtube.com/watch?v=DzrJrCywY18

The code consists of:
 
## Client
The Arduino code that runs in the device. This is the client that pings the server to sync.

## Server
This has a mysql database accessed by a PHP "gui" code which allows users to interact with the device and also there's a "pulso" code which is listening to Client pings and keeps everyhting syncd and the database up to date.