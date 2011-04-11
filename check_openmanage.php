<?php
#
# PNP4Nagios template for check_openmanage 
# Author: 	Trond Hasle Amundsen
# Contact: 	t.h.amundsen@usit.uio.no
# Website:      http://folk.uio.no/trondham/software/check_openmanage.html
# Date: 	2010-03-16
#
# $Id$
#
# Copyright (C) 2008-2011 Trond H. Amundsen
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# Array with different colors
$colors = array("0022ff", "22ff22", "ff0000", "00aaaa", "ff00ff",
		"ffa500", "cc0000", "0000cc", "0080C0", "8080C0",
		"FF0080", "800080", "688e23", "408080", "808000",
		"000000", "00FF00", "0080FF", "FF8000", "800000",
		"FB31FB");

# Color for power usage in watts
$PWRcolor = "dd0000";

# Counters
$count = 0;  # general counter
$f = 0;      # fan probe counter
$t = 0;      # temp probe counter
$a = 0;      # amp probe counter
$v = 0;      # volt probe counter
$e = 0;      # enclosure counter

# Flags
$visited_fan  = 0;
$visited_temp = 0;
$visited_amp  = 0;
$visited_volt = 0;

# Enclosure id
$enclosure_id = '';

# Default title
$def_title = 'Dell OpenManage';

