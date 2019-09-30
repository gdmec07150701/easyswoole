<?php
namespace App\Service;

class ArrayToolkit
{
    public static function column(array $array, $columnName)
    {
        if (empty($array)) {
            return array();
        }

        $column = array();

        foreach ($array as $item) {
            if (isset($item[$columnName])) {
                $column[] = $item[$columnName];
            }
        }

        return $column;
    }

    /**
     * 秒转换成H:i:s
     * @param $second
     * @return string
     */
    public static function time2string($second){
        $second = $second%(3600*24);
        $hour = floor($second/3600);
        $second = $second%3600;
        $minute = floor($second/60);
        $second = $second%60;
        return str_pad($hour,'2','0',STR_PAD_LEFT).':'.str_pad($minute,'2','0',STR_PAD_LEFT).':'.str_pad($second,'2','0',STR_PAD_LEFT);
    }

    /**
     * 计算出两个日期之间的月份
     * @param  [type] $start_date [开始日期，如2014-03]
     * @param  [type] $end_date   [结束日期，如2015-12]
     * @param  string $explode [年份和月份之间分隔符，此例为 - ]
     * @param  boolean $addOne [算取完之后最后是否加一月，用于算取时间戳用]
     * @return [type]    [返回是两个月份之间所有月份字符串]
     */
    public static function dateMonths($start_date,$end_date,$explode='-',$addOne=false)
    {
        //判断两个时间是不是需要调换顺序
        $start_int = strtotime($start_date);
        $end_int = strtotime($end_date);
        if ($start_int > $end_int) {
            $tmp = $start_date;
            $start_date = $end_date;
            $end_date = $tmp;
        }


        //结束时间月份+1，如果是13则为新年的一月份
        $start_arr = explode($explode, $start_date);
        $start_year = intval($start_arr[0]);
        $start_month = intval($start_arr[1]);


        $end_arr = explode($explode, $end_date);
        $end_year = intval($end_arr[0]);
        $end_month = intval($end_arr[1]);


        $data = array();
        $data[] = date('Y-m',strtotime($start_date));

        $tmp_month = $start_month;
        $tmp_year = $start_year;


        //如果起止不相等，一直循环
        while (!(($tmp_month == $end_month) && ($tmp_year == $end_year))) {
            $tmp_month++;
            //超过十二月份，到新年的一月份
            if ($tmp_month > 12) {
                $tmp_month = 1;
                $tmp_year++;
            }
            $data[] = $tmp_year . $explode . str_pad($tmp_month, 2, '0', STR_PAD_LEFT);
        }


        if ($addOne == true) {
            $tmp_month++;
            //超过十二月份，到新年的一月份
            if ($tmp_month > 12) {
                $tmp_month = 1;
                $tmp_year++;
            }
            $data[] = $tmp_year . $explode . str_pad($tmp_month, 2, '0', STR_PAD_LEFT);
        }


        return $data;
    }

    public static function parts(array $array, array $keys)
    {
        foreach (array_keys($array) as $key) {
            if (!in_array($key, $keys)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function filterField(array $array, array $keys)
    {
        foreach (array_keys($array) as $key) {
            if (in_array($key, $keys)) {
                unset($array[$key]);
            }
        }

        return $array;
    }


    public static function requireds(array $array, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }

    public static function changes(array $before, array $after)
    {
        $changes = array('before' => array(), 'after' => array());

        foreach ($after as $key => $value) {
            if (!isset($before[$key])) {
                continue;
            }

            if ($value != $before[$key]) {
                $changes['before'][$key] = $before[$key];
                $changes['after'][$key]  = $value;
            }
        }

        return $changes;
    }

    public static function group(array $array, $key)
    {
        $grouped = array();

        foreach ($array as $item) {
            if (empty($grouped[$item[$key]])) {
                $grouped[$item[$key]] = array();
            }

            $grouped[$item[$key]][] = $item;
        }

        return $grouped;
    }

    public static function index(array $array, $name)
    {
        $indexedArray = array();

        if (empty($array)) {
            return $indexedArray;
        }

        foreach ($array as $item) {
            if (isset($item[$name])) {
                $indexedArray[$item[$name]] = $item;
                continue;
            }
        }

        return $indexedArray;
    }

    public static function rename(array $array, array $map)
    {
        $keys = array_keys($map);

        foreach ($array as $key => $value) {
            if (in_array($key, $keys)) {
                $array[$map[$key]] = $value;
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function filter(array $array, array $specialValues)
    {

        $filtered = array();

        foreach ($specialValues as $key => $value) {
            if (!array_key_exists($key, $array)) {
                continue;
            }

            if (is_array($value)) {
                $filtered[$key] = (array) $array[$key];
            } elseif (is_int($value)) {
                $filtered[$key] = (int) $array[$key];
            } elseif (is_float($value)) {
                $filtered[$key] = (float) $array[$key];
            } elseif (is_bool($value)) {
                $filtered[$key] = (bool) $array[$key];
            } else {
                $filtered[$key] = (string) $array[$key];
            }

            if (empty($filtered[$key])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /** 获取区间内随机红包（符合正态分布）
     * @param $min 红包最小值
     * @param $max 红包最大值
     * @param $num 红包个数
     * @param $total 红包金额
     * @return array
     */
    public static function rand_section($min,$max,$num,$total)
    {

        $data = array();
        if ($min * $num > $total) {
            return array();
        }
        if($max*$num < $total){
            return array();
        }
        while ($num >= 1) {
            $num--;
            $kmix = max($min, $total - $num * $max);
            $kmax = min($max, $total - $num * $min);
            $kAvg = $total / ($num + 1);
            //获取最大值和最小值的距离之间的最小值
            $kDis = min($kAvg - $kmix, $kmax - $kAvg);
            //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
            $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
            $k = sprintf("%.2f", $kAvg + $r);
            $total -= $k;
            $data[] = $k;
        }
        return $data;
    }
}
