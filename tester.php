<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

class Tester
{
    static public function getLoadedExtensions()
    {
        return get_loaded_extensions();
    }
    
    static public function gdTest()
    {
        return extension_loaded('gd') && function_exists('gd_info');
    }
    
    static public function imagickTest()
    {
        return extension_loaded('imagick') && class_exists('Imagick');
    }
    
    static public function pdoMysqlTest()
    {
        if(class_exists('PDO') && extension_loaded('PDO') && extension_loaded('pdo_mysql')) {
            foreach(\PDO::getAvailableDrivers() as $driver) {
                if($driver === 'mysql') {
                    return true;
                }
            }
        }
    
        return false;
    }
    
    static function curlTest()
    {
        if(is_callable('curl_init') && extension_loaded('curl')) {
            return true;
        }
        
        return false;
    }
    
    static function phpVersionIsSameCguCliTest()
    {
        if(Tester::shellExecTest()) {
            return stripos(shell_exec('php -v'), phpversion()) !== false;
        }
    
        return false;
    }
    
    static public function redisTest()
    {
        $flags = STREAM_CLIENT_CONNECT;
        $flags |= STREAM_CLIENT_ASYNC_CONNECT;
    
        if(!($resource = @stream_socket_client('tcp://127.0.0.1:6379', $errno, $errstr, 2, $flags))) {
            return false;
        }
    
        //Change timeout on stream
        stream_set_timeout($resource, 1, 0);
        
        @fwrite($resource, "*3\r\n$3\r\nSET\r\n$5\r\nmykey\r\n$1\r\n\"1\"\r\n");
        $buffer = trim(fread($resource, 4096));
        fclose($resource);
    
        if($buffer == '+OK') {
            return true;
        }
    
        return false;
    }
    
    static public function shellExecTest()
    {
        return is_callable('shell_exec') && false === stripos(ini_get('disable_functions'), 'shell_exec');
    }
    
    static public function rewriteModTest()
    {
        return getenv('REWRITE_MOD_ENABLER') === "1" ? true : false;
    }
    
    static public function getPhpCgiVersion()
    {
        return phpversion();
    }
    
    static public function getPhpCliVersion()
    {
        if(Tester::shellExecTest()) {
            return shell_exec('php -v');
        }
        
        return null;
    }
}

$tests = [
    'server time' => new \DateTime(),
    'mod_rewrite enabled' => Tester::rewriteModTest(),
    'shell_exec callable' => Tester::shellExecTest(),
    'PHP (cgi) version' => Tester::getPhpCgiVersion(),
    'PHP (cli) version (shell_exec must callable)' => Tester::getPhpCliVersion(),
    'PDO Mysql (PDO class & mysql driver)' => Tester::pdoMysqlTest(),
    'curl (ext & function)' => Tester::curlTest(),
    'GD (ext)' => Tester::gdTest(),
    'Imagick (ext & class)' => Tester::imagickTest(),
    'Redis server tcp://127.0.0.1:6379 (connect, read & write)' => Tester::redisTest(),
];

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Tester</title>
<style> pre { margin: 0; }</style>
</head>

<body>
    <table align="center" border="1" width="60%">
    <?php foreach($tests as $label => $result): ?>
    
      <tr>
        <td><?php echo $label; ?></td>
        <?php if(is_bool($result)): ?>
        <td bgcolor="#<?php echo ($result) ? '00FF00' : 'FF0000'; ?>">&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <?php elseif(is_object($result)): ?>
        <td><pre><?php echo print_r($result, true); ?><pre></td>
        <?php else: ?>
        <td><?php echo $result; ?></td>
        <?php endif; ?>
      </tr>
      
    <?php endforeach; ?>
    </table>
</body>
</html>