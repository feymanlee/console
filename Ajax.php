<?php


//----------------------------------
// 通用接口
//----------------------------------
include_once './Common.php';

if (isset($_GET['action'])) {
    $_GET['action']();
}

/**
 * 取系统信息
 *
 * @param String $_GET ['ip'] 服务器IP
 * @param String $_GET ['name'] 欲取的信息   info , disk ,cpu ,net
 *
 * @return Json   成功返回数组，失败返回false
 */
function systemInfo()
{
    $name = $_GET['name'];
    switch ($name) {
        case 'info':
            $Return = SendSocket("System|info");
            break;
        case 'disk':
            $Return = SendSocket("System|disk");
            break;
        case 'cpu':
            $Return = SendSocket("System|cpu");
            break;
        case 'net':
            $Return = SendSocket("System|net");
            break;
        default:
            $Return = false;
            break;
    }
    ajax_return($Return);
}

/**
 * 取服务器信息
 *
 * @param String $_GET ['ip'] 服务器IP
 * @param String $_GET ['name'] 欲取的信息   serverType , softInfo ,inSql
 *
 * @return Json   成功返回数组，失败返回false
 */
function server_info()
{
    $name = $_GET['name'];
    switch ($name) {
        case 'serverType':
            $Return = SendSocket("Type");
            break;
        case 'softInfo':
            $Return = SendSocket("Info");
            break;
        case 'inSql':
            $Return = SendSocket("InSQL");
            break;
        default:
            $Return = false;
            break;
    }
    ajax_return($Return);
}


/**
 * 设置备注信息
 *
 * @param String $_GET ['tab'] 数据库表名
 * @param String $_GET ['id'] 条件ID
 *
 * @return Bool
 */
function setPs()
{
    $tableName = $_GET['tab'];
    $id        = $_GET['id'];
    $SQL       = M($tableName);
    if ($SQL->where("id=$id")->setField('ps', $_GET['ps'])) {
        ajax_return(true);
    } else {
        ajax_return(false);
    }

}

/**
 * 取数据列表
 *
 * @param String $_GET ['tab'] 数据库表名
 * @param Int    $_GET ['count'] 每页的数据行数
 * @param Int    $_GET ['p'] 分页号  要取第几页数据
 *
 * @return Json  page.分页数 , count.总行数   data.取回的数据
 */
function getData()
{
    $table = $_GET['tab'];
    $data  = GetSql($table);

    $SQL = M('backup');
    //解密加密文本
    for ($i = 0; $i < count($data['data']); $i++) {
        $id = $data['data'][$i]['id'];
        if (isset($data['data'][$i]['password']) && strlen($data['data'][$i]['password']) > 20) {
            $data['data'][$i]['password'] = RCode($data['data'][$i]['password'], 'DECODE');
        }
        if (isset($data['data'][$i]['filename'])) {
            if ($data['data'][$i]['size'] == '0') {
                $data['data'][$i]['size'] = filesize($data['data'][$i]['filename']);
                if ($data['data'][$i]['size'] !== false)
                    $SQL->where("id=" . $data['data'][$i]['id'])->setField('size', $data['data'][$i]['size']);
            }
        } else {
            $data['data'][$i]['backup_count'] = $SQL->where("pid='$id'")->getCount();
        }

        if ($data[$i]['ps'] == '') $data[$i]['ps'] = '空';
    }


    //返回
    ajax_return($data);
}

/**
 * 取数据库行
 *
 * @param String $_GET ['tab'] 数据库表名
 * @param Int    $_GET ['id'] 索引ID
 *
 * @return Json
 */
function getFind()
{
    $tableName = I('get.tab');
    $id        = I('get.id');
    $SQL       = M($tableName);
    $where     = "id=$id";
    $find      = $SQL->where($where)->find();
    ajax_return($find);
}

/**
 * 取字段值
 *
 * @param String $_GET ['tab'] 数据库表名
 * @param String $_GET ['key'] 字段
 * @param String $_GET ['id'] 条件ID
 *
 * @return String
 */
function getKey()
{
    $tableName = $_GET['tab'];
    $keyName   = $_GET['key'];
    $id        = $_GET['id'];
    $SQL       = M($tableName);
    $where     = "id=$id";
    $retuls    = $SQL->where($where)->getField($keyName);
    ajax_return($retuls);
}


