<?php

Aseco::registerEvent('onStartup', 'goalpanel_initalize');
Aseco::registerEvent('onEndRace', 'goalpanel');
Aseco::registerEvent('onEndRace1', 'goalpanel1');
Aseco::registerEvent("onNewChallenge", "goalpanel_off");
//Aseco::registerEvent('onPlayerFinish', 'goalpanel_PlayerFinish');  // disabled due usage of $checkpoints class

global $goalpanel_players;
$goalpanel_players=array();


// possible use for counting player restart & finishes
function goalpanel_PlayerFinish($aseco, $command) 
		{
			global $goalpanel_players;
			$login = $command->player->login;
			$nick = $command->player->nickname;
			$score = $command->score;
	
			if (!isset($goalpanel_players[$login]['nick'])) {
				$goalpanel_players[$login]['nick'] = $nick;
			}
			$goalpanel_players[$login]['login'] = $login;
			
			if ($command->score == 0 ) {  // if player gets to goal
			$goalpanel_players[$login]['restart']++;
					}
					else {
			$goalpanel_players[$login]['finish']++;
					}
		}

function goalpanel_sort($a,$b) 
	{
		if($a["score"]==$b["score"]) return 0;
		return $a["score"]>$b["score"]?1:-1;
	}
function goalpanel1($aseco, $command) {
//$command[0] for playerinfo, $command[1] for challenge
global $goalpanel_players;
global $rasp;
global $checkpoints;

if (empty($command[0])) return;   // if no players in server, bail out

foreach ($command[0] as $data) {
	$login = $data['Login'];
	if ($data['BestTime'] == -1) $data['BestTime']=0;
	
			$aseco->client->resetError();
			$aseco->client->query('GetDetailedPlayerInfo', $login);
			$info = $aseco->client->getResponse();
			if ($aseco->client->isError()) {
				$rank = 0;
				$score = 0;
				$lastm = 0;
				$zone = '';
			} else {
				$LPrank = $info['LadderStats']['PlayerRankings'][0]['Ranking'];
				$LPscore = $info['LadderStats']['PlayerRankings'][0]['Score'];
				$LPlastm = $info['LadderStats']['LastMatchScore'];
				$zone = substr($info['Path'], 6);  // strip "World|"
			}
			
	$tempRank = $rasp->getRank($login);
	$serverRank = preg_match('/(\d+)\/(\d+)/',$tempRank, $test);
	$goalpanel_players[$login]['score']=$data['BestTime'];
	$goalpanel_players[$login]['LP']=$data['LadderScore'];
	$goalpanel_players[$login]['login'] = $login;
	$goalpanel_players[$login]['nick'] = $data['NickName'];
	$goalpanel_players[$login]['rank'] = $LPrank;
	$goalpanel_players[$login]['LPscore'] = $LPscore;
	$goalpanel_players[$login]['LPlast'] = $LPlastm;
	$goalpanel_players[$login]['zone'] = $zone;
	$goalpanel_players[$login]['srank'] = $test[0];
	}  //foreach

print_r($goalpanel_players);
}

