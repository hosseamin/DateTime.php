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
$calendar = '';
if(isset($argv))
  {
    if(sizeof($argv) >= 2)
      $date_str = $argv[1];
    if(sizeof($argv) >= 3)
      $calendar = $argv[2];
  }
elseif(isset($_GET))
  {
    if(!isset($_GET['date']))
      die("usage: GET date=<date>[&calendar=<calname>]\n");
    $date_str = $_GET['date'];
    if(isset($_GET['calendar']))
      $calendar = $_GET['calendar'];
    elseif(isset($_GET['cal']))
      $calendar = $_GET['cal'];
  }
$tz = new DateTimeZone("Asia/Tehran");
if(isset($date_str))
  switch(strtolower($calendar))
    {
    case 'persian':
      $sdate = new PersianDateTime($date_str, $tz);
      break;
    case 'islamic':
      $sdate = new IslamicTabularDateTime($date_str, $tz);
      break;
    case 'gregorian':
    default:
      $sdate = new GregorianDateTime($date_str, $tz);
      break;
    }
$pd = new PersianDateTime("now", $tz);
$id = new IslamicTabularDateTime("now", $tz);
$gd = new GregorianDateTime("now", $tz);
$dt = new DateTime("now", $tz);
if(isset($sdate))
  {
    $time = $sdate->getTimestamp();
    $pd->setTimestamp($time);
    $id->setTimestamp($time);
    $gd->setTimestamp($time);
    $dt->setTimestamp($time);
  }
echo "Persian date ".$pd->format('Y-m-d H:i:s w '.
				 '\m\o\n\t\h-\l\e\n\g\t\h: t').
" tzoffset ".($pd->getOffset() / 3600)."\n";
echo "Islamic date ".$id->format('Y-m-d H:i:s w '.
				 '\m\o\n\t\h-\l\e\n\g\t\h: t').
" tzoffset ".($id->getOffset() / 3600)."\n";
echo "Gregorian date ".$gd->format('Y-m-d H:i:s w '.
				   '\m\o\n\t\h-\l\e\n\g\t\h: t').
" tzoffset ".($gd->getOffset() / 3600)."\n";
echo "php's DateTime ".$dt->format('Y-m-d H:i:s w '.
				   '\m\o\n\t\h-\l\e\n\g\t\h: t').
" tzoffset ".($dt->getOffset() / 3600)."\n";
?>