/**
 * 获取数据与分页
 *
 * @param string $table  表
 * @param string $where  查询条件
 * @param int    $limit  每页行数
 * @param mixed  $result 定义分页数据结构
 *
 * @return array
 */
function GetSql($table, $result = '1,2,3,4,5,8')
{
    //判断前端是否传入参数
    $order  = !empty($_GET['order']) ? $_GET['order'] : "id desc";  //排序
    $limit  = !empty($_GET['limit']) ? $_GET['limit'] : 20;            //每页行数
    $result = !empty($_GET['return']) ? $_GET['return'] : $result;    //分页结构
    //取查询条件
    $where = GetWhere($table);
    //实例化数据库对象
    $SQL = M($table);
    //取总行数
    $count = $SQL->where($where)->getCount();
    //包含分页类
    include_once './class/Page.class.php';
    //实例化分页类
    $page = new Page($count, $limit);
    //获取分页数据
    $data['page'] = $page->getPage($result == 'false' ? false : $result);
    //取出数据
    $data['data'] = $SQL->where($where)->order($order)->limit($page->shift, $page->row)->select();

    return $data;
}

//获取条件
function GetWhere($tableName)
{
    if (!isset($_GET['search'])) return '';
    $search = $_GET['search'];

    switch ($tableName) {
        case 'sites':
            $where = "id like binary '$search' or  name like binary '%$search%' or status like binary '$search' or domain like binary '%$search%' or ps like binary '%$search%'";
            break;
        case 'ftps':
            $where = "id like binary '$search' or  name like binary '%$search%' or ps like binary '%$search%'";
            break;
        case 'databases':
            $where = "id like binary '$search' or  name like binary '%$search%' or ps like binary '%$search%'";
            break;
        case 'logs':
            $where = "type like binary '%$search%' or log like binary '%$search%' or addtime like binary '%$search%'";
            break;
        case 'backup':
            $where = "pid=$search";
            break;
        default:
            return "";
            break;
    }

    return $where;
}


//浏览文件夹
function GetDir()
{
    $path = trim($_POST['path']);
    $path = str_replace('//', '/', $path);

    if ($path == '') {
        $path = '/';
        $Disk = SendSocket("System|disk");
        foreach ($Disk as $value) {
            if ($value['分区'] == '/boot' || $value['分区'] == '') continue;
            $data['DISK'][] = ['Span' => $value['分区'], 'Size' => $value['可用空间']];
        }
        $data['PATH'] = '/';
        ajax_return($data);
    }
    if (!is_dir($path)) $path = $_SESSION['config']['sites_path'];
    if (!file_exists($path)) $path = '/';
    $dir = dir($path);
    if (!$dir) {
        $path = $_SESSION['config']['sites_path'];
        $dir  = dir($path);
    }
    $dirs  = [];
    $files = [];
    while ($file = $dir->read()) {
        $fileName = $path . '/' . $file;
        if ($file != '.' && $file != '..') {
            if (is_dir($fileName)) {
                $dirs[] = $file;
            } else {

                $files[] = $file . ';' . filesize($fileName) . ';' . ($file == '.user.ini' ? '目录安全文件,删除后可能导致重大安全隐患!' : filemtime($fileName));
            }
        }
    }
    array_multisort($dirs, SORT_ASC, SORT_STRING, $dirs);
    array_multisort($files, SORT_ASC, SORT_STRING, $files);
    $data = ['PATH' => $path, 'DIR' => $dirs, 'FILES' => $files];
    if (!json_encode($data)) $data = mult_iconv('GB2312', 'UTF-8', $data);
    ajax_return($data);
}

//取目录大小
function GetDirSize()
{
    $path = I('path');
    SendSocket("ExecShell|du -sb $path");
    $size = formatsize(intval(file_get_contents('/tmp/shell_temp.pl')));
    ajax_return($size);
}

//清理日志
function CloseLogs()
{
    SendSocket("ExecShell|rm -f /www/wwwlogs/*log");
    SendSocket("ExecShell|kill -USR1 `cat /www/server/nginx/logs/nginx.pid`");
    $_GET['path'] = '/www/wwwlogs';
    GetDirSize();

}