function goalpanel($aseco, $command) {
global $goalpanel_players;
global $rasp;
global $checkpoints;

// if no players in server, bail out
if (empty($goalpanel_players)) return;   

// generate some random data for testing multipage functions... which doesn't exist yet

/* for ($x=0; $x < 14; $x++) {
$login = gen_chars('6');
$goalpanel_players[$login]['login'] = $login;
$goalpanel_players[$login]['nick'] = gen_chars('12');
$goalpanel_players[$login]['wins'] = gen_nums('2');
$goalpanel_players[$login]['pb'] = gen_nums('6');
$goalpanel_players[$login]['LPscore'] = gen_nums('5');
$goalpanel_players[$login]['LP'] = gen_nums('3');
$goalpanel_players[$login]['rank'] = gen_nums('5');
$goalpanel_players[$login]['zone'] = gen_chars('8');
$goalpanel_players[$login]['srank'] = gen_nums('1')."/".gen_nums('2');
$goalpanel_players[$login]['score'] = gen_nums('6');
$goalpanel_players[$login]['restart'] = gen_nums('2');
$goalpanel_players[$login]['finish'] = gen_nums('2');
}

for ($x=0; $x < 2; $x++) {
$login = gen_chars('6');
$goalpanel_players[$login]['login'] = $login;
$goalpanel_players[$login]['nick'] = gen_chars('12');
$goalpanel_players[$login]['wins'] = gen_nums('2');
$goalpanel_players[$login]['pb'] = gen_nums('6');
$goalpanel_players[$login]['LPscore'] = gen_nums('5');
$goalpanel_players[$login]['LP'] = gen_nums('3');
$goalpanel_players[$login]['rank'] = gen_nums('5');
$goalpanel_players[$login]['zone'] = gen_chars('8');
$goalpanel_players[$login]['srank'] = gen_nums('1')."/".gen_nums('2');
$goalpanel_players[$login]['score'] = 0;
$goalpanel_players[$login]['restart'] = gen_nums('2');
$goalpanel_players[$login]['finish'] = gen_nums('2');
}
*/

// slice players with 0 time to other array, then make own array 
$zero_array=array();
$players_array=array();
foreach ($goalpanel_players as $key => $values) {
if ($values['score'] == 0) { $zero_array[$key] = $values; continue;}
$players_array[$key] = $values;
}
	if (count($players_array) < 0) uasort($players_array,"goalpanel_sort");

	// needs to further coding... doesn't work :(

	/* if (count($zero_array) < 0) { 
			$goalpanel_players = array_merge($players_array,$zero_array);
		} 
		else 
		{
	$goalpanel_players = $zero_array;
		}
		*/
		
	$goalpanel_players = array_merge($players_array,$zero_array);

		// needs to change to external xml file
		$hsize = 0.07;
		$bsize = 0.04;
		$csize = 0.05;
		$lines = 20;
		$widths = array("1.4");
		$header = "Challenge Results";
		$style['WINDOW']['STYLE'] = "Bgs1";
		$style['WINDOW']['SUBSTYLE'] = "BgWindow3";
		$style['HEADER']['STYLE'] = "BgsPlayerCard";
		$style['HEADER']['SUBSTYLE'] = "BgRacePlayerName";
		$style['HEADER']['TEXTSTYLE'] = "TextTitle2";
		$style['BODY']['STYLE'] = "BgsPlayerCard";
		$style['BODY']['SUBSTYLE'] = "BgActivePlayerName";
		$style['BODY']['TEXTSTYLE'] = "TextTitle3";
		
		$style['ENTRY']['STYLE'] =  "Bgs1";
		$style['ENTRY']['SUBSTYLE'] =  "BgCard2";
		$style['ENTRY']['TEXTSTYLE'] = "TextTitle2";
		
		$style['BUTTON']['STYLE'] = "Bgs1InRace";
		$style['BUTTON']['SUBSTYLE'] = "NavButton";	
			
		// manialink header & window
		$xml  = '<manialink id="34890"><frame pos="' . ($widths[0]/2) . ' 0.5 0">' .
		        '<quad size="' . $widths[0] . ' ' . (0.11+$hsize+$lines*$bsize) .
		        '" style="' . $style['WINDOW']['STYLE'] .
		        '" substyle="' . $style['WINDOW']['SUBSTYLE'] . '"/>' . LF;

		// header and icon
		$xml .= '<quad pos="-' . ($widths[0]/2) . ' -0.01 -0.1" size="' . ($widths[0]-0.02) . ' ' . $hsize .
		        '" halign="center" style="' . $style['HEADER']['STYLE'] .
		        '" substyle="' . $style['HEADER']['SUBSTYLE'] . '"/>' . LF;

		$xml .= '<label pos="-'.($widths[0]/2).' -0.025 -0.2" size="' . ($widths[0]-0.05) . ' ' . $hsize .
			        '" halign="center" style="' . $style['HEADER']['TEXTSTYLE'] .
			        '" text="' . htmlspecialchars(validateUTF8String($header)) . '"/>' . LF;
					
		//body
		/*$xml .= '<quad pos="-' . ($widths[0]/2) . ' -' . (0.02+$hsize) .
		        ' -0.1" size="' . ($widths[0]-0.02) . ' ' . (0.02+$lines*$bsize) .
		        '" halign="center" style="' . $style['BODY']['STYLE'] .
		        '" substyle="' . $style['BODY']['SUBSTYLE']. '"/>' . LF;*/

		$cnt = 0;
		$bnt = 0;
		$offset = 0;
		
		$goalpanel_sliced = array_slice($goalpanel_players,$offset,8,true);   // do multipage processing
		foreach ($goalpanel_sliced as $line) {

		//draw background
		$xml .= '<quad pos="-0.025 -' . ($hsize+($cnt*$csize)).
					        ' -0.2" size="' . ($widths[0]-0.04) . ' ' . (0.02+(2*$bsize)) .
					        '" halign="left" style="' . $style['ENTRY']['STYLE'] .
		        '" substyle="' . $style['ENTRY']['SUBSTYLE']. '"/>';
		//draw winner name
		$xml .= '<label pos="-0.13 -' . ($hsize+($cnt*$csize)+0.008) .
					        ' -0.2" size="0.6' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$888'.stripColors($line['nick'])) . '"/>' . LF;
		
		//draw time
		$xml .='<quad style="Icons128x32_1" substyle="RT_TimeAttack" pos="-0.60 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.05 0.05"/>';
		$xml .= '<label pos="-0.66 -' . ($hsize+($cnt*$csize)+0.008) .
					        ' -0.2" size="0.2 0.04" scale="0.6 0.6" halign="left" style="TextRaceChrono" 
							text="' . htmlspecialchars('$666'.goalFormatTime($line['score'])) . '"/>' . LF;					
		//draw ladderpoints 
		$xml .='<quad style="Icons128x128_1" substyle="LadderPoints" pos="-0.83 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.05 0.05"/>';
		$xml .= '<label pos="-0.9 -' . ($hsize+($cnt*$csize)+0.008) .
					        ' -0.2" size="0.4 ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$666'.$line['LP']) . '"/>' . LF;	
		//draw server rank 
		/*$xml .='<quad style="Icons64x64_1" substyle="TrackInfo" pos="-1.1 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.05 0.05"/>';
		$xml .= '<label pos="-1.15 -' . ($hsize+($cnt*$csize)+0.008) .
					        ' -0.2" size="0.4 ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$666'.$line['srank']) . '"/>' . LF;	*/				
	
		//second line		
		$xml .= '<label pos="-0.13 -' . ($hsize+($cnt*$csize)+0.008+$bsize) .
					        ' -0.2" size="0.6' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$666'.$line['zone']) . '"/>' . LF;
							
		//draw ladderpoints 
		$xml .='<quad style="Icons64x64_1" substyle="ToolLeague1" pos="-0.83 -'.($hsize+($cnt*$csize)+$bsize).' -0.3" size="0.05 0.05"/>';
		$xml .= '<label pos="-0.9 -' . ($hsize+($cnt*$csize)+0.008+$bsize) .
					        ' -0.2" size="0.4 ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$666'.$line['rank']) . '"/>' . LF;	
		//draw server rank 
		$xml .='<quad style="BgRaceScore2" substyle="Fame" pos="-1.1 -'.($hsize+($cnt*$csize)+$bsize).' -0.3" size="0.05 0.05"/>';
		$xml .= '<label pos="-1.15 -' . ($hsize+($cnt*$csize)+0.008+$bsize) .
					        ' -0.2" size="0.4 ' . (0.02+$bsize) .
					        '" halign="left" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars('$666'.$line['srank']) . '"/>' . LF;			
		//draw place numbers
  switch ($bnt+$offset) {

   case "0":
   $xml .='<quad style="Icons64x64_1" substyle="First"  pos="-0.06 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.07 0.07"/>';
   print "0";
   break;

   case "1":
	 $xml .='<quad style="Icons64x64_1" substyle="Second"  pos="-0.06 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.07 0.07"/>';
   print "1";
   break;

   case "2":
	 $xml .='<quad style="Icons64x64_1" substyle="Third"  pos="-0.06 -'.($hsize+($cnt*$csize)+0.01).' -0.3" size="0.07 0.07"/>';
   print "2";
   break;

   default :
	$xml .= '<label pos="-0.09 -' . ($hsize+($cnt*$csize)+0.02) .
					        ' -0.2" size="0.07 0.07" scale="1.4 1.4" 
							halign="center" style="' . $style['ENTRY']['TEXTSTYLE'] .
					        '" text="' . htmlspecialchars(validateUTF8String('$000'.($bnt+$offset+1))) . '"/>' . LF; 

   break;

   }
   
		$cnt=$cnt+2;
		$bnt++;
		}
		$xml .="</frame></manialink>";
	$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
	// display individual scoretable so ppl can change their own panels
	
	foreach ($aseco->server->players->player_list as $pl) {
	display_goalpanel($pl->login);
	}
	

}

