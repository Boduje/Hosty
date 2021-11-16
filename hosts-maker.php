<?php

// Current working directory of this file
$dir = getcwd().'/';


// Path for the raw hosts file
$hosts_list = $dir.'hosts-raw.txt';


// Read the file into an array
$lines = file($hosts_list, FILE_IGNORE_NEW_LINES);  // FILE_IGNORE_NEW_LINES, FILE_SKIP_EMPTY_LINES


// Sort the hosts list array
sort($lines);


// Remove any duplicates
$lines = array_unique($lines);


// Count the number of host entries for later display
$hosts_count = count($lines);


// Determine line suffix
$ls = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? "\r\n" : "\n";


// Build the basic hosts file
$hosts_text = "
# Copyright (c) 1993-1999 Microsoft Corp.$ls
#$ls
# This is a sample HOSTS file used by Microsoft TCP/IP for Windows.$ls
#$ls
# This file contains the mappings of IP addresses to host names. Each$ls
# entry should be kept on an individual line. The IP address should$ls
# be placed in the first column followed by the corresponding host name.$ls
# The IP address and the host name should be separated by at least one$ls
# space.$ls
#$ls
# Additionally, comments (such as these) may be inserted on individual$ls
# lines or following the machine name denoted by a '#' symbol.$ls
#$ls
# For example:$ls
#$ls
#\t102.54.94.97\trhino.acme.com\t# source server$ls
#\t38.25.63.10\tx.acme.com\t# x client host$ls
$ls$ls
127.0.0.1\tlocalhost$ls$ls
# Special Entries$ls$ls
0.0.0.0\t0.0.0.0\t# fix for traceroute and netstat display anomaly$ls$ls";


// Default variable is empty
$list_text = '';


// Add all the hosts from the array to the existing hosts text
foreach ($lines as $l)
{
	$hosts_text .= "0.0.0.0\t".$l."$ls";
	$list_text .= $l."$ls";
}


/**
 * UPDATE THE SYSTEM HOSTS FILE
 */

// TEST
//$hosts_file     = $dir.'test-hosts.txt';
//$hosts_file_bak = $dir.'test-hosts.txt.bak';

// LIVE
$hosts_file     = 'C:\Windows\System32\drivers\etc\hosts';
$hosts_file_bak = 'C:\Windows\System32\drivers\etc\hosts_bak';


// Make a backup copy of the hosts file in case something goes wrong
copy($hosts_file, $hosts_file_bak);


// Open the hosts file for writing
if (!$hosts_file = fopen($hosts_file,'w'))
{
	exit('The system hosts file cannot be opened at this time. If you have recently updated the hosts file, please wait one minute and try again.');
}


// If writing to the file fails, rename the backup file to restore hosts file
if (!fwrite($hosts_file, $hosts_text))
{
	rename($hosts_file_bak, $hosts_file);
	echo 'There was an error encountered when trying to write the new hosts file.  The prior hosts file has been restored.';
}


// Delete the copy that was made
unlink($hosts_file_bak);


// Close the file
fclose($hosts_file);


/**
 * UPDATE THE SOURCE LIST
 */

// Give a name for source backup file
$hosts_list_bak = $hosts_list.'.bak';


// Make a backup copy of the hosts file in case something goes wrong
copy($hosts_list, $hosts_list_bak);


// We will now update the raw, source list with the new array
if (!$list = fopen($hosts_list, 'w'))
{
	exit('The source list for the hosts file cannot be opened at this time.');
}


// If writing to the source file fails, rename the backup file to restore it
if (!fwrite($list, $list_text))
{
	rename($hosts_list_bak, $hosts_list);
	echo 'There was an error encountered when updating the hosts list source file.  The prior source has been restored.';
}


// Delete the source copy that was made
unlink($hosts_list_bak);


// Close the source list
fclose($list);


/**
 * EVERYTHING IS COMPLETE
 */
echo '
	<h4>The hosts file has been updated successfully.</h4>
	<p>There are now '.$hosts_count.' entries in the hosts file.</p>
	<p>Internet browsing might be delayed momentarily while the system loads the new file.</p>
';