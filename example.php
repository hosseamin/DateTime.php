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

header("content-type: text/plain;charset=utf8");
$tz = new DateTimeZone("Asia/Tehran");
$t = time();
$pd = new PersianDateTime("now", $tz);
$id = new IslamicTabularDateTime("now", $tz);
$gd = new DateTime("now", $tz);
echo "Persian date ".$pd->format('Y-m-d H:i:s w')."\n";
echo "Islamic date ".$id->format('Y-m-d H:i:s w')."\n";
echo "Gregorian date ".$gd->format('Y-m-d H:i:s w')."\n";
?>