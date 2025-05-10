<?php
#############################################################################
# IP address geolocation
# Aastra SIP Phones R1.4.2 or better
#
# php source code
#
# Usage
# script.php?ip=IP
#    IP is the IP address to lookup or SELF to lookup for current public IP.
#
# Copyright 2005-2015 Mitel Networks
#############################################################################

#############################################################################
# PHP customization for includes and warnings
#############################################################################
$os = strtolower(PHP_OS);
if(strpos($os, 'win') === false) ini_set('include_path',ini_get('include_path').':include:../include');
else ini_set('include_path',ini_get('include_path').';include;..\include');
error_reporting(E_ERROR | E_PARSE);

#############################################################################
# Includes
#############################################################################
require_once('AastraCommon.php');

#############################################################################
# Beginning of the active code
#############################################################################
# Retrieve parameters
$ip=Aastra_getvar_safe('ip');

# Trace
Aastra_trace_call('ip','ip='.$ip);

# Test User Agent
Aastra_test_phone_version('1.4.2.',0);

# Get language
$language=Aastra_get_language();

# Global compatibility
$nb_softkeys=Aastra_number_physical_softkeys_supported();
$is_toptitle_supported=Aastra_is_top_title_supported();

# Test parameter
if($ip)
	{
	# So far so good
	$error=0;

	# user input
	if($ip!='SELF') $lookup=$ip;
	else
		{
		# Retrieve public IP address
		$lookup=Aastra_getvar_safe('REMOTE_ADDR','','SERVER');
		}

	# Perform geolocation
	$return=Aastra_get_geolocation($lookup);

	# Check return
	if(!$return[0]) $error=1;

	# Display result
	if($error==0)
		{
		if(Aastra_is_formattedtextscreen_supported())
			{
			# As a Formatted Text Screen
			require_once('AastraIPPhoneFormattedTextScreen.class.php');
			$object = new AastraIPPhoneFormattedTextScreen();
			$object->setDestroyOnExit();
			$size=Aastra_size_formattedtextscreen();
			if($is_toptitle_supported) $object->setTopTitle(Aastra_get_label('IP Geolocation',$language));
			if($size>5) $font='double';
			else $font=NULL;
			$object->addLine($lookup,$font,'center');
			if($size>5) $object->addLine('',$font);
			$object->setScrollStart($size-1);
			$line=$return[1]['city'];
			if($return[1]['region']!='') $line.=', '.$return[1]['region'];
			$object->addLine($line,$font,'right');
			$object->addLine($return[1]['country_name'],$font,'right');
			$line=Aastra_get_label('Lat.=',$language).$return[1]['latitude'];
			$object->addLine($line,$font,'right');
			$line=Aastra_get_label('Long.=',$language).$return[1]['longitude'];
			$object->addLine($line,$font,'right');
			$line=Aastra_get_label('Powered by geoPlugin',$language);
			if($size>5)
				{
				$object->setScrollEnd();
				$object->addLine($line,'','center');
				}
			else
				{
				$object->addLine($line,'','center');
				$object->setScrollEnd();
				}
			}
		else
			{
			# As a Text Screen
			require_once('AastraIPPhoneTextScreen.class.php');
			$object = new AastraIPPhoneTextScreen();
			$object->setDestroyOnExit();
			$object->setTitle($lookup);
			$text=Aastra_get_label('City=',$language).$return[1]['city'];
			if($return[1]['region']!='') $text.=', '.$return[1]['region'];
			$text.='. ';
			$text.=Aastra_get_label('Country=',$language).$return[1]['country_name'].'. ';
			$text.=Aastra_get_label('Latitude=',$language).$return[1]['latitude'].'. ';
			$text.=Aastra_get_label('Longitude=',$language).$return[1]['longitude'].'. ';
			$object->setText($text);
			}

		# Softkeys
		if($nb_softkeys>0)
			{
			if($ip!='SELF') $object->addSoftkey('1', Aastra_get_label('New lookup',$language), $XML_SERVER);
			$object->addSoftkey($nb_softkeys, Aastra_get_label('Exit',$language), 'SoftKey:Exit');
			}
		if($ip!='SELF') $object->setCancelAction($XML_SERVER);
		}
	else
		{
		# Display error
		require_once('AastraIPPhoneTextScreen.class.php');
		$object = new AastraIPPhoneTextScreen();
		$object->setDestroyOnExit();
		if($is_toptitle_supported) $object->setTopTitle($lookup);
		else $object->setTitle($lookup);
		$object->setText(Aastra_get_label('Geolocation failed.',$language));

		# Softkeys
		if($nb_softkeys>0)
			{
 			if($ip!='SELF') $object->addSoftkey($nb_softkeys,Aastra_get_label('Close',$language),$XML_SERVER);
			else $object->addSoftkey($nb_softkeys,Aastra_get_label('Exit',$language),'SoftKey:Exit');
			}
		if($ip!='SELF') $object->setCancelAction($XML_SERVER);
		}
	}
else
	{
	# Input IP address
	require_once('AastraIPPhoneInputScreen.class.php');
	$object = new AastraIPPhoneInputScreen();
	if($is_toptitle_supported) $object->setTopTitle(Aastra_get_label('IP Geolocation',$language));
	else $object->setTitle(Aastra_get_label('IP Geolocation',$language));
	$object->setPrompt(Aastra_get_label('Enter IP address',$language));
	$object->setParameter('ip');
	$object->setType('IP');
	$object->setURL($XML_SERVER);
	$object->setDestroyOnExit();

	# Softkeys
	if($nb_softkeys>0)
		{
		if($nb_softkeys==4)
			{
			$object->addSoftkey('1',Aastra_get_label('Backspace',$language),'SoftKey:BackSpace');
			$object->addSoftkey('2',Aastra_get_label('.',$language),'SoftKey:Dot');
			$object->addSoftkey('3',Aastra_get_label('Submit',$language),'SoftKey:Submit');
			$object->addSoftkey('4',Aastra_get_label('Exit',$language),'SoftKey:Exit');	
			}
		else if($nb_softkeys==6)
			{
			$object->addSoftkey('1',Aastra_get_label('Backspace',$language),'SoftKey:BackSpace');
			$object->addSoftkey('2',Aastra_get_label('.',$language),'SoftKey:Dot');
			$object->addSoftkey('5',Aastra_get_label('Submit',$language),'SoftKey:Submit');
			$object->addSoftkey('6',Aastra_get_label('Exit',$language),'SoftKey:Exit');	
			}
		else if($nb_softkeys==10)
			{
			$object->addSoftkey('10',Aastra_get_label('Exit',$language),'SoftKey:Exit');	
			}
		}
	}

# Display object
$object->output();
exit;
?>
