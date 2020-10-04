import socket
args=__import__('sys').argv
sock=socket.socket(socket.AF_INET,socket.SOCK_STREAM)
try:
    socket.connect((args[1],int(args[2])))
    print("access granted!")
except Exception as error:
    print("$ unable to reach the target peer due to :")
    print(error)
    exit()
while 1:
    cmd=input("")
    sock.send(str.encode(cmd))
    responce_back=sock.recv(20480)
    if len(responce_back)!=0:
        print(responce_back.decode('ascii'))
    if cmd=='exit':
        sock.close()
        break
