<?php
#############################################################################
# North America area code finder
# Aastra SIP Phones R1.4.2 or better
#
# php source code
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
require_once('AastraIPPhoneTextScreen.class.php');
require_once('AastraIPPhoneInputScreen.class.php');

#############################################################################
# Beginning of the active code
#############################################################################
# Retrieve parameters
$area=Aastra_getvar_safe('area');

# Trace
Aastra_trace_call('area','area='.$area);

# Test User Agent
Aastra_test_phone_version('1.4.2.',0);

# Get language
$language=Aastra_get_language();

# Global compatibility
$nb_softkeys=Aastra_number_physical_softkeys_supported();
$is_toptitle_supported=Aastra_is_top_title_supported();

# Test parameter
if($area)
	{
	# Load area codes
	$array=Aastra_readINIfile('area_codes.txt','#','=');
	$object = new AastraIPPhoneTextScreen();
	$object->setDestroyOnExit();
	$object->setTitle(sprintf(Aastra_get_label('Area code %s',$language),$area));
	if($array[''][$area]!='') $object->setText($array[''][$area]);
	else $object->setText(sprintf(Aastra_get_label('Area code %s not found.',$language),$area));

	# Softkeys
	if($nb_softkeys>0)
		{
		$object->addSoftkey($nb_softkeys-1,Aastra_get_label('Back',$language), $XML_SERVER);
		$object->addSoftkey($nb_softkeys,Aastra_get_label('Exit',$language), 'SoftKey:Exit');
		}
	$object->setCancelAction($XML_SERVER);
	}
else
	{
	# Input area code
	$object=new AastraIPPhoneInputScreen();
	if($is_toptitle_supported) $object->setTopTitle(Aastra_get_label('Area code finder',$language));
	else $object->setTitle(Aastra_get_label('Area code finder',$language));
	$object->setPrompt(Aastra_get_label('Enter area code',$language));
	$object->setParameter('area');
	$object->setType('number');
	$object->setURL($XML_SERVER);
	$object->setDestroyOnExit();
	
	# Softkeys
	if($nb_softkeys>0)
		{
		if($nb_softkeys==4)
			{
			$object->addSoftkey('1',Aastra_get_label('Backspace',$language),'SoftKey:BackSpace');
			$object->addSoftkey('3',Aastra_get_label('Lookup',$language),'SoftKey:Submit');
			$object->addSoftkey('4',Aastra_get_label('Exit',$language),'SoftKey:Exit');
			}
		else if($nb_softkeys==6)
			{
			$object->addSoftkey('1',Aastra_get_label('Backspace',$language),'SoftKey:BackSpace');
			$object->addSoftkey('5',Aastra_get_label('Lookup',$language),'SoftKey:Submit');
			$object->addSoftkey('6',Aastra_get_label('Exit',$language),'SoftKey:Exit');
			}
		else
			{
			$object->addSoftkey('10',Aastra_get_label('Exit',$language),'SoftKey:Exit');
			}
		}
	}

# Display object
$object->output();
exit;
?>
