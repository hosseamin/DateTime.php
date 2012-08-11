<?php
/*
 * Islamic Tabular Calendar implemented as php's DateTime Class
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
require_once(__DIR__.'/BaseDateTime.php');

class IslamicTabularDateTime extends BaseDateTime {

  /* Constants */
  const UNIX_YEAR_SHIFT =  1389;
  // (m9)266 * 24 * 3600 +  21 * 24 * 3600;
  const UNIX_YREM_SHIFT = 24796800;
  const UNIX_DAY_OF_WEEK = 5; // 5 means پنج شنبه or Thu
  const DAY_OF_WEEK_SHIFT = 2;

  const _30years = 10631; // 30 * _1year + 11
  const _1leapYear = 355;
  const _1year = 354;
  /*
   * textual values are in utf8 character set
   */
  protected static $CONST_DAY_OF_WEEK_NAMES = 
    array("السبت", "الأحد", "الاثنين", "الثلاثاء", "الأربعاء",
	  "الخميس", "الجمعة");
  // 4 characters day of week
  protected static $CONST_DAY_OF_WEEK_SHORT_NAMES = 
    array("السبت", "الأحد", "الاثنين", "الثلاثاء", "الأربعاء",
	  "الخميس", "الجمعة");
  protected static $CONST_MONTH_NAMES = 
    array("المحرّم", "صفر", "ربيع الأوّل", "ربيع الثاني", "جمادى الأولى",
	  "جمادى الثانية", "رجب", "شعبان", "رمضان",
	  "شوّال", "ذو القعدة", "ذو الحجّة");
  protected static $CONST_MONTH_SHORT_NAMES = 
    array("المحرم", "صفر", "ربيع الأول", "ربيع الثاني", "جمادى الأولى",
	  "جمادى الثانية", "رجب", "شعبان", "رمضان",
	  "شوال", "ذو القعدة", "ذو الحجة");

  protected static $CONST_MERIDIEM_STATUS_L = array('ص', 'م');
  protected static $CONST_MERIDIEM_STATUS_U = array('AM', 'PM');

  protected $CENTURY = 14;
  /*
   * variables are year number, month number and the reminder of 
   * month in sec or in miliseconds
   */
  private $_year = 0;
  private $_month = 0;
  private $_mrem = 0;
  
  function __construct($time = "now", $timezone = NULL)
  {
    $this->DAY_OF_WEEK_NAMES =& self::$CONST_DAY_OF_WEEK_NAMES;
    $this->DAY_OF_WEEK_SHORT_NAMES =& self::$CONST_DAY_OF_WEEK_SHORT_NAMES;
    $this->MONTH_NAMES =& self::$CONST_MONTH_NAMES;
    $this->MONTH_SHORT_NAMES =& self::$CONST_MONTH_SHORT_NAMES;
    $this->MERIDIEM_STATUS_L =& self::$CONST_MERIDIEM_STATUS_L;
    $this->MERIDIEM_STATUS_U =& self::$CONST_MERIDIEM_STATUS_L;

    parent::__construct($time, $timezone);
  }
  public function add($interval)
  {
    $this->_year += $interval->y;
    $this->_addmonth($interval->m);
    $this->_adddays($interval->d);
    $this->_addtime($interval->h, $interval->i, $interval->s);
  }
  public function diff($datetime2, $absolute = false)
  {
    
  }
  public static function getLastErrors()
  {
    return parent::getLastErrors();
  }
  public function getOffset()
  {
    return parent::getOffset();
  }
  public function getTimestamp()
  {
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array($isleap);
    $yrem = self::month2days($this->_month, $mths) * 3600 * 24  + $this->_mrem;
    if($this->_year < self::UNIX_YEAR_SHIFT ||
       ($this->year == self::UNIX_YEAR_SHIFT && 
	self::UNIX_REM_YEAR_IN_SEC > $yrem))
      return false;
    $ts = (self::years_range_to_days(self::UNIX_YEAR_SHIFT, $this->_year) 
	   * 3600 * 24) + $yrem - self::UNIX_YREM_SHIFT;
    $offset = $this->_getOffset_ts($ts);
    if($offset === false)
      return $ts;
    return $ts - $offset;
  }
  public function setDate($year, $month, $days)
  {
    if($month >= 12 || $month <= 0)
      return false;
    $month -= 1;
    $isleap = self::is_leap_year($year);
    $mths = self::get_months_array($isleap);
    if($days > $mths[$month])
      return false;
    $this->_year = $year;
    $this->_month = $month;
    $cd = self::sec2days($this->_mrem);
    $this->_mrem += (($days-1) - $cd) * 3600 * 24;
  }
  public function setISODate($year ,$week, $day = 1)
  {
    
  }
  public function setTime($hour, $minute, $second = 0)
  {
    if($hour >= 24 || $minute >= 60 || $second >= 60 ||
       $hour < 0 || $minute < 0 || $second < 0)
      return false;
    $ch = self::sec2hours($this->_mrem);
    $cm = self::sec2mins($this->_mrem);
    $cs = self::sec2secs($this->_mrem);
    $this->_mrem += ($hour - $ch) * 3600 + ($minute - $cm) * 60 + 
      ($second - $cs);
  }
  public function setTimestamp($us)
  {
    $us += $this->_getOffset_ts($us);
    $this->_year = self::UNIX_YEAR_SHIFT;
    $this->_month = 0;
    $this->_mrem = 0;
    $us += self::UNIX_YREM_SHIFT;
    $this->_adddays(self::sec2days($us));
    $this->_mrem += $us % (3600 * 24);
  }
  public function sub($interval)
  {
    $this->_year -= $interval->y;
    $this->_submonths($interval->m);
    $this->_subdays($interval->d);
    $this->_subtime($interval->h, $interval->i, $interval->s);
  }
  /*
   * internal methods
   */
  public function dayOfYear()
  {
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array($isleap);
    return self::month2days($this->_month, $mths) + 
      self::sec2days($this->_mrem);
  }
  public function getYear()
  {
    return $this->_year;
  }
  public function getMonth()
  {
    return $this->_month;
  }
  public function getDate()
  {
    return self::sec2days($this->_mrem) + 1;
  }
  public function getHours()
  {
    return self::sec2hours($this->_mrem);
  }
  public function getMinutes()
  {
    return self::sec2mins($this->_mrem);
  }
  public function getSeconds()
  {
    return self::sec2secs($this->_mrem);
  }
  public function getDay()
  {
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array($isleap);
    $d =  self::years_range_to_days(0, $this->_year) + 
      self::month2days($this->_month, $mths) + self::sec2days($this->_mrem)
      + self::DAY_OF_WEEK_SHIFT;
    
    return $d % 7;
  }  
  private function _getOffset_ts($ts)
  {
    $trans = $this->getTimezone()->getTransitions($ts, $ts);
    if(sizeof($trans) > 0)
      return $trans[0]['offset'];
    return false;
  }
  public function isLeapYear()
  {
    return self::is_leap_year($this->_year);
  }
  public function getMonthLength($m = NULL)
  {
    if($m === NULL)
      $m = $this->_month;
    if($m < 0 || $m >= 12)
      return false;
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array();
    return $mths[$m];
  }
  private function _addmonth($m)
  {
    $m += $this->_month;
    if($m >= 12)
      {
	$this->_year += floor($m / 12);
	$m = $m % 12;
      }
    $this->_month = $m;
  }
  private function _adddays($d)
  {
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array($isleap);
    $sd = self::sec2days($this->_mrem) + 1;
    $d += $sd;
    while($d > $mths[$this->_month])
    {
	$d -= $mths[$this->_month++];
	if($this->_month >= 12)
	{
	  ++$this->_year;
	  $isleap = $this->isLeapYear();
	  $mths = self::get_months_array($isleap);
	  $this->_month = 0;
	}
    }
    $this->_mrem += ($d - $sd) * 24 * 3600;
  }
  private function _addtime($h, $m, $s)
  {
    $srem = $this->_mrem % (24 * 3600);
    $x = $h * 3600 + $m * 60 + $s;
    if($srem + $x > 24 * 3600)
      {
	$this->_adddays(floor(($srem + $x) / (24 * 3600)));
	$x = ($x + $srem) % (24 * 3600) - $srem;
      }
    $this->_mrem += $x;
  }
  private function _submonths($m)
  {
     if($m > 12)
      {
	$this->_year -= floor($m / 12);
	$m = $m % 12;
      }
     if($m > $this->_month)
       {
	 --$this->_year;
	 $this->_month = 12 - ($m - $this->_month);
       }
     else
       $this->_month -= $m;
  }
  private function _subdays($d)
  {
    $isleap = $this->isLeapYear();
    $mths = self::get_months_array($isleap);
    $sd = self::sec2days($this->_mrem);
    if($d - $sd <= 0)
      $this->_mrem -= $d * 3600 * 24;
    else
      {
	--$this->_month;
	while($d > $mths[$this->_month])
	  {
	    $d -= $mths[$this->_month--];
	    if($this->_month < 0)
	      {
		--$this->_year;
		$isleap = $this->isLeapYear();
		$mths = self::get_months_array($isleap);
		$this->_month += 12;
	      }
	  }
	$this->_mrem += ($mths[$this->_month] - ($d - $sd) - $sd) * 24 * 3600;
      }
  }
  private function _subtime($h, $m, $s)
  {
    $srem = $this->_mrem % (24 * 3600);
    $x = $h * 3600 + $m * 60 + $s;
    if($x - $srem > 0)
      {
	$this->_subdays($tmp = ceil(($x - $srem) / (24 * 3600)));
	if($tmp * 24 * 3600 > $x - $srem)
	  $x = ($x % (24 * 3600)) - (24 * 3600);
	else
	  $x = $x % (24 * 3600);
      }
    $this->_mrem -= $x;
  }
  /*
   * static methods
   */
  public static function is_leap_year($y)
  {
    switch($y % 30)
      {
      case 1:
      case 4:
      case 6:
      case 9:
      case 12:
      case 15:
      case 17:
      case 20:
      case 23:
      case 25:
      case 28:
	return true;
      }
    return false;
  }
  public static function sec2days($s)
  {
    return floor($s / (3600 * 24));
  }
  public static function sec2hours($s)
  {
    return floor(($s % (3600 * 24)/3600));
  }
  public static function sec2mins($s)
  {
    return floor(($s % (3600))/60);
  }
  public static function sec2secs($s)
  {
    return floor(($s % (60)));
  }
  public static function get_months_array($isleap)
  {
    if($isleap)
      return array(30, 29, 30, 29, 30, 29, 30, 29,
		   30, 29, 30, 30);
    return array(30, 29, 30, 29, 30, 29, 30, 29,
		 30, 29, 30, 29);
  }
  public static function month2days($m, $mths)
  {
    $d = 0;
    while(--$m >= 0)
	$d += $mths[$m];
    return $d;
  }
  public static function years_range_to_days($y, $ey)
  {
    if($ey < $y)
      return null;
    $delta = $ey - $y;
    $d = floor($delta / 30) * self::_30years;
    $y += floor($delta / 30) * 30;
    while($y < $ey)
      {
	if(self::is_leap_year($y))
	  {
	    $d += self::_1leapYear;
	  }else
	  {
	    $d += self::_1year;
	  }
	++$y;
      }
    return $d;
  }
}
?>