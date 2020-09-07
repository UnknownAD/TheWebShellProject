<?php
$verification=socket_create(AF_INET,SOCK_STREAM,0);
socket_connect($verification,$argv[1],80) or die('unable to reach the target host');
socket_write($verification,sprintf("GET /shell_magnify.php HTTP/1.1\r\nHost: %s\r\nConnection: Close\r\n\r\n",$argv[1]));
if(strpos(socket_read($verification,1024),'200 OK')){echo "1";}
else{echo "0";}
?>