function event_goalrace($aseco, $answer) {
	/// $answer[0]=PlayerUid, $answer[1]=Login, $answer[2]=Answer

	$login = $answer[1];
	$player = $aseco->server->players->getPlayer($login);
	
	switch ($answer[2]) {
	case -4:  $player->msgs['offset'] = 1; break;
	case -3:  $player->msgs['offset'] -= 5; break;
	case -2:  $player->msgs['offset'] -= 1; break;
	case  1:  break;  // stay on current page
	case  2:  $player->msgs['offset'] += 1; break;
	case  3:  $player->msgs['offset'] += 5; break;
	case  4:  $player->msgs['offset'] = $tot; break;
	}

	// leave actions outside 40001 - 40200 to other handlers
	if ($answer[2] >= 60001 && $answer[2] <= 60200) {
		// get player
		$player = $aseco->server->players->getPlayer($answer[1]);
		$target = $player->playerlist[$answer[2]-40001]['login'];
		$login = $answer[1];

		$command = array();
		$command['author'] = $player;
		$command['params'] = $target;
		if (isset($pts->Players[$target])) {
			$pts->removePlayer($target); 
			addremovePlayers($aseco, $login); 
		}
		else {
			$pts->addPlayer($target);
			addremovePlayers($aseco, $login); 
		}

	}
}  // event_goalpanel

