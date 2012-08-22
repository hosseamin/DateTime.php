<?php
/*
 * DateTime.php DateTime implementation for several calendars
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
// Regular Year Month Date Time
abstract class RYMDateTime extends BaseDateTime {

  /* Constants */
  protected $UNIX_YEAR_SHIFT;
  protected $UNIX_REM_YEAR_IN_SEC;
  protected $DAY_OF_WEEK_SHIFT;
  protected $_1leapYear;
  protected $_1year;

  /*
   * variables are year number, month number and the reminder of 
   * month in sec or in miliseconds
   */
  protected $_year = 0;
  protected $_month = 0;
  protected $_mrem = 0;
  
  function __construct($time = "now", $timezone = NULL)
  {
    parent::__construct($time, $timezone);
  }
  abstract protected function getMonthsArray($isleap = NULL);

  public function add($interval)
  {
    if($interval->invert)
      return $this->sub($interval);
    $this->_year += $interval->y;
    $this->_addmonth($interval->m);
    $this->_adddays($interval->d);
    $this->_addtime($interval->h * 3600 +
		    $interval->i * 60 + $interval->s);
  }
  public function diff($dt2, $absolute = false)
  {
    $i = new DateInterval("P0Y");
    $dt1 = $this;
  start_get_diff:
    $yd = $dt2->getYear() - $dt1->getYear();
    $md = $dt2->getMonth() - $dt1->getMonth();
    $dd = $dt2->getDate() - $dt1->getDate();
    $hd = $dt2->getHours() - $dt1->getHours();
    $id = $dt2->getMinutes() - $dt1->getMinutes();
    $sd = $dt2->getSeconds() - $dt1->getSeconds();

    if(!isset($invert))
      {
	if($yd != 0)
	  $invert = $yd;
	elseif($md != 0)
	  $invert = $md;
	elseif($dd != 0)
	  $invert = $md;
	elseif($hd != 0)
	  $invert = $hd;
	elseif($id != 0)
	  $invert = $id;
	else
	  $invert = $sd;
	if($invert < 0)
	  {
	    $dt1 = $dt2;
	    $dt2 = $this;
	    goto start_get_diff;
	  }
      }
    // eval
    if($sd < 0)
      {
	--$id;
	$sd = 60 + $sd;
      }
    if($id < 0)
      {
	--$hd;
	$id = 60 + $id;
      }
    if($hd < 0)
      {
	--$dd;
	$hd = 60 + $hd;
      }
    if($dd < 0)
      {
	//TODO::find which month to get
	$mlen = 30;
	--$md;
	$dd = $mlen + $dd;
      }
    if($md < 0)
      {
	--$yd;
	$md = 12 + $md;
      }
    $i->y = abs($yd);
    $i->m = $md;
    $i->d = $dd;
    $i->h = $hd;
    $i->i = $id;
    $i->s = $sd;
    $i->days = self::sec2days($dt2->getTimestamp() -
				  $dt1->getTimestamp());
    $i->invert = $invert >= 0 ? false : true;
    return $i;
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
    $mths = $this->getMonthsArray($isleap);
    $yrem = $this->monthTodays($this->_month, $mths) * 3600 * 24  + $this->_mrem;
    if($this->_year < $this->UNIX_YEAR_SHIFT ||
       ($this->_year == $this->UNIX_YEAR_SHIFT && 
	$this->UNIX_YREM_SHIFT > $yrem))
      return false;
    $ts = ($this->yearsRangeToDays($this->UNIX_YEAR_SHIFT, $this->_year)
	   * 3600 * 24) + $yrem - $this->UNIX_YREM_SHIFT;
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
    $isleap = $this->isLeapYear($year);
    $mths = $this->getMonthsArray($isleap);
    if($days > $mths[$month])
      return false;
    $this->_year = $year;
    $this->_month = $month;
    $cd = $this->sec2days($this->_mrem);
    $this->_mrem += (($days-1) - $cd) * 3600 * 24;
  }
  public function setISODate($year ,$week, $day = 1)
  {
    
  }
  public function setTime($hour, $minute, $second = 0)
  {
    if($hour >= 24 || $minute >= 60 || $second >= 60 ||
       $hour < 0 || $minute < 0 || $second < 0)
      return false;;
    $ch = $this->sec2hours($this->_mrem);
    $cm = $this->sec2mins($this->_mrem);
    $cs = $this->sec2seconds($this->_mrem);
    $this->_mrem += ($hour - $ch) * 3600 + ($minute - $cm) * 60 + 
      ($second - $cs);
  }
  public function setTimestamp($us)
  {
    $us += $this->_getOffset_ts($us);
    $this->_year = $this->UNIX_YEAR_SHIFT;
    $this->_month = 0;
    $this->_mrem = 0;
    $us = $us + $this->UNIX_YREM_SHIFT;
    $this->_adddays($this->sec2days($us));
    $this->_mrem += $us % (3600 * 24);
  }
  public function sub($interval)
  {
    if($interval->invert)
      return $this->add($interval);
    $this->_year -= $interval->y;
    $this->_submonths($interval->m);
    $this->_subdays($interval->d);
    $this->_subtime($interval->h * 3600 + 
		    $interval->i * 60 + $interval->s);
  }
  public function setTimeInDays($d)
  {
    // problem no way to find timezone offset
    $this->_year = 0;
    $this->_month = 0;
    $this->_mrem = 0;
    $this->_adddays($d);
  }
  public function getTimeInDays()
  {
    // how to find timezone offset
    return $this->yearsRangeToDays($this->UNIX_YEAR_SHIFT, $this->_year) +
      $this->monthToDays($this->_month) + $this->sec2days($this->_mrem);
  }
  /*
   * internal methods
   */
  public function dayOfYear()
  {
    return $this->monthToDays($this->_month) + 
      $this->sec2days($this->_mrem);
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
    return $this->sec2days($this->_mrem) + 1;
  }
  public function getHours()
  {
    return $this->sec2hours($this->_mrem);
  }
  public function getMinutes()
  {
    return $this->sec2mins($this->_mrem);
  }
  public function getSeconds()
  {
    return $this->sec2seconds($this->_mrem);
  }
  public function getDay()
  {
    $d = $this->yearsRangeToDays(0, $this->_year) + 
      $this->monthToDays($this->_month) + $this->sec2days($this->_mrem)
      + $this->DAY_OF_WEEK_SHIFT;
    
    return $d % 7;
  }  
  private function _getOffset_ts($ts)
  {
    $trans = $this->getTimezone()->getTransitions($ts, $ts);
    if(sizeof($trans) > 0)
      return $trans[0]['offset'];
    return false;
  }
  public function getMonthLength($m = NULL)
  {
    if($m === NULL)
      $m = $this->_month;
    if($m < 0 || $m >= 12)
      return false;
    $isleap = $this->isLeapYear();
    $mths = $this->getMonthsArray();
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
    $mths = $this->getMonthsArray($isleap);
    $sd = self::sec2days($this->_mrem) + 1;
    $d += $sd;
    while($d > $mths[$this->_month])
    {
	$d -= $mths[$this->_month++];
	if($this->_month >= 12)
	{
	  ++$this->_year;
	  $isleap = $this->isLeapYear();
	  $mths = $this->getMonthsArray($isleap);
	  $this->_month = 0;
	}
    }
    $this->_mrem += ($d - $sd) * 24 * 3600;
  }
  private function _addtime($s)
  {
    $srem = $this->_mrem % (24 * 3600);
    if($srem + $s > 24 * 3600)
      {
	$this->_adddays(floor(($srem + $s) / (24 * 3600)));
	$s = ($s + $srem) % (24 * 3600) - $srem;
      }
    $this->_mrem += $s;
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
    $mths = $this->getMonthsArray($isleap);
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
		$mths = $this->getMonthsArray($isleap);
		$this->_month += 12;
	      }
	  }
	$this->_mrem += ($mths[$this->_month] - ($d - $sd) - $sd) * 24 * 3600;
      }
  }
  private function _subtime($s)
  {
    $srem = $this->_mrem % (24 * 3600);
    if($s - $srem > 0)
      {
	$this->_subdays($tmp = ceil(($s - $srem) / (24 * 3600)));
	if($tmp * 24 * 3600 > $s - $srem)
	  $s = ($s % (24 * 3600)) - (24 * 3600);
	else
	  $s = $s % (24 * 3600);
      }
    $this->_mrem -= $s;
  }
  protected function monthToDays($m, $mths = NULL)
  {
    if($mths === NULL)
      $mths = $this->getMonthsArray();
    $d = 0;
    while(--$m >= 0)
	$d += $mths[$m];
    return $d;
  }
  protected function yearsRangeToDays($y, $ey)
  {
    if($ey < $y)
      return null;
    while($y < $ey)
      {
	if($this->isLeapYear($y))
	  {
	    $d += $this->_1leapYear;
	  }else
	  {
	    $d += $this->_1year;
	  }
	++$y;
      }
    return $d;
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
  public static function sec2seconds($s)
  {
    return floor(($s % (60)));
  }
}
?>