//转换数组字符编码
function mult_iconv($in_charset, $out_charset, $data)
{
    if (substr($out_charset, -8) == '//IGNORE') {
        $out_charset = substr($out_charset, 0, -8);
    }
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $key       = iconv($in_charset, $out_charset . '//IGNORE', $key);
                $rtn[$key] = mult_iconv($in_charset, $out_charset, $value);
            } elseif (is_string($key) || is_string($value)) {
                if (is_string($key)) {
                    $key = iconv($in_charset, $out_charset . '//IGNORE', $key);
                }
                if (is_string($value)) {
                    $value = iconv($in_charset, $out_charset . '//IGNORE', $value);
                }
                $rtn[$key] = $value;
            } else {
                $rtn[$key] = $value;
            }
        }
    } elseif (is_string($data)) {
        $rtn = iconv($in_charset, $out_charset . '//IGNORE', $data);
    } else {
        $rtn = $data;
    }

    return $rtn;
}

//创建文件夹
function AddDir()
{
    $path = I('path');
    $tmp  = explode('/', $path);
    $path = str_replace("\\", "/", $path);
    $Name = $tmp[count($tmp) - 1];
    if ($Name == '') $path .= 'NewFolder';
    if (CheckPath($Name)) {
        ajax_return(['status' => false, 'msg' => '文件夹名称不合法,不能有 <span style="color:red">\\/:*?"<>|</span>']);
    }
    $data = SendSocket("AddDir|" . $path);
    $data['msg'] .= $path;
    ajax_return($data);
}

//删除空文件夹
function DelNullDir()
{
    $path = I('path');
    $path = str_replace("\\", "/", $path);
    $data = SendSocket("DelNullDir|" . $path);
    $data['msg'] .= $path;
    ajax_return($data);
}

//校验目录名称
function CheckPath($Name)
{
    $str = "/:*?\"<>|";
    for ($i = 0; $i < strlen($str); $i++) {
        if (strstr($Name, $str{$i})) {
            return true;
        }
    }

    return false;
}

//取流量统计
function GetNetTotal()
{
    $netStr = @file("/proc/net/dev");
    $data   = [];
    $up     = $down = $data['downTotal'] = $data['upTotal'] = 0;
    for ($i = 2; $i < count($netStr); $i++) {
        preg_match_all("/([^\s]+):[\s]{0,}(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/", $netStr[$i], $info);
        if ($info[1][0] == 'lo') continue;

        $up += $info[10][0];
        $down += $info[2][0];
        $data['downTotal'] += $info[2][0];
        $data['upTotal'] += $info[10][0];
    }
    if (!isset($_SESSION['up'])) {
        $_SESSION['up']   = $info[10][0];
        $_SESSION['down'] = $info[2][0];
    }
    $data['downTotal'] = formatsize($data['downTotal']);
    $data['upTotal']   = formatsize($data['upTotal']);
    $data['up']        = round(($up - $_SESSION['up']) / 1024 / 3, 2);
    $data['down']      = round(($down - $_SESSION['down']) / 1024 / 3, 2);

    $_SESSION['up']   = $up;
    $_SESSION['down'] = $down;
    if ($data['up'] > 102400) {
        $data['up'] = $data['down'] = 0;
    }
    ajax_return($data);
}

//取系统统计
function GetSystemTotal()
{
    $data                = [];
    $data['system']      = str_replace('release', '', file_get_contents('/etc/redhat-release'));
    $mem                 = GetMemTotal();
    $data['memTotal']    = $mem['memTotal'];
    $data['memRealUsed'] = $mem['memRealUsed'];
    $data['time']        = GetTimeTotal();
    $cpu                 = GetCpuTotal();
    $data['cpuNum']      = $cpu['num'];
    $data['cpuRealUsed'] = $cpu['used'];
    ajax_return($data);

}