function goalpanel_initialize($aseco, $command) {
setCustomUIField("scoretable",false);
}

function goalpanel_off($aseco, $command) {
	global $goalpanel_players;
	$goalpanel_players=array();
	print_r($goalpanel_players);
	$xml  = '<manialink id="34890"></manialink>';
	$aseco->client->query('SendDisplayManialinkPage', $xml, 0, false);
}

//Time calculation from IRCBOT - i'm not good with those, so rude copypasta of function from there. sorry.

function goalFormatTime($MwTime, $msec = true){
// This function is a modified version of the original
// because the original can not show times below 10ms
// which is used to show time difference by the IRCBOT
    if ($MwTime == -1) {
        return '???';
    } else {
        $minutes = floor($MwTime/(1000*60));
        $seconds = floor(($MwTime-$minutes*60*1000)/1000);
        if (strlen($MwTime) == 2){           // new
            $mseconds = substr($MwTime,strlen($MwTime)-2,1);
            }
        else{
            $mseconds = substr($MwTime,strlen($MwTime)-3,2);
        }
        if ($msec)
            {
            $tm = sprintf('%02d:%02d.%02d', $minutes, $seconds, $mseconds);
            }
        else
            {
            $tm = sprintf('%02d:%02d', $minutes, $seconds);
            }
        }
    if (substr($tm, 0, 1) == '0')
        {
        $tm = substr($tm, 1);
        }
    return $tm;
    } 

// this code is public domain from http://www.astahost.com/info.php/Php-Random-Text-Generating_t2583.html
function gen_chars($length = 6) {
    // Available characters
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghjkmnoprstvwxyz';

    $Code  = '';
    // Generate code
    for ($i = 0; $i < $length; ++$i)
    {
        $Code .= substr($chars, (((int) mt_rand(0,strlen($chars))) - 1),1);
    }
return $Code;
}

// this code is modified version of above
function gen_nums($length = 6) {
    // Available characters
    $chars = '0123456789';

    $Code  = '';
    // Generate code
    for ($i = 0; $i < $length; ++$i)
    {
        $Code .= substr($chars, (((int) mt_rand(0,strlen($chars))) - 1),1);
    }
return $Code;
}

?>
