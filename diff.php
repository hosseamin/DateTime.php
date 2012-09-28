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
require_once(__DIR__.'/PersianDateTime.php');
require_once(__DIR__.'/IslamicTabularDateTime.php');
require_once(__DIR__.'/GregorianDateTime.php');

header("content-type: text/plain;charset=utf8");
$tz = new DateTimeZone("Asia/Tehran");
$calendar = '';
if(isset($argv))
  {
    if(sizeof($argv) < 3)
      die("usage: <date> <date> [<calendar>]\n");
    $date_str = $argv[1];
    $date2_str = $argv[2];
    if(sizeof($argv) >= 4)
      $calendar = $argv[3];
  }
elseif(isset($_GET))
  {
    if(!isset($_GET['date1']) && !isset($_GET['date2']))
      die("usage: GET date1=<date>&date2=<date>[&calendar=<calname>]\n");
    $date_str = $_GET['date1'];
    $date2_str = $_GET['date2'];
    if(isset($_GET['calendar']))
      $calendar = $_GET['calendar'];
    elseif(isset($_GET['cal']))
      $calendar = $_GET['cal'];
  }
switch(strtolower($calendar))
  {
  case 'persian':
    $sdate = new PersianDateTime($date_str, $tz);
    $edate = new PersianDateTime($date2_str, $tz);
    break;
  case 'islamic':
    $sdate = new IslamicTabularDateTime($date_str, $tz);
    $edate = new IslamicTabularDateTime($date2_str, $tz);
    break;
  case 'gregorian':
  default:
    $sdate = new GregorianDateTime($date_str, $tz);
    $edate = new GregorianDateTime($date2_str, $tz);
    break;
  }
$diff = $sdate->diff($edate);
echo $diff->format("%R %Y years, %M months, %D days").", ";
echo $diff->format("%H Hours, %I Minutes, %S Seconds")."\n";
?>