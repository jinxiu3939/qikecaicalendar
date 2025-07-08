<?php
// namespace qikecai\calendar_test;

require 'vendor/autoload.php';

use Qikecai\Ccalendar\Calendar;
use Qikecai\Ccalendar\Julian;
use Qikecai\Ccalendar\SolarTerm;
use Qikecai\Ccalendar\ChineseCalendar;

// 开启php错误提示
error_reporting(E_ALL);
// 开启display_errors
ini_set('display_errors', 'On');
// 开启trace
ini_set('xdebug.var_display_max_depth', '10');

// 新的日历对象
$Calendar = new Calendar();

// 创建一个日历，createCalendar方法4个参数分别是:int年,int月,int日=0,int时=-1
// createCalendar方法在指定的年份不在-1000至3000之内时throw DomainException异常
$calendar = $Calendar->createCalendar(2020,5,15);

// ------------ 以下代码部分处理当天的日期转换 --------------- //

$current_gregorian_str = $calendar['w'] . ' ' . $calendar['y'] . '-' . $calendar['m'] . '-' . $calendar['d'];

$current_jq_str = !empty($calendar['solar_terms']) ? ' 节气:' . $calendar['solar_terms'][0] . ' 定:' . $calendar['solar_terms'][1] : '';

$current_gz_str = !empty($calendar['gz']) ? $calendar['gz']['y']['s'] . '年'.$calendar['gz']['m']['s'] . '月'.$calendar['gz']['d']['s'] . '日' : '';

$current_lunar_str = !empty($calendar['lunar']) ? $calendar['lunar'][0] . '年' . $calendar['lunar'][1] . $calendar['lunar'][2] : '';

print '当天：' . "\n";

print '【' . $current_gregorian_str . ' 农历:' . $current_lunar_str . ' 干支:' . $current_gz_str . $current_jq_str . '】'."\n";

print "\n";

// ------------ 以下部分是日历表数据转换 --------------- //

print '日历表：' . "\n";

foreach ($calendar['days'] as [$k,$day]){
    // 公历
    $gregorian_str = $day['gregorian']['w'] . ' ' . $day['gregorian']['y'] . '-' . $day['gregorian']['m'] . '-' . $day['gregorian']['d'];

    // 节气
    $jq_str = (isset($day['solar_terms']) && isset($day['solar_terms'][$k])) ? ' 节气:' . $day['solar_terms'][$k][0] . ' 定:' . $day['solar_terms'][$k][1] : '';

    // 干支
    $gz_str = !empty($day['gz']) ? $day['gz']['y']['s'] . '年' . $day['gz']['m']['s'] . '月' . $day['gz']['d']['s'] . '日' : '';

    // 农历
    $lunar_str = !empty($day['lunar']) ? $day['lunar'][0] . '年' . $day['lunar'][1] . $day['lunar'][2] : '';

    print $gregorian_str . ' 农历:' . $lunar_str .' 干支:'. $gz_str . $jq_str . "\n";

}

// Julian::julianDay参数(int年,int月=1,int日=1,int时=12,int分=0,int秒=0,int毫秒=0)
$jd = Julian::julianDay(2011,8,9,5,24,35);
var_dump($jd);

$jd = 2455782.7254051;

// Julian::julianDayToDate参数(float儒略日)
// 返回一个array,是字符串索引的整数值['Y' int年, 'n' int月, 'j' int日, 'G' int时, 'i' int分, 's' int秒,'u' int毫秒]
$date = Julian::julianDayToDateArray($jd);
printf('%d-%d-%d %d:%d:%d',$date['Y'],$date['n'],$date['j'],$date['G'],$date['i'],$date['s']);

print "\n";

// 或者使用jdToDateTime方法直接转换为DateTime
// 该方法第二个参数为时区名称，默认为: 'Asia/Shanghai'
$dateTime = Julian::jdToDateTime($jd, 'Asia/Shanghai');
print $dateTime->format(\DateTimeInterface::RFC3339);

// 日期直接转换成简化儒略日
// 如果日期在1858年11月17日凌晨之前，则返回0
$mjd = Julian::modifiedJulianDay(2011,8,9,5,24,35);
print $mjd;

print "\n";

// 简化儒略日转儒略日
$jd = Julian::mjdTojulianDay($mjd);
print $jd;

$st_names = ['春分', '清明', '谷雨', '立夏', '小满', '芒种', '夏至', '小暑', '大暑', '立秋', '处暑', '白露',
                          '秋分', '寒露', '霜降', '立冬', '小雪', '大雪', '冬至', '小寒', '大寒', '立春', '雨水', '惊蛰'];

// 该方法第二个参数为时区名称，默认为: 'Asia/Shanghai'
$sts = SolarTerm::solarTerms(2021);

foreach ($sts as $stv){
    printf("%s: %s \n", $st_names[$stv['i']], $stv['d']->format(\DateTimeInterface::RFC3339));
}

$year = 2020;
$month = 5;
$day = 26;

$lunarDateArray = ChineseCalendar::gregorianToLunar($year,$month,$day);
$leapstr = $lunarDateArray['leap'] === 1 ? '(闰)' : '';
printf("公历 %'.04d-%'.02d-%'.02d 是农历: %'.04d年 %s%d月 %d日 \n", $year, $month, $day, $lunarDateArray['Y'], $leapstr,$lunarDateArray['n'],$lunarDateArray['j']);
print "\n";

// 默认时区: 'Asia/Shanghai'
$gdate = ChineseCalendar::lunarToGregorian($lunarDateArray['Y'],$lunarDateArray['n'],$lunarDateArray['j'],$lunarDateArray['leap']);
print '农历'.$lunarDateArray['Y'].'年'.$leapstr.$lunarDateArray['n'].'月'.$lunarDateArray['j'].'是公历' . ': ' . $gdate->format('Y-m-d') ."\n";
print "\n";

$year = 2020;
$month = 5;
$day = 26;
$hours = 19;
$scs = ChineseCalendar::sexagenaryCycle($year, $month, $day, $hours);
printf("%d年%d月%d日%d时的干支是: %s%s(%s)年 %s%s月 %s%s日 %s%s时 \n",$year,$month,$day,$hours,
    $Calendar->getLang('heavenly_stems')[$scs['y']['g']],$Calendar->getLang('earthly_branches')[$scs['y']['z']],$Calendar->getLang('symbolic_animals')[$scs['y']['z']],
    $Calendar->getLang('heavenly_stems')[$scs['m']['g']],$Calendar->getLang('earthly_branches')[$scs['m']['z']],
    $Calendar->getLang('heavenly_stems')[$scs['d']['g']],$Calendar->getLang('earthly_branches')[$scs['d']['z']],
    $Calendar->getLang('heavenly_stems')[$scs['h']['g']],$Calendar->getLang('earthly_branches')[$scs['h']['z']]);

$month = 5;
$day = 26;
$star_sign = ['水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手', '摩羯'];
$signIndex = ChineseCalendar::signIndex($month, $day);
printf("%d月%d日出生 属%s座", $month, $day, $star_sign[$signIndex]);