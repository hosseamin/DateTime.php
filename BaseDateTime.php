<?php
/*
 * Base DateTime Abstract Class similar to php's DateTime Class
 *   Copyright (C) 2012  Hossein Amin
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

abstract class BaseDateTime {
  /* Constants */
  const ATOM = "Y-m-d\TH:i:sP";
  const COOKIE = "l, d-M-y H:i:s T";
  const ISO8601 = "Y-m-d\TH:i:sO";
  const RFC822 = "D, d M y H:i:s O";
  const RFC850 = "l, d-M-y H:i:s T";
  const RFC1036 = "D, d M y H:i:s O";
  const RFC1123 = "D, d M Y H:i:s O";
  const RFC2822 = "D, d M Y H:i:s O";
  const RFC3339 = "Y-m-d\TH:i:sP";
  const RSS = "D, d M Y H:i:s O";
  const W3C = "Y-m-d\TH:i:sP";

  protected $CENTURY = 0;
  
  protected $DAY_OF_WEEK_NAMES;
  protected $DAY_OF_WEEK_SHORT_NAMES;
  protected $MONTH_NAMES;
  protected $MONTH_SHORT_NAMES;
  /*
   * Lowercase Ante meridiem and Post meridiem 
   * Uppercase Ante meridiem and Post meridiem
   * two ways of representing it
   * it could be uppercase/lowercase in english or
   * short or full
   * both are array with 2 element am, pm
   */
  protected $MERIDIEM_STATUS_L;
  protected $MERIDIEM_STATUS_U;
  
  private $_tz;
  
  function __construct($time = "now", $timezone = NULL)
  {
    if($timezone === NULL)
      $this->_tz = new DateTimeZone(date_default_timezone_get());
    else
      $this->_tz = $timezone;
    if($time == "now" || $time == "")
      $this->setTimestamp(time());
    elseif(!$this->_parse_date($time))
      if(!$this->_parse_time($time))
	if(!$this->_parse_date_time($time))
	  if(!$this->_parse_date_time_nonstrict($time))
	    throw new Exception("Can't parse string `$time' as".
				" date and/or time");
    
  }
  abstract public function add($interval);
  abstract public function diff($datetime2, $absolute = false);
  abstract public function getTimestamp();
  abstract public function setDate($year, $month, $days);
  abstract public function setISODate($year ,$week, $day = 1);
  abstract public function setTime($hour, $minute, $second = 0);
  abstract public function setTimestamp($us);
  abstract public function sub($interval);
  // similar to (get/set)Timestamp but it's starts from zero 
  // and measurements are in days
  abstract public function setTimeInDays($d);
  abstract public function getTimeInDays();

  /*
   * internal abstract methods
   */
  abstract public function dayOfYear();
  abstract public function getYear();
  abstract public function getMonth();
  abstract public function getDate();
  abstract public function getHours();
  abstract public function getMinutes();
  abstract public function getSeconds();
  abstract public function getDay();
  abstract public function isLeapYear($y = NULL);
  // gets value represents days length
  // if $m is null returns currect month length
  abstract public function getMonthLength($m = NULL);
  
  public static function createFromFormat($format, $time, $timezone = NULL)
  {
    
  }
  public function format($s)
  {
    $schars = array('d', 'D', 'j', 'l', 'N', 'w', 'z', 'W', 'F',
		    'm', 'n', 't', 'L', 'o', 'Y', 'y', 'a', 'A',
		    'B', 'g', 'G', 'h', 'H', 'i', 's', 'u', 'e');
    $len = strlen($s);
    $escp = false;
    $re = '';
    for($i = 0; $i < $len; ++$i)
      {
	$c = $s[$i];
	if($escp)
	    $re .= $c;
	elseif($c != '\\')
	  if(array_search($c, $schars) !== false)
	    $re .= $this->format_special_char($c);
	  else
	    $re .= $c;
	if($c == '\\')
	  $escp = $escp ? false : true;
	elseif($escp)
	  $escp = false;
      }
    return $re;
  }
  public static function getLastErrors()
  {
    
  }
  public function modify($modify)
  {
    
  }
  public static function __set_state($array)
  {
    // ??
  }
  public function getTimezone()
  {
    return $this->_tz;
  }
  public function setTimezone($timezone)
  {
    $this->_tz = $timezone;
  }
  public function getOffset()
  {
    // simple method
    $ts = $this->getTimestamp();
    $trans = $this->_tz->getTransitions($ts, $ts);
    if(sizeof($trans) > 0)
      return $trans[0]['offset'];
    return false;
  }
  public function __wakeup()
  {
    // ??
  }
  private function _now()
  {
    $this->setTimestamp(time());
  }
  private function _find_timezone($s)
  {
    $tz_ids = DateTimeZone::listIdentifiers();
    if(array_search($s, $tz_ids) !== false)
      return new DateTimezone($s);
    $tz_abbr = DateTimeZone::listAbbreviations();
    $tz_abbr_keys = array_keys($tz_abbr);
    if(($r = array_search(strtolower($s), $tz_abbr_keys)) !== false)
      {
	$tz = $tz_abbr[$tz_abbr_keys[$r]];
	if(sizeof($tz > 0))
	  return new DateTimeZone($tz[0]['timezone_id']);
      }
    return null;
  }
  private function _parse_time($str)
  {
    // hh
    $pttrn = '/^(\d{1,2}) ?(.*)/';
    $pttrn2 = '/^[tT]?(\d{1,2})[.:](\d{1,2}) ?(.*)/';
    $pttrn3 = '/^[tT]?(\d{1,2})[.:](\d{1,2})[.:](\d{1,2}) ?(.*)/';
    $pttrn4 = '/^[tT]?(\d{4})$/';
    $pttrn5 = '/^[tT]?(\d{6})$/';
    if(preg_match($pttrn, $str, $match) == 1)
      {
	$m = array_search($match[2], $this->MERIDIEM_STATUS_L);
	if($m === false)
	  $m = array_search($match[2], $this->MERIDIEM_STATUS_U);
	if($m !== false)
	  {
	    $h = intval($match[1], 10);
	    if($h > 12)
	      return false;
	    $h = ($h % 12) + $m * 12;
	    $this->_now();
	    $this->setTime($h, 0, 0);
	    return true;
	  }
      }
    if(preg_match($pttrn2, $str, $match) == 1)
      {
	if($match[3] == '')
	  {
	    $h = intval($match[1], 10);
	    $i = intval($match[2], 10);
	    if($h > 23 && $i > 59)
	      return false;
	    $this->_now();
	    $this->setTime($h, $i, 0);
	    return true;
	  }
	$m = array_search($match[3], $this->MERIDIEM_STATUS_L);
	if($m === false)
	  $m = array_search($match[3], $this->MERIDIEM_STATUS_U);
	if($m !== false)
	  {
	    $h = intval($match[1], 10);
	    $i = intval($match[2], 10);
	    if($h > 12 && $i > 59)
	      return false;
	    $h = ($h % 12) + $m * 12;
	    $this->_now();
	    $this->setTime($h, $i, 0);
	    return true;
	  }
      }
    if(preg_match($pttrn3, $str, $match) == 1)
      {
	if($match[4] == '')
	  {
	    $h = intval($match[1], 10);
	    $i = intval($match[2], 10);
	    $s = intval($match[3], 10);
	    if($h > 23 && $i > 59 && $s > 59)
	      return false;
	    $this->_now();
	    $this->setTime($h, $i, $s);
	    return true;
	  }
	$m = array_search($match[4], $this->MERIDIEM_STATUS_L);
	if($m === false)
	  $m = array_search($match[4], $this->MERIDIEM_STATUS_U);
	if($m !== false)
	  {
	    $h = intval($match[1], 10);
	    $i = intval($match[2], 10);
	    $s = intval($match[3], 10);
	    if($h > 12 && $i > 59 && $s > 59)
	      return false;
	    $h = ($h % 12) + $m * 12;
	    $this->_now();
	    $this->setTime($h, $i, $s);
	    return true;
	  }
	$tz = $this->_find_timezone($match[4]);
	if($tz !== null)
	  {
	    $h = intval($match[1], 10);
	    $i = intval($match[2], 10);
	    $s = intval($match[3], 10);
	    if($h > 23 && $i > 59 && $s > 59)
	      return false;
	    $this->setTimeZone($tz);
	    $this->_now();
	    $this->setTime($h, $i, $s);
	    return true;
	  }
      }
    if(preg_match($pttrn4, $str, $match) == 1)
      {
	$h = intval(substr($match[1], 0, 2), 10);
	$i = intval(substr($match[1], 2, 2), 10);
	if($h > 23 && $i > 59)
	  return false;
	$this->_now();
	$this->setTime($h, $i, 0);
	return true;
      }
    if(preg_match($pttrn5, $str, $match) == 1)
      {
	$h = intval(substr($match[1], 0, 2), 10);
	$i = intval(substr($match[1], 2, 2), 10);
	$s = intval(substr($match[1], 4, 2), 10);
	if($h > 23 && $i > 59 && $s > 59)
	  return false;
	$this->_now();
	$this->setTime($h, $i, $s);
	return true;
      }
    if(($tz = $this->_find_timezone($str)) !== null)
      {
	$this->setTimeZone($tz);
	$this->_now();
	return true;
      }
  }
  private function _parse_date($str)
  {
    $pttrn1 = '/^(\d{8})$/';
    $pttrn2 = '/^(\d{2})[\/-](\d{2})[\/-](\d{2})$/';
    $pttrn3 = '/^(\d{4})[\/-](\d{2})[\/-](\d{2})$/';
    $pttrn4 = '/^([+-])(\d{4})-(\d{2})-(\d{2})/';
    $pttrn5 = '/^(\d{4})-(\d{1,2})/';
    $pttrn6 = '/^(\d{1,2})[\t .-]*([^\d]+)(\d{4})|([^\d]*)(\d{4})/';
    $pttrn7 = '/^(\d{4})[\t .-]*([^\d]+)|([^-]+)-(\d{1,2})-(\d{4})/';
    if(preg_match($pttrn1, $str, $match) == 1)
      {
	$y = intval(substr($match[1], 0, 4), 10);
	$m = intval(substr($match[1], 4, 2), 10);
	$d = intval(substr($match[1], 6, 2), 10);
	return $this->setDate($y, $m, $d);
      }
    if(preg_match($pttrn2, $str, $match) == 1 || 
       preg_match($pttrn3, $str, $match) == 1)
      {
	$y = intval($match[1], 10);
	$m = intval($match[2], 10);
	$d = intval($match[3], 10);
	if(strlen($match[1]) == 2)
	  $y += ($this->CENTURY - 1) * 100;
	return $this->setDate($y, $m, $d);
      }
    if(preg_match($pttrn4, $str, $match) == 1)
      {
	$this->_now();
	$i = new DateInterval('P0D');
	$i->invert = ($match[1] == '-') ? true : false;
	$i->y = intval($match[2], 10);
	$i->m = intval($match[3], 10);
	$i->d = intval($match[4], 10);
	$this->add($i);
	return true;
      }
    if(preg_match($pttrn5, $str, $match) == 1)
      return $this->setDate(intval($match[1], 10), intval($match[2], 10), 1);
    if(preg_match($pttrn6, $str, $match) == 1)
      {
	$m = -1;
	if(sizeof($match) == 4)
	  $mname = $match[2];
	else
	  $mname = $match[4];
	foreach($this->MONTH_NAMES as $key => $needle)
	  {
	    if(stripos($mname, $needle) === 0)
	      {
		$m = $key;
		break;
	      }
	  }
	if($m == -1)
	  foreach($this->MONTH_SHORT_NAMES as $key => $needle)
	    {
	      if(stripos($mname, $needle) === 0)
		{
		  $m = $key;
		  break;
		}
	    }
	if($m != -1)
	  {
	    if(sizeof($match) == 4)
	      return $this->setDate(intval($match[3], 10), $m + 1,
				    intval($match[1], 10));
	    return $this->setDate(intval($match[5], 10), $m + 1, 1);
	      
	  }
      }
    if(preg_match($pttrn7, $str, $match) == 1)
      {
	if(sizeof($match) == 3)
	  $mname = strtolower($match[2]);
	else
	  $mname = strtolower($match[3]);
	if(($m = array_search($mname, array_map('strtolower',
					$this->MONTH_NAMES))) === false)
	  $m = array_search($mname, array_map('strtolower',
					      $this->MONTH_SHORT_NAMES));
	if($m !== false)
	  {
	    if(sizeof($match) == 3)
	      return $this->setDate(intval($match[1], 10), $m + 1, 1);
	    return $this->setDate(intval($match[5], 10), $m + 1,
				  intval($match[4], 10));
	  }
      }
    return false;
  }
  private function _parse_date_time($str)
  {
    $pttrn1 = '/^(\d{1,2})\/([^\/]+)\/(\d{4}):(\d{2}):(\d{2}):(\d{2}) '.
      '(GMT)?([+-])(\d{2}):?(\d{2})/';
    $pttrn2 = '/^(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})/';
    $pttrn3 = '/^\@([+-]?\d+)/';
    
    if(preg_match($pttrn1, $str, $match) == 1)
      {
	$mname = strtolower($match[2]);
	if(($m = array_search($mname, array_map('strtolower',
					$this->MONTH_NAMES))) === false)
	  $m = array_search($mname, array_map('strtolower',
					      $this->MONTH_SHORT_NAMES));
	if($m !== false)
	  {
	    if($this->setDate(intval($match[3], 10), $m + 1,
			      intval($match[1], 10)) === false)
	      return false;
	    $this->setTime(intval($match[4], 10), intval($match[5]), 
			   intval($match[6], 10));
	    return true;
	  }
      }
    if(preg_match($pttrn2, $str, $match) == 1)
      {
	if($this->setDate(intval($match[1], 10), intval($match[2], 10),
			  intval($match[3], 10)) === false)
	  return false;
	$this->setTime(intval($match[4], 10), intval($match[5], 10),
		       intval($match[6], 10));
	return true;
      }
    if(preg_match($pttrn3, $str, $match) == 1)
      {
	$this->setTimestamp(intval($match[1], 10));
	return true;
      }
    return false;
  }
  private function _parse_date_time_nonstrict($str)
  {
    $pttrn = '/[^0-9]/';
    $res = preg_split($pttrn, $str);
    $rlen = sizeof($res);
    if($rlen < 3 || !is_numeric($res[0]) ||
       !is_numeric($res[1]) || !is_numeric($res[2]))
      return false;
    $y = intval($res[0], 10);
    if($y < 100)
      $y = ($this->CENTURY - 1) * 100 + $y;
    $m = intval($res[1], 10);
    $d = intval($res[2], 10);
    if(!($d > 0))
      $d = 1;
    $h = $i = $s = 0;
    if($rlen > 3)
      {
	$h = intval($res[3], 10);
	if($rlen > 4)
	  {
	    $i = intval($res[4], 10);
	    if($rlen > 5)
	      $s = intval($res[5], 10);
	  }
      }
    if($this->setDate($y, $m, $d) === false)
      return false;
    $this->setTime($h, $i, $s);
    return true;
  }
  /*
   * format subroutines
   */
  private static function format_fixlen_prefix($s, $p, $n)
  {
    $s = strval($s);
    $len = $n - strlen($s);
    if($len > 0)
      return str_repeat($p, $len).$s;
    return $s;
  }
  private function format_special_char($c)
  {
    switch($c)
      {
      case 'd':
	return self::format_fixlen_prefix($this->getDate(), '0', 2);
	break;
      case 'D':
	return $this->DAY_OF_WEEK_SHORT_NAMES[$this->getDay()];
	break;
      case 'j':
	return $this->getDate();
	break;
      case 'l':
	return $this->DAY_OF_WEEK_NAMES[$this->getDay()];
	break;
      case 'N':
	return $this->getDay() + 1;
	break;
      case 'w':
	return $this->getDay();
	break;
      case 'z':
	// the day of year
	return $this->dayOfYear();
	break;
      case 'W':
	return floor($this->dayOfYear() / 7);
	break;
      case 'F':
	return $this->MONTH_NAMES[$this->getMonth()];
	break;
      case 'm':
	return self::format_fixlen_prefix($this->getMonth() + 1, '0', 2);
	break;
      case 'M':
	// A short textual rep of a monthm
	return $this->MONTH_SHORT_NAMES[$this->getMonth()];
	break;
      case 'n':
	return $this->getMonth() + 1;
	break;
      case 't':
	return $this->getMonthLength();
	break;
      case 'L':
	return $this->isLeapYear() ? 1 : 0;
	break;
      case 'o':
	// not implemented
	break;
      case 'Y':
	return self::format_fixlen_prefix($this->getYear(), '0', 4);
	break;
      case 'y':
	return $this->getYear() % 100;
	break;
      case 'a':
	return $this->getHours() < 12 ? $this->MERIDIEM_STATUS_L[0] :
	$this->MERIDIEM_STATUS_L[1];
	break;
      case 'A':
	return $this->getHours() < 12 ? $this->MERIDIEM_STATUS_U[0] :
	$this->MERIDIEM_STATUS_U[1];
	break;
      case 'B':
	break;
      case 'g':
	return ($this->getHours() % 12) + 1;
	break;
      case 'G':
	return $this->getHours();
	break;
      case 'h':
	return self::format_fixlen_prefix(($this->getHours() % 12) + 1,
					  '0', 2);
	break;
      case 'H':
	return self::format_fixlen_prefix($this->getHours(), '0', 2);
	break;
      case 'i':
	return self::format_fixlen_prefix($this->getMinutes(), '0', 2);
	break;
      case 's':
	return self::format_fixlen_prefix($this->getSeconds(), '0', 2);
	break;
      case 'u':
	break;
      case 'e':
	return $this->getTimezone()->getName();
	break;
      }
  }
}
?>