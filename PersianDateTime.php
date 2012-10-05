<?php
/*
 * Persian calendar implemented as php's DateTime Class
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
require_once('RYMDateTime.php');

class PersianDateTime extends RYMDateTime {

  /* Constants */
  protected $UNIX_YEAR_SHIFT = 1348;
  protected $UNIX_YREM_SHIFT = 24710400;
  protected $DAY_OF_WEEK_SHIFT = 4;
  protected $ISO_START_DOW = 0;
  
  const _33years = 12053;
  protected $_1leapYear = 366;
  protected $_1year = 365;

  /*
   * textual values are in utf8 character set
   */
  protected static $CONST_DAY_OF_WEEK_NAMES = 
    array("شنبه", "یک شنبه", "دو شنبه", "سه شنبه",
	  "چهار شنبه", "پنج شنبه", "جمعه");
  // 4 characters day of week
  protected static $CONST_DAY_OF_WEEK_SHORT_NAMES = 
    array("شنبه", "یک ش", "دو ش", "سه ش",
	  "چهار ش", "پنج ش", "جمعه" );
  protected static $CONST_MONTH_NAMES = 
    array("فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد",
	  "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند");
  protected static $CONST_MONTH_SHORT_NAMES = 
    array("فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد",
	  "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند");

  protected static $CONST_MERIDIEM_STATUS_L = array('ق.ظ', 'ب.ظ');
  protected static $CONST_MERIDIEM_STATUS_U = array('AM', 'PM');

  protected $CENTURY = 14;

  protected $_year = 0;
  protected $_month = 0;
  protected $_mrem = 0;
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
  public static function getCalendarName()
  {
    return 'persian';
  }
  public function isLeapYear($y = NULL)
  {
    if($y === NULL)
      $y = $this->_year;
    //exception where found in wikipedia site
    //http://fa.wikipedia.org/wiki/سال_کبیسه
    if($y <= 1472 && $y >= 1343)
      $twenty_one_exp = 22;
    else
      $twenty_one_exp = 21;
    switch($y % 33)
      {
      case 1:
      case 5:
      case 9:
      case 13:
      case 17:
      case $twenty_one_exp:
      case 26:
      case 30:
	return true;
      }
    return false; 
  }
  protected function getMonthsArray($isleap = NULL)
  {
    if($isleap === NULL)
      $isleap = $this->isLeapYear();
    if($isleap)
      return array(31, 31, 31, 31, 31, 31,
		   30, 30, 30, 30, 30, 30);
    return array(31, 31, 31, 31, 31, 31,
		 30, 30, 30, 30, 30, 29);
  }
  protected function yearsRangeToDays($y, $ey)
  {
    if($ey < $y)
      return null;
    $delta = $ey - $y;
    $d = floor($delta / 33) * self::_33years;
    $y += floor($delta / 33) * 33;
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
}
?>