//取内存统计
function GetMemTotal()
{
    $str = @file("/proc/meminfo");
    $str = implode("", $str);
    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
    preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

    $data['memTotal']    = intval($buf[1][0] / 1024);
    $data['memFree']     = round($buf[2][0] / 1024, 2);
    $data['memBuffers']  = round($buffers[1][0] / 1024, 2);
    $data['memCached']   = round($buf[3][0] / 1024, 2);
    $data['memRealUsed'] = $data['memTotal'] - $data['memFree'] - $data['memCached'] - $data['memBuffers'];

    return $data;

}

//取开机时间
function GetTimeTotal()
{
    $str   = @file("/proc/uptime");
    $str   = explode(" ", implode("", $str));
    $str   = trim($str[0]);
    $min   = $str / 60;
    $hours = $min / 60;
    $days  = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $min   = floor($min - ($days * 60 * 24) - ($hours * 60));
    if ($days !== 0) $data = $days . "天";
    if ($hours !== 0) $data .= $hours . "小时";
    $data .= $min . "分钟";

    return $data;
}

//取CPU统计
function GetCpuTotal()
{
    $stat1 = GetCoreInformation();
    sleep(1);
    $stat2         = GetCoreInformation();
    $data          = GetCpuPercentages($stat1, $stat2);
    $result['num'] = count($data);
    $free          = 0;
    foreach ($data as $value) {
        $free += $value['idle'];
    }
    $result['used'] = round(($result['num'] * 100 - $free) / $result['num'], 2);

    return $result;
}

function GetCpuPercentages($stat1, $stat2)
{
    if (count($stat1) !== count($stat2)) {
        return;
    }
    $cpus = [];
    for ($i = 0, $l = count($stat1); $i < $l; $i++) {
        $dif            = [];
        $dif['user']    = $stat2[$i]['user'] - $stat1[$i]['user'];
        $dif['nice']    = $stat2[$i]['nice'] - $stat1[$i]['nice'];
        $dif['sys']     = $stat2[$i]['sys'] - $stat1[$i]['sys'];
        $dif['idle']    = $stat2[$i]['idle'] - $stat1[$i]['idle'];
        $dif['iowait']  = $stat2[$i]['iowait'] - $stat1[$i]['iowait'];
        $dif['irq']     = $stat2[$i]['irq'] - $stat1[$i]['irq'];
        $dif['softirq'] = $stat2[$i]['softirq'] - $stat1[$i]['softirq'];

        $total = array_sum($dif);
        $cpu   = [];
        foreach ($dif as $x => $y) $cpu[$x] = round($y / $total * 100, 2);
        $cpus['cpu' . $i] = $cpu;
    }

    return $cpus;


}


function GetCoreInformation()
{
    $data  = file('/proc/stat');
    $cores = [];
    foreach ($data as $line) {
        if (preg_match('/^cpu[0-9]/', $line)) {
            $info    = explode(' ', $line);
            $cores[] = [
                'user'    => $info[1],
                'nice'    => $info[2],
                'sys'     => $info[3],
                'idle'    => $info[4],
                'iowait'  => $info[5],
                'irq'     => $info[6],
                'softirq' => $info[7]];
        }
    }

    return $cores;
}

//取Nginx负载状态
function GetNginxStatus()
{
    $url    = "http://127.0.0.1/nginx_status";
    $result = httpGet($url);
    $tmp    = explode("\n", $result);

    $data             = [];
    $active           = explode(':', $tmp[0]);
    $data['active']   = intval($active[1]);        //当前活动连接数量
    $server           = explode(' ', trim($tmp[2]));
    $data['accepts']  = intval($server[0]);        //总连接次数
    $data['handled']  = intval($server[1]);        //总成功握手次数
    $data['requests'] = intval($server[2]);        //总请求数
    $rww              = explode(' ', $tmp[3]);
    $data['Reading']  = intval($rww[1]);            //读取客户端的连接数.
    $data['Writing']  = intval($rww[3]);            //响应数据到客户端的数量
    $data['Waiting']  = intval($rww[5]);            //驻留进程数
    ajax_return($data);
}

//单位转换
function formatsize($size)
{
    $danwei = [' B ', ' K ', ' M ', ' G ', ' T '];
    for ($i = 0; $i < count($danwei); $i++) {
        if ($size < 1024) return round($size, 2) . $danwei[$i];
        $size /= 1024;
    }

    return round($size, 2) . $danwei[count($danwei) - 1];
}

?>
