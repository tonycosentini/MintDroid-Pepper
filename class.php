<?php
/******************************************************************************
 Pepper
 
 Developer		: Tony Cosentini
 Plug-in Name	: MintDroid
 
 
 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file

$installPepper = "TC_MintDroid";

function TC_MintDroid_obStagger($buffer)
{
	return str_replace('SI.Mint.staggerPaneLoading(true);', 'SI.Mint.staggerPaneLoading(false);', TC_MintDroid_obNoScroll($buffer));
}

function TC_MintDroid_obNoScroll($buffer)
{
	return preg_replace('#<div class="scroll[^"]*">#', '<div>', $buffer);
}

class TC_MintDroid extends Pepper
{
	var $version	= 100;
	var $info		= array
	(
		'pepperName'	=> 'MintDroid',
		'pepperUrl'		=> 'http://endlesswhileloop.com/',
		'pepperDesc'	=> "A combination of the iPhone Pepper and the Ego Helper Pepper. Allows the Android Browser to view Mint in a much more mobile-friendly layout, also allows the MintDroid widget to pull information from your Mint installation. Original code written by Shaun Inman, Richard Herrera, and Garrett Murray.",
		'developerName'	=> 'Tony Cosentini',
		'developerUrl'	=> 'http://endlesswhileloop.com/',
	);
	
	
	var $isiPhone	= false;
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 203)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2.03. Mint 2, a paid upgrade, is available at <a href="http://www.haveamint.com/">haveamint.com</a>.</p>'
			);
		}
		else
		{
			$compatible = array
			(
				'isCompatible'	=> true,
			);
		}
		return $compatible;
	}
	
	/**************************************************************************
	 update()
	 **************************************************************************/
	function update()
	{		
		if (!isset($this->prefs['openNew']))
		{	
			$this->prefs['openNew'] = true;
		}
		
		if (!isset($this->prefs['loadAll']))
		{	
			$this->prefs['loadAll'] = false;
		}
	}
	
	/**************************************************************************
	 onPepperLoad()
	 **************************************************************************/
	function onPepperLoad()
	{
	  // This is more or less the only part of the Pepper changed for Android, still kept the original code just in case user is on an iPhone or iPod.
		$this->isiPhone = (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') || strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) !== false);
		
		if ($this->isiPhone)
		{
			$openNew = ($this->prefs['openNew']) ? 'true' : 'false';
			$loadAll = ($this->prefs['loadAll']) ? 'true' : 'false';
			$iPhoneHead = <<<HTML
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<link href="pepper/tonycosentini/mintdroid/style.css" rel="stylesheet" type="text/css" media="only screen and (max-device-width: 480px)" charset="utf-8" />
<script type="text/javascript" src="pepper/tonycosentini/mintdroid/script.js"></script>
<script type="text/javascript">
// <![CDATA[
SI.Mint.singleCol	= true;
SI.iPhone.openNew	= {$openNew};
SI.iPhone.loadAll	= {$loadAll};
// ]]>
</script>

HTML;
			// This is a hack, an abuse of a temporary variable used during the update process.
			// If you are a Pepper developer intersted in using this sort of functionality 
			// let me know so I can update the API. 
			if (!isset($this->Mint->tmp['headTags']))
			{
				$this->Mint->tmp['headTags'] = $iPhoneHead;
			}
			
			if (!$this->prefs['loadAll'])
			{
				ob_start('TC_MintDroid_obStagger');
			}
			else
			{
				ob_start('TC_MintDroid_obNoScroll');
			}
		}
	}
	
	/**************************************************************************
	 onDisplayPreferences() 
	 **************************************************************************/
	function onDisplayPreferences()
	{
		$openNew = ($this->prefs['openNew']) ? ' checked="checked"' : '';
		$preferences['Link Behavior'] = <<<HTML
<table>
	<tr>
		<td><label><input type="checkbox" name="openNew" value="1"{$openNew} /> Open links in new tab</label></td>
	</tr>
</table>
HTML;

		$loadAll = ($this->prefs['loadAll']) ? ' checked="checked"' : '';
		$preferences['Pane Behavior'] = <<<HTML
<table>
	<tr>
		<td><label><input type="checkbox" name="loadAll" value="1"{$loadAll} /> Load all panes automatically</label></td>
	</tr>
</table>
HTML;
		
		if ($this->isiPhone)
		{
			$preferences[''] = <<<HTML
<table><tr><td>Mint <em class="iphone">and</em> an iPhone? Talk about disposable income!</td></tr></table>
<script type="text/javascript" src="pepper/tonycosentini/mintdroid/script.js"></script>
<script type="text/javascript" language="javascript">
// <![CDATA[
SI.iPhone.updateLayout();
SI.iPhone.tidyPreferences();
setInterval(SI.iPhone.updateLayout, 400);
// ]]>
</script>

HTML;
		}
		
		return $preferences;
	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{	
		$this->prefs['openNew']	= (isset($_POST['openNew'])) ? true : false;
		$this->prefs['loadAll']	= (isset($_POST['loadAll'])) ? true : false;
	}	


/* EGO APP XML APP */
function show_stats($email, $password)
{
	global $Mint;
	
	if (urldecode($email) == $Mint->cfg['email'] && urldecode($password) == $Mint->cfg['password'])
	{
		$visits = $Mint->data[0]['visits'];
	
		// total
		$total_unqs = $visits[0][0]['unique'];
		$total_hits = $visits[0][0]['total'];
	
		// this hour
		$hourly_keys = array_keys($visits[1]);
		$last_hour = $hourly_keys[(count($hourly_keys)-1)];
		$hour_unqs = $visits[1][$last_hour]['unique'];
		$hour_hits = $visits[1][$last_hour]['total'];
	
		// today
		$daily_keys = array_keys($visits[2]);
		$last_day = $daily_keys[(count($daily_keys)-1)];
		$day_unqs = $visits[2][$last_day]['unique'];
		$day_hits = $visits[2][$last_day]['total'];
	
		// this week
		$weekly_keys = array_keys($visits[3]);
		$last_week = $weekly_keys[(count($weekly_keys)-1)];
		$week_unqs = $visits[3][$last_week]['unique'];
		$week_hits = $visits[3][$last_week]['total'];
	
		// this month
		$monthly_keys = array_keys($visits[4]);
		$last_month = $monthly_keys[(count($monthly_keys)-1)];
		$month_unqs = $visits[4][$last_month]['unique'];
		$month_hits = $visits[4][$last_month]['total'];
		
		// build lazy XML
		// i know this isn't an awesome way to do it, but i want to make sure
		// it's compatible with all PHP versions, since i don't know exactly
		// what mint requires, so i can't rely on frameworks or libs or whatever
		
		header('Content-type: text/xml');
		
		echo '<?xml version="1.0" encoding="UTF-8" ?>';
		echo "\n<stats>";
		
		echo "\n\n";
		echo "<!-- Mint stats provided by the Ego Helper Pepper, used by Ego, an iPhone";
		echo " application available at http://ego-app.com -->";
		echo "\n";
		
		echo "\n\t<visits>";
		echo "\n\t\t<total unique=\"{$total_unqs}\" hits=\"{$total_hits}\"/>";
            echo "\n\t\t<hour unique=\"{$hour_unqs}\" hits=\"{$hour_hits}\"/>";
            echo "\n\t\t<day unique=\"{$day_unqs}\" hits=\"{$day_hits}\"/>";
            echo "\n\t\t<week unique=\"{$week_unqs}\" hits=\"{$week_hits}\"/>";
            echo "\n\t\t<month unique=\"{$month_unqs}\" hits=\"{$month_hits}\"/>";
		echo "\n\t</visits>";
		echo "\n</stats>";
	}
}

function clean_string($str)
{
    return str_replace('&', '&amp;', $str);
}
}