# Loop through the performance data
foreach ($DS as $i) {
	
    # TEMPERATURES (Celsius)
    if (preg_match('/^T/', $NAME[$i])) {
	if ($visited_temp == 0) {
	    ++$count;
	    $visited_temp = 1;
	}

	# Long label
	$NAME[$i] = preg_replace('/^T(\d+)_(.+)/', '$2', $NAME[$i]);
	$NAME[$i] = preg_replace('/_/', ' ', $NAME[$i]);

	# Short label
	$NAME[$i] = preg_replace('/^T(\d+)$/', 'Probe $1', $NAME[$i]);

	$ds_name[$count] = "Chassis Temperatures";

	$warnThresh = "INF";
	$critThresh = "INF";

	if ($WARN[$i] != "") {
	    $warnThresh = $WARN[$i];
	}
	if ($CRIT[$i] != "") {
	    $critThresh = $CRIT[$i];
	}

	$opt[$count] = "--slope-mode --vertical-label \"Celsius\" --title \"$def_title: Chassis Temperatures\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$i#".$colors[$t++].":\"$NAME[$i]\" " ;
	$def[$count] .= "GPRINT:var$i:LAST:\"%6.0lf °C last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%6.0lf °C max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%6.2lf °C avg \\n\" ";
    }

    # WATTAGE PROBE
    if (preg_match('/^W/', $NAME[$i])) {

	# Long label
	$NAME[$i] = preg_replace('/^W(\d+)_(.+)/', '$2', $NAME[$i]);
	$NAME[$i] = preg_replace('/_/', ' ', $NAME[$i]);

	# Short label
	$NAME[$i] = preg_replace('/^W(\d+)$/', 'Probe $1', $NAME[$i]);

	++$count;
	$ds_name[$count] = "Power Consumption";
	$vlabel = "Watt";

	$title = $ds_name[$count];

	$opt[$count] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";

	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}

	$def[$count] .= "VDEF:tot$i=var$i,TOTAL ";
	$def[$count] .= "CDEF:kwh$i=var$i,POP,tot$i,1000,/,60,/,60,/ ";
        $def[$count] .= "CDEF:btu$i=kwh$i,3412.3,* ";

	$def[$count] .= "AREA:var$i#$PWRcolor:\"$NAME[$i]\" " ;
	$def[$count] .= "GPRINT:var$i:LAST:\"%6.0lf W last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%6.0lf W max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%6.2lf W avg \l\" ";

        $def[$count] .= "COMMENT:\" \l\" ";

        $def[$count] .= "COMMENT:\"    Total power used in time period\:\" ";
	$def[$count] .= "GPRINT:kwh$i:AVERAGE:\"%10.2lf kWh\l\" ";

        $def[$count] .= "COMMENT:\"                                    \" ";
	$def[$count] .= "GPRINT:btu$i:AVERAGE:\"%10.2lf BTU\l\" ";
    }

    # AMPERAGE PROBE
    if (preg_match('/^A/', $NAME[$i])) {

	# Long label
	$NAME[$i] = preg_replace('/^A(\d+)_(.+)/', '$2', $NAME[$i]);
	$NAME[$i] = preg_replace('/_/', ' ', $NAME[$i]);

	# Short label
	$NAME[$i] = preg_replace('/^A(\d+)$/', 'Probe $1', $NAME[$i]);
		
	if ($visited_amp == 0) {
	    ++$count;
	    $visited_amp = 1;
	}
	$ds_name[$count] = "Amperage Probes";
	$vlabel = "Ampere";

	$title = $ds_name[$count];

	$opt[$count] = "-X0 --lower-limit 0 --slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE ";
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE ";
	}
	$def[$count] .= "AREA:var$i#".$colors[$a++].":\"$NAME[$i]\":STACK ";
	$def[$count] .= "GPRINT:var$i:LAST:\"%4.1lf A last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%4.1lf A max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%4.3lf A avg \\n\" ";
    }
    

    # VOLTAGE PROBE
    if (preg_match('/^V/', $NAME[$i])) {

	# Long label
	$NAME[$i] = preg_replace('/^V(\d+)_(.+)/', '$2', $NAME[$i]);
	$NAME[$i] = preg_replace('/_/', ' ', $NAME[$i]);

	# Short label
	$NAME[$i] = preg_replace('/^V(\d+)$/', 'Probe $1', $NAME[$i]);
		
	if ($visited_volt == 0) {
	    ++$count;
	    $visited_volt = 1;
	}
	$ds_name[$count] = "Voltage Probes";
	$vlabel = "Volts";

	$title = $ds_name[$count];

	$opt[$count] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$i#".$colors[$v++].":\"$NAME[$i]\" " ;
	$def[$count] .= "GPRINT:var$i:LAST:\"%4.2lf A last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%4.2lf A max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%4.4lf A avg \\n\" ";
    }

    # FANS (RPMs)
    if (preg_match('/^F/', $NAME[$i])) {
	if ($visited_fan == 0) {
	    ++$count;
	    $visited_fan = 1;
	}

	# Long label
	$NAME[$i] = preg_replace('/^F(\d+)_(.+)/', '$2', $NAME[$i]);
	$NAME[$i] = preg_replace('/_/', ' ', $NAME[$i]);

	# Short label
	$NAME[$i] = preg_replace('/^F(\d+)$/', 'Probe $1', $NAME[$i]);

	$ds_name[$count] = "Fan Probes";

	$opt[$count] = "-X0 --slope-mode --vertical-label \"RPMs\" --title \"$def_title: Fan Speeds\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$i#".$colors[$f++].":\"$NAME[$i]\" " ;
	$def[$count] .= "GPRINT:var$i:LAST:\"%6.0lf RPM last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%6.0lf RPM max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%6.2lf RPM avg \\n\" ";
    }
	
    # ENCLOSURE TEMPERATURES (Celsius)
    if (preg_match('/^E(?P<encl>.+?)_t(emp_)?(?P<probe>\d+)/', $NAME[$i], $matches)) {

	$this_id     = $matches['encl'];
	$probe_index = $matches['probe'];

	if ($enclosure_id != $this_id) {
	    $e = 0;
	    ++$count;
	    $enclosure_id = $this_id;
	}

	# Label
	$NAME[$i] = "Probe $probe_index";

	$ds_name[$count] = "Enclosure $enclosure_id Temperatures";

	$warnThresh = "INF";
	$critThresh = "INF";

	if ($WARN[$i] != "") {
	    $warnThresh = $WARN[$i];
	}
	if ($CRIT[$i] != "") {
	    $critThresh = $CRIT[$i];
	}

	$opt[$count] = "--slope-mode --vertical-label \"Celsius\" --title \"$def_title: Enclosure $enclosure_id Temperatures\" ";

	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$i=$rrdfile:$DS[$i]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$i#".$colors[$e++].":\"$NAME[$i]\" " ;
	$def[$count] .= "GPRINT:var$i:LAST:\"%6.0lf °C last \" ";
	$def[$count] .= "GPRINT:var$i:MAX:\"%6.0lf °C max \" ";
	$def[$count] .= "GPRINT:var$i:AVERAGE:\"%6.2lf °C avg \\n\" ";
    }
}
?>
