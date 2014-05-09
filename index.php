<?php require("config.php") ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<!--  - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - >
<  index.html: VLC media player web interface - VLM
< - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - >
<  Copyright (C) 2005-2006 the VideoLAN team
<  $Id$
<
<  Authors: Brandon Brooks <bwbrooks -at- archmageinc -dot- com>
<
<  This program is free software; you can redistribute it and/or modify
<  it under the terms of the GNU General Public License as published by
<  the Free Software Foundation; either version 2 of the License, or
<  (at your option) any later version.
<
<  This program is distributed in the hope that it will be useful,
<  but WITHOUT ANY WARRANTY; without even the implied warranty of
<  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
<  GNU General Public License for more details.
<
<  You should have received a copy of the GNU General Public License
<  along with this program; if not, write to the Free Software
<  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111, USA.
< - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -->
<html>
	<head>
		<title>Přehrávač VLC - webové rozhraní</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link href="favicon.ico" type="image/x-icon" rel="shortcut icon" />
		<script type="text/javascript" src="js/common.js"></script>
		<script type="text/javascript">
		//<![CDATA[
			if(isMobile()){
				window.location='mobile.html';
			}
		//]]>
		</script>
		<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.13.custom.css" rel="stylesheet" />
		<link type="text/css" href="css/main.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="js/jquery.jstree.js"></script>
		<script type="text/javascript" src="js/ui.js"></script>
		<script type="text/javascript" src="js/controlers.js"></script>
		<script type="text/javascript">
		//<![CDATA[
			var pollStatus	=	true;
			$(function(){
				$('.button').hover(function(){$(this).addClass('ui-state-hover')},function(){$(this).removeClass('ui-state-hover')});
				$('#buttonPlayList').click(function(){
					$('#libraryContainer').animate({
						height: 'toggle'
					});
					$('#buttonszone1').animate({
						width: 'toggle'
					});
					return false;
				});
				$('#buttonViewer').click(function(){
					$('#viewContainer').animate({
						height: 'toggle'
					})
					return false;
				});
				$('#buttonEqualizer').click(function(){
					updateEQ();
					$('#window_equalizer').dialog('open');
					return false;
				})
				$('#buttonOffsets').click(function(){
					$('#window_offset').dialog('open');
					return false;
				});
				$('#buttonBatch').click(function(){
					$('#window_batch').dialog('open');
					return false;
				});
				$('#buttonOpen').click(function(){
					browse_target	=	'default';
					browse();
					$('#window_browse').dialog('open');
					return false;
				});
				$('#buttonPrev').mousedown(function(){
					intv	=	1;
					ccmd	=	'prev';
					setIntv();
					return false;
				});
				$('#buttonPrev').mouseup(function(){
					if(intv<=5){
						sendCommand({'command':'pl_previous'});
					}
					intv	=	0;
					return false;
				});
				$('#buttonNext').mousedown(function(){
					intv	=	1;
					ccmd	=	'next';
					setIntv();
					return false;
				});
				$('#buttonNext').mouseup(function(){
					if(intv<=5){
						sendCommand({'command':'pl_next'});
					}
					intv	=	0;
					return false;
				});
				$('#buttonPlEmpty').click(function(){
					sendCommand({'command':'pl_empty'})
					updatePlayList(true);
					return false;
				});
				$('#buttonLoop').click(function(){
					sendCommand({'command':'pl_loop'});
					return false;
				});
				$('#buttonRepeat').click(function(){
					sendCommand({'command':'pl_repeat'});
					return false;
				});
				$('#buttonShuffle').click(function(){
					sendCommand({'command':'pl_random'});
					return false;
				})
				$('#buttonRefresh').click(function(){
				    updatePlayList(true);
				    return false;
				});
				$('#buttonPlPlay').click(function(){
					sendCommand({
						'command': 'pl_play',
						'id':$('.jstree-clicked','#libraryTree').first().parents().first().attr('id').substr(5)
					})
					return false;
				});
				$('#buttonPlAdd').click(function(){
					$('.jstree-clicked','#libraryTree').each(function(){
						if($(this).parents().first().attr('uri')){
							sendCommand({
								'command':'in_enqueue',
								'input' : $(this).parents().first().attr('uri')
							});
						};
					});
					$('#libraryTree').jstree('deselect_all');
					setTimeout(function(){updatePlayList(true);},1000);
					return false;
				});
				$('#buttonStreams, #buttonStreams2').click(function(){
					updateStreams();
					$('#window_streams').dialog('open');
				});
				$('#buttonSout').click(function(){
					if(current_que=='main'){
						$('#windowStreamConfirm').dialog('open');
					}else{
						$('#player').empty();
						current_que		=	'main';
						sendVLMCmd('del Current');
						updateStatus();
					}
					return false;
				});
				$('#windowStreamConfirm').dialog({
					autoOpen: false,
					width:600,
					modal: true,
					buttons:{
						"Ano":function(){
							var file			=	$('[current="current"]','#libraryTree').length>0 ? decodeURIComponent($('[current="current"]','#libraryTree').first().attr('uri').substr(7)) : ($('.jstree-clicked','#libraryTree').length>0 ? decodeURIComponent($('.jstree-clicked','#libraryTree').first().parents().first().attr('uri').substr(7)) : ($('#plid_'+current_id).attr('uri') ? decodeURIComponent($('#plid_'+current_id).attr('uri').substr(7)) : false));
							if(file){
								if($('#viewContainer').css('display')=='none'){
									$('#buttonViewer').click();
								}
								var defaultStream	=	'new Current broadcast enabled input "'+file+'" output #transcode{vcodec=FLV1,vb=4096,fps=25,scale=1,acodec=mp3,ab=512,samplerate=44100,channels=2}:std{access='+$('#stream_protocol').val()+',mux=avformat{{mux=flv}},dst=0.0.0.0:'+$('#stream_port').val()+'/'+$('#stream_file').val()+'}';
								sendVLMCmd('del Current;'+defaultStream+';control Current play');
								$('#player').attr('href',$('#stream_protocol').val()+'://'+$('#stream_host').val()+':'+$('#stream_port').val()+'/'+$('#stream_file').val());
								current_que			=	'stream';
								updateStreams();
							}
							$(this).dialog('close');
						},
						"No":function(){
							$(this).dialog('close');
						}
					}
				});
				$('#viewContainer').animate({height: 'toggle'});
			});
			/* delay script loading so we won't block if we have no net access */
			$.getScript('http://releases.flowplayer.org/js/flowplayer-3.2.6.min.js', function(data, textStatus){
				$('#player').empty();
				flowplayer("player", "http://releases.flowplayer.org/swf/flowplayer-3.2.7.swf");
				/* .getScript only handles success() */
			 });
		//]]>
		</script>
	</head>
	<body id="regular_layout">
			<div class="centered">
			<div id="mainContainer" class="centered">
			<div id="controlContainer" class="ui-widget">
				<div id="controlTable" class="ui-widget-content">
					<ul id="controlButtons">
						<li id="buttonPrev" class="button48  ui-corner-all" title="Předchozí"></li>
						<li id="buttonPlay" class="button48  ui-corner-all paused" title="Přehrát"></li>
						<li id="buttonNext" class="button48  ui-corner-all" title="Následující"></li>
						<li id="buttonOpen" class="button48  ui-corner-all" title="Otevřít médium"></li>
						<li id="buttonStop" class="button48  ui-corner-all" title="Zastavit"></li>
						<li id="buttonFull" class="button48  ui-corner-all" title="Full Screen"></li>
						<li id="buttonSout" class="button48  ui-corner-all" title="Easy Stream"></li>
					</ul>
					<ul id="buttonszone2" class="buttonszone">
						<li id="buttonPlayList" class="button ui-widget ui-state-default ui-corner-all" title="Hide / Show Library"><span class="ui-icon ui-icon-note"></span>Knihovna</li>
						<li id="buttonViewer" class="button ui-widget ui-state-default ui-corner-all" title="Hide / Show Viewer"><span class="ui-icon ui-icon-video"></span>Náhled videa</li>
						<li id="buttonStreams" class="button ui-widget ui-state-default ui-corner-all" title="Manage Streams"><span class="ui-icon ui-icon-script"></span>Správa proudů</li>
						<li id="buttonOffsets" class="button ui-widget ui-state-default ui-corner-all" title="Track Synchronisation"><span class="ui-icon ui-icon-transfer-e-w"></span>Synchronizace stop</li>
						<li id="buttonEqualizer" class="button ui-widget ui-state-default ui-corner-all" title="Ekvalizér"><span class="ui-icon ui-icon-signal"></span>Ekvalizér</li>
						<li id="buttonBatch" class="button ui-widget ui-state-default ui-corner-all" title="VLM Batch Commands"><span class="ui-icon ui-icon-suitcase"></span>VLM Batch Commands</li>
					</ul>
					<div id="volumesliderzone">
						<div id="volumeSlider" title="Volume"><img src="images/speaker-32.png" class="ui-slider-handle" alt="volume"/></div>
						<div id="currentVolume" class="dynamic">50%</div>
					</div>
					<div id="artszone">
						<img id="albumArt" src="/art" width="141px" height="130px" alt=""/>
					</div>
					<div id="mediaTitle" class="dynamic"></div>
					<div id="seekContainer">
						<div id="seekSlider" title="Seek Time"></div>
						<div id="currentTime" class="dynamic">00:00:00</div>
						<div id="totalTime" class="dynamic">00:00:00</div>
					</div>
				</div>
			</div>
			<div id="libraryContainer" class="ui-widget">
				<ul id="buttonszone1" align="left" class="buttonszone ui-widget-content" style="overflow:hidden; white-space: nowrap;">
					<li id="buttonShuffle" class="button ui-widget ui-state-default ui-corner-all" title="Zamíchat"><span class="ui-icon ui-icon-shuffle"></span>Zamíchat</li>
					<li id="buttonLoop" class="button ui-widget ui-state-default ui-corner-all" title="Smyčka"><span class="ui-icon ui-icon-refresh"></span>Smyčka</li>
					<li id="buttonRepeat" class="button ui-widget ui-state-default ui-corner-all" title="Opakovat"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>Opakovat</li>
					<li id="buttonPlEmpty" class="button ui-widget ui-state-default ui-corner-all" title="Vyprázdnit seznam"><span class="ui-icon ui-icon-trash"></span>Vyprázdnit seznam</li>
					<li id="buttonPlAdd" class="button ui-widget ui-state-default ui-corner-all" title="Vybrané do fronty"><span class="ui-icon ui-icon-plus"></span>Vybrané do fronty</li>
					<li id="buttonPlPlay" class="button ui-widget ui-state-default ui-corner-all" title="Přehrát vybrané"><span class="ui-icon ui-icon-play"></span>Přehrát vybrané</li>
					<li id="buttonRefresh" class="button ui-widget ui-state-default ui-corner-all" title="Obnovit seznam"><span class="ui-icon ui-icon-arrowrefresh-1-n"></span>Obnovit seznam</li>
				</ul>
				<div id="libraryTree" class="ui-widget-content"></div>
			</div>
			<div id="viewContainer" class="ui-widget">
				<div id="mediaViewer" class="ui-widget-content">
					<div href="<?php echo $CONFIG["vlc-stream"] ?>" style="display:block; width:100%" id="player">
						<p>(Přehrávač nenačten.)</p>
					</div>
				</div>
			</div>
			<div class="footer">
				VLC 2.1.2 Rincewind - Lua Web Interface - <a id="mobileintflink" href="/mobile.html">Mobile Interface</a> - Copyright © 1996-2013 the VideoLAN team			</div>
		</div>
		</div>
		<div id="windowStreamConfirm" title="Confirm Stream Settings">
			<p>
				By creating a stream, the <i>Main Controls</i> will operate the stream instead of the main interface.				The stream will be created using default settings, for more advanced configuration, or to modify the default settings, select the button to the right: <i>Manage Streams</i>				<span id="buttonStreams2" class="button ui-widget ui-state-default ui-corner-all" title="Manage Streams"><span class="ui-icon ui-icon-script"></span></span>
			</p>
			<p>
				Once the stream is created, the <i>Media Viewer</i> window will display the stream.				Volume will be controlled by the player, and not the <i>Main Controls</i>.			</p>
			<p>
				The current playing item will be streamed. If there is no currently playing item, the first selected item from the <i>Library</i> will be the subject of the stream.			</p>
			<p>
				To stop the stream and resume normal controls, click the <i>Open Stream</i> button again.			</p>
			<p>
				Opravdu si přejete vytvořit proud?			</p>
		</div>
		<script type="text/javascript">
//<![CDATA[
	var browse_target = 'default';
	$(function(){
		$('#window_browse').dialog({
			autoOpen: false,
			width: 600,
			height: 650,
			modal: true,
			resizable: false,
			buttons: {
				"Přehrát":function(){
					$('li.ui-selected','#browse_elements').each(function(){
						$(this).dblclick();
					});
				},
				"Do fronty": function() {
					$('li.ui-selected','#browse_elements').each(function(){
						var path	=	this.getAttribute('opendir') ? this.getAttribute('opendir') : this.getAttribute('openfile');
						switch(browse_target){
							default:
								sendCommand('command=in_enqueue&input='+encodeURI(path));
								setTimeout(function(){updatePlayList(true);},1000);
								break;
						}
					});
					$(this).dialog("close");
				},
				"Zrušit" : function(){
					$(this).dialog("close")
				}
			}
		});
	});
//]]>
</script>

<div id="window_browse" title="Media Browser">
	<div style="height:500px;overflow: auto;">
		<ol id='browse_elements' selectable="selectable">
			<li>Přehrát seznam</li>
		</ol>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
	var stream_server		=	window.location.hostname;
	function configureStreamWindow(stream_protocol,stream_server,stream_port,stream_file){
		$('#stream_protocol').val(stream_protocol);
		$('#stream_host').val(stream_server);
		$('#stream_port').val(stream_port);
		$('#stream_file').val(stream_file);
	}
	$(function(){
		$('#window_streams').dialog({
			autoOpen: false,
			minWidth: 600,
			minHeight: 430,
			buttons:{
				"Close":function(){
					$(this).dialog("close");
				}
			}
		});
		$('#window_stream_config').dialog({
			autoOpen: false,
			width:400,
			modal: true,
			buttons:{
				"Okay":function(){
					$(this).dialog('close');
				}
			}
		});
		$('#button_create_stream').click(function(){
			$('#window_create_stream').dialog('open');
			return false;
		});
		$('#button_clear_streams').click(function(){
			sendVLMCmd('del all');
			return false;
		});
		$('#button_config_streams').click(function(){
			$('#window_stream_config').dialog('open');
			return false;
		});
		$('#button_create_mosaic').click(function(){
			$('#window_mosaic').dialog('open');
			return false;
		});
		$('#button_refresh_streams').click(function(){
			updateStreams();
			return false;
		})
		$('#stream_host').val(stream_server);
	});
//]]>
</script>
<div id="stream_status_" style="visibility:hidden;display:none;">
	<h3><a href="#" id="stream_title_"></a></h3>
	<div>
		<div id="button_stream_stop_" class="button icon ui-widget ui-state-default" title="Zastavit"><span class="ui-icon ui-icon-stop"></span></div>
		<div id="button_stream_play_" class="button icon ui-widget ui-state-default" title="Přehrát"><span class="ui-icon ui-icon-play"></span></div>
		<div id="button_stream_loop_" class="button icon ui-widget ui-state-default" title="Smyčka"><span class="ui-icon ui-icon-refresh"></span></div>
		<div id="button_stream_delete_" class="button icon ui-widget ui-state-default" title="Remove Stream"><span class="ui-icon ui-icon-trash"></span></div>
		<div>Title: <span id="stream_file_"></span></div>
		<div style="width: 260px; margin: 5px 0px 10px 0px;">
			<div id="stream_pos_"></div>
			Čas: <span id="stream_current_time_">00:00:00</span> / <span id="stream_total_time_">00:00:00</span>
		</div>
	</div>
</div>
<div id="window_streams" title="Manage Streams">
	<div id="button_create_stream" class="button icon ui-widget ui-state-default" title="Create New Stream" opendialog="window_create_stream"><span class="ui-icon ui-icon-plus"></span></div>
	<div id="button_create_mosaic" class="button icon ui-widget ui-state-default" title="Create Mosaic" opendialog="window_create_mosaiac"><span class="ui-icon ui-icon-calculator"></span></div>
	<div id="button_clear_streams" class="button icon ui-widget ui-state-default" title="Delete All Streams"><span class="ui-icon ui-icon-trash"></span></div>
	<div id="button_config_streams" class="button icon ui-widget ui-state-default" title="Configure Stream Defaults"><span class="ui-icon ui-icon-wrench"></span></div>
	<div id="button_refresh_streams" class="button ui-widget ui-state-default ui-corner-all" title="Refresh Streams"><span class="ui-icon ui-icon-arrowrefresh-1-n"></span></div>
	<div id="stream_info">

	</div>

</div>
<div id="window_stream_config" title="Stream Input Configuration">
	<table>
		<tr>
			<td>Protokol</td>
			<td><input type="text" name="stream_protocol" id="stream_protocol" value="http" /></td>
		</tr>
		<tr>
			<td>Hostitel</td>
			<td><input type="text" name="stream_host" id="stream_host" value="" /></td>
		</tr>
		<tr>
			<td>Port</td>
			<td><input type="text" name="stream_port" id="stream_port" value="8081" /></td>
		</tr>
		<tr>
			<td>Soubor</td>
			<td><input type="text" name="stream_file" id="stream_file" value="stream.flv" /></td>
		</tr>
	</table>
</div>
<script type="text/javascript">
//<![CDATA[
	$(function(){
		$('#stream_out_method').change(function(){
			$('#output_options').empty();
			switch($(this).val()){
				case 'file':
					var options = $('#file_options').clone();
					break;
				case 'http':
					var options = $('#net_options').clone();
					break;
				case 'mmsh':
				case 'rtp':
				case 'udp':
					var options = $('#net_options').clone();
					$('#stream_out_file_',options).val('');
					break;
			}
			$('[id]',options).each(function(){
				$(this).attr('id',$(this).attr('id').substr(0,$(this).attr('id').length-1));
				$(this).attr('name',$(this).attr('name').substr(0,$(this).attr('name').length-1));
			});
			$(options).css({
				'visibility':'visible',
				'display':'block'
			})
			$(options).appendTo('#output_options');
		});
		$('#stream_out_mux').change(function(){
			if($(this).val()=='ffmpeg'){
				$('#stream_out_mux_opts').val('{mux=flv}');
			}else{
				$('#stream_out_mux_opts').val('');
			}
		});
		$('#window_create_stream').dialog({
			autoOpen: false,
			width:800,
			modal: true,
			buttons:{
				"Vytvořit":function(){
					var e	=	false;
					$('input',$(this)).removeClass('ui-state-error');
					$('#stream_error_container').css({
						'visibility':'hidden',
						'display':'none'
					});
					if(!$('#stream_name').val()){
						$('#stream_name').addClass('ui-state-error');
						e	=	true;
					}
					if(!$('#stream_input').val()){
						$('#stream_input').addClass('ui-state-error');
						e	=	true;
					}

					if($('#stream_out_method').val()!='file' && !$('#stream_out_port').val()){
						$('#stream_out_port').addClass('ui-state-error');
						e	=	true;
					}
					if($('#stream_out_method').val()!='file' && !$('#stream_out_dest').val()){
						$('#stream_out_dest').addClass('ui-state-error');
						e	=	true;
					}

					if($('#stream_out_method').val()=='file' && !$('#stream_out_filename').val()){
						$('#stream_out_filename').addClass('ui-state-error');
						e	=	true;
					}
					if(e){
						$('#stream_error_message').empty();
						$('#stream_error_message').append('One or more fields require attention.');
						$('#stream_error_container').css({
							'visibility':'visible',
							'display':'block'
						})
					}else{
						sendVLMCmd(buildStreamCode());
						$(this).dialog('close');
					}
				},
				"Zrušit":function(){
					$(this).dialog('close');
				}
			}
		});
		$('#button_input').click(function(){
			browse_target	=	'#stream_input';
			browse();
			$('#window_browse').dialog('open');
		});
		$('#button_in_screen').click(function(){
			$('#stream_input').val('screen://');
		});
	});
	function buildStreamCode(){
		var name		=	$('#stream_name').val().replace(' ','_');
		var infile		=	$('#stream_input').val();

		var vcodec		=	$('#stream_vcodec').val();
		var vb			=	$('#stream_vb').val();
		var fps			=	$('#stream_fps').val();
		var scale		=	$('#stream_scale').val();
		var dlace		=	$('#stream_deinterlace').is(':checked');

		var acodec		=	$('#stream_acodec').val();
		var ab			=	$('#stream_ab').val();
		var srate		=	$('#stream_samplerate').val();
		var channels	=	$('#stream_channels').val();

		var scodec		=	$('#stream_scodec').val() && !$('#stream_soverlay').checked ? ','+$('#stream_scodec').val() : '';
		var soverlay	=	$('#stream_soverlay').is(':checked') ? ',soverlay' : '';

		var outmethod	=	$('#stream_out_method').val();
		var mux			=	$('#stream_out_mux').val();
		var muxoptions	=	$('#stream_out_mux_opts').val() ? '{'+$('#stream_out_mux_opts').val()+'}' : '';

		if(outmethod=='file'){
			var filename	=	$('#stream_out_filename').val();
		}else{
			var outport		=	$('#stream_out_port').val();
			var outdest		=	$('#stream_out_dest').val();
			var outfile		=	$('#stream_out_file').val();
		}
		var dest		=	outmethod=='file' ? filename : (outfile ? outdest+':'+outport+'/'+outfile : outdest+':'+outport);
		var inCode		=	'new '+name+' broadcast enabled input "'+infile+'" ';
		var transCode	=	'output #transcode{vcodec='+vcodec+',vb='+vb+',fps='+fps+',scale='+scale+',acodec='+acodec+',ab='+ab+',samplerate='+srate+',channels='+channels+scodec+soverlay+'}';
		var outCode		=	':std{access='+outmethod+',mux='+mux+muxoptions+',dst='+dest+'}';

		return inCode+transCode+outCode;
	}
//]]>
</script>
<div id="window_create_stream" title="Create Stream">
	<table width="100%">
		<tr>
			<td style="text-align:right" valign="top">
				<h5>Název proudu</h5>
			</td>
			<td colspan="5" valign="top">
				<input type="text" name="stream_name" id="stream_name" value=""/>
			</td>
		</tr>
		<tr>
			<th colspan="2" valign="top">
				<h5>Video</h5>
			</th>
			<th colspan="2" valign="top">
				<h5>Zvuk</h5>
			</th>
			<th colspan="2" valign="top">
				<h5>Titulky</h5>
			</th>
			<th colspan="2" valign="top">
				<h5>Výstup</h5>
			</th>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">Video kodek</td>
			<td valign="top">
				<select name="stream_vcodec" id="stream_vcodec">
					<option value="FLV1">FLV1</option>
					<option value="mp1v">mp1v</option>
					<option value="mp2v">mp2v</option>
					<option value="mp4v">mp4v</option>
					<option value="DIV1">DIV1</option>
					<option value="DIV2">DIV2</option>
					<option value="DIV3">DIV3</option>
					<option value="h263">H263</option>
					<option value="h264">H264</option>
					<option value="WMV1">WMV1</option>
					<option value="WMV2">WMV2</option>
					<option value="MJPG">MJPG</option>
					<option value="theo">theo</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Zvukový kodek</td>
			<td valign="top">
				<select name="stream_acodec" id="stream_acodec">
					<option value="mp3">mp3</option>
					<option value="mpga">mpga</option>
					<option value="mp2a">mp2a</option>
					<option value="mp4a">mp4a</option>
					<option value="a52">a52</option>
					<option value="vorb">vorb</option>
					<option value="flac">flac</option>
					<option value="spx">spx</option>
					<option value="s16l">s16l</option>
					<option value="fl32">fl32</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Subtitle codec</td>
			<td valign="top">
				<select name="stream_scodec" id="stream_scodec">
					<option value="">Nic</option>
					<option value="dvbs">dvbs</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Output	method</td>
			<td valign="top">
				<select name="stream_out_method" id="stream_out_method">
					<option value="http">HTTP</option>
					<option value="file">Soubor</option>
					<option value="mmsh">MMSH</option>
					<option value="rtp">RTP</option>
					<option value="udp">UDP</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">Datový tok videa</td>
			<td valign="top">
				<select name="stream_vb" id="stream_vb">
					<option value="4096">4096</option>
					<option value="3072">3072</option>
					<option value="2048">2048</option>
					<option value="1024">1024</option>
					<option value="768">768</option>
					<option value="512">512</option>
					<option value="384">384</option>
					<option value="256">256</option>
					<option value="192">192</option>
					<option value="128">128</option>
					<option value="96">96</option>
					<option value="64">64</option>
					<option value="32">32</option>
					<option value="16">16</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Datový tok zvuku</td>
			<td valign="top">
				<select name="stream_ab" id="stream_ab">
					<option value="512">512</option>
					<option value="384">384</option>
					<option value="256">256</option>
					<option value="192">192</option>
					<option value="128">128</option>
					<option value="96">96</option>
					<option value="64">64</option>
					<option value="32">32</option>
					<option value="16">16</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Přesah</td>
			<td valign="top">
				<input type="checkbox" name="stream_soverlay" id="stream_soverlay" value="1" />
			</td>
			<td style="text-align:right" valign="top">Multiplexer</td>
			<td valign="top">
				<select name="stream_out_mux" id="stream_out_mux">
					<option value="ts">MPEG TS</option>
					<option value="ps">MPEG PS</option>
					<option value="mpeg1">MPEG 1</option>
					<option value="ogg">OGG</option>
					<option value="asf">ASF</option>
					<option value="mp4">MP4</option>
					<option value="mov">MOV</option>
					<option value="wav">WAV</option>
					<option value="raw">Raw</option>
					<option value="ffmpeg" selected="selected">FFMPEG</option>
				</select>
			</td>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">FPS videa</td>
			<td valign="top">
				<select name="stream_fps" id="stream_fps">
					<option value="300">300</option>
					<option value="120">120</option>
					<option value="100">100</option>
					<option value="72">72</option>
					<option value="60">60</option>
					<option value="50">50</option>
					<option value="48">48</option>
					<option value="30">30</option>
					<option value="25" selected="selected">25</option>
					<option value="24">24</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Audio sample rate</td>
			<td valign="top">
				<select name="stream_samplerate" id="stream_samplerate">
					<option value="192000">192 KHz</option>
					<option value="96000">96 KHz</option>
					<option value="50000">50 KHz</option>
					<option value="48000">48 KHz</option>
					<option value="44100" selected="selected">44 KHz</option>
					<option value="32000">32 KHz</option>
					<option value="22050">22 KHz</option>
					<option value="16000">16 KHz</option>
					<option value="11025">11 KHz</option>
					<option value="8000">8 KHz</option>
				</select>
			</td>
			<td colspan="2" valign="top">&nbsp;</td>
			<td style="text-align:right" valign="top">MUX options</td>
			<td valign="top">
				<input type="text" name="stream_out_mux_opts" id="stream_out_mux_opts" value="{mux=flv}" />
			</td>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">Video scale</td>
			<td valign="top">
				<select name="stream_scale" id="stream_scale">
					<option value="0.25">25%</option>
					<option value="0.5">50%</option>
					<option value="0.75">75%</option>
					<option selected="selected" value="1">100%</option>
					<option value="1.25">125%</option>
					<option value="1.5">150%</option>
					<option value="1.75">175%</option>
					<option value="2">200%</option>
				</select>
			</td>
			<td style="text-align:right" valign="top">Zvukové kanály</td>
			<td valign="top">
				<select name="stream_channels" id="stream_channels" >
					<option value="1">1</option>
					<option value="2" selected="selected">2</option>
					<option value="4">4</option>
					<option value="6">6</option>
				</select>
			</td>
			<td colspan="2" valign="top">&nbsp;</td>
			<td colspan="2" rowspan="2" valign="top">
				<div id="output_options">
						<table>
							<tr>
								<td style="text-align:right" valign="top">Port výstupu</td>
								<td valign="top"><input type="text" name="stream_out_port" id="stream_out_port" value="8081" /></td>
							</tr>
							<tr>
								<td style="text-align:right" valign="top">Cíl výstupu</td>
								<td><input type="text" name="stream_out_dest" id="stream_out_dest" value="0.0.0.0" /></td>
							</tr>
							<tr>
								<td style="text-align:right" valign="top">Output	file</td>
								<td valign="top"><input type="text" name="stream_out_file" id="stream_out_file" value="stream.flv" /></td>
							</tr>
						</table>
				</div>
			</td>
		</tr>
		<tr>
			<td valign="top" style="text-align:right">Odstranit prokládání</td>
			<td valign="top">
				<input type="checkbox" name="stream_deinterlace" id="stream_deinterlace" value="1" />
			</td>
			<td colspan="2" valign="top">&nbsp;</td>
			<td colspan="2" valign="top">&nbsp;</td>
		</tr>
		<tr>
			<td style="text-align:right" colspan="2" valign="top">
				Input media			</td>
			<td colspan="6" valign="top">
				<input type="text" name="stream_input" id="stream_input" value="" size="50" />
				<div id="button_input" class="button icon ui-widget ui-state-default" title="Media file" opendialog="window_browse"><span class="ui-icon ui-icon-eject"></span></div>
				<div id="button_in_screen" class="button icon ui-widget ui-state-default" title="Capture screen" ><span class="ui-icon ui-icon-contact"></span></div>
			</td>
		</tr>
	</table>
	<div class="ui-widget" id="stream_error_container" style="display:none;visibility: hidden;">
		<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
			<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
			<strong>Chyba:</strong> <span id="stream_error_message">Sample ui-state-error style.</span></p>
		</div>
	</div>
</div>
<div id="net_options" style="display:none;visibility: hidden;">
	<table>
		<tr>
			<td style="text-align:right" valign="top">Port výstupu</td>
			<td valign="top"><input type="text" name="stream_out_port_" id="stream_out_port_" value="8081" /></td>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">Cíl výstupu</td>
			<td valign="top"><input type="text" name="stream_out_dest_" id="stream_out_dest_" value="0.0.0.0" /></td>
		</tr>
		<tr>
			<td style="text-align:right" valign="top">Výstupní soubor</td>
			<td valign="top"><input type="text" name="stream_out_file_" id="stream_out_file_" value="stream.flv" /></td>
		</tr>
	</table>
</div>
<div id="file_options" style="display:none;visibility: hidden;">
	<table>
		<tr>
			<td style="text-align:right" valign="top">Název souboru</td>
			<td valign="top"><input type="text" name="stream_out_filename_" id="stream_out_filename_"/></td>
		</tr>
	</table>
</div>
<script type="text/javascript">
//<![CDATA[
	$(function(){
		$('#window_offset').dialog({
			autoOpen: false,
			minWidth: 400,
			buttons:{
				"Close":function(){
					$(this).dialog("close");
				}
			}
		});
		$( "#rateSlider" ).slider({
			range: "min",
			value: 1,
			min: 0.25,
			max: 10,
			step: 0.25,
			stop: function( event, ui ) {
				sendCommand({
					'command':'rate',
					'val':(ui.value)
				})
			},
			slide: function(event,ui){
				$('#currentRate').empty();
				$('#currentRate').append(ui.value+'x');
			}
		});
		$( "#audioSlider" ).slider({
			range: "min",
			value: 0,
			min: -10,
			max: 10,
			step: 0.25,
			stop: function( event, ui ) {
				sendCommand({
					'command':'audiodelay',
					'val':(ui.value)
				})
			},
			slide: function(event,ui){
				$('#currentAudioDelay').empty();
				$('#currentAudioDelay').append(ui.value+'s');
			}
		});
		$( "#subtitleSlider" ).slider({
			range: "min",
			value: 0,
			min: -10,
			max: 10,
			step: 0.25,
			stop: function( event, ui ) {
				sendCommand({
					'command':'subdelay',
					'val':(ui.value)
				})
			},
			slide: function(event,ui){
				$('#currentSubtitleDelay').empty();
				$('#currentSubtitleDelay').append(ui.value+'s');
			}
		});
	});
//]]>
</script>
<div id="window_offset" title="Synchronizace stop">
	<div>Rychlost přehrávání</div>
	<div id="rateSlider" title="Rychlost přehrávání"></div>
	<div id="currentRate" class="dynamic">1x</div>
	<br/>
	<div>Zpoždění zvuku</div>
	<div id="audioSlider" title="Zpoždění zvuku"></div>
	<div id="currentAudioDelay" class="dynamic">0s</div>
	<br/>
	<div>Zpoždění titulků</div>
	<div id="subtitleSlider" title="Zpoždění titulků"></div>
	<div id="currentSubtitleDelay" class="dynamic">0s</div>
</div>
<script type="text/javascript">
//<![CDATA[
	$(function(){
		$('#window_mosaic').dialog({
			autoOpen: false,
			width: 800,
			maxWidth: 1000,
			minWidth: 800,
			minHeight: 500,
			modal: true,
			buttons: {
				"Vytvořit": function() {
					$(this).dialog("close");
				},
				"Zrušit" : function(){
					$(this).dialog("close")
				}
			}
		});
		$('#mosaic_bg').resizable({
			maxWidth: 780,
			ghost: true
		});
		$('#mosaic_tiles').draggable({
			maxWidth: 780,
			handle: 'h3',
			containment: [13,98,99999999,99999999],
			drag:function(event,ui){
				var xoff	=	ui.offset.left - $('#mosaic_bg').offset().left;
				var yoff	=	ui.offset.top - $('#mosaic_bg').offset().top-17;
				$('#mosaic_xoff').val(xoff);
				$('#mosaic_yoff').val(yoff);
			}
		});
		$('input','#mosaic_options').change(setMosaic);
		setMosaic();
	});
	function setMosaic(){
		var rows	=	Number($('#mosaic_rows').val());
		var cols	=	Number($('#mosaic_cols').val());
		var n		=	0;
		$('#mosaic_tiles').empty()
		$('#mosaic_tiles').append('<tr><td colspan="99"><h3 style="margin:0px;cursor:move; font-weight:normal" class="ui-widget-header">Mozaikové dlaždice</h3></td></tr>');
		for(var i=0;i<rows;i++){
			$('#mosaic_tiles').append('<tr>');
			for(var j=0;j<cols;j++){
				$('tr:last','#mosaic_tiles').append('<td class="mosaic">');
				$('td:last','#mosaic_tiles').append('<div id="mosaic_open__'+n+'" class="button icon ui-widget ui-state-default" title="Open Media" style="margin-top:49%"><span class="ui-icon ui-icon-eject"></span></div>');
				n++;
			}
		}
		$('.mosaic').resizable({
			alsoResize: '.mosaic',
			resize:function(event,ui){
				$('#mosaic_width').val(ui.size.width);
				$('#mosaic_height').val(ui.size.height);
				$('[id^=mosaic_open]').css({
					'margin-top': Number($('#mosaic_height').val()/2)
				});
			}
		});
		$('.mosaic').css({
			'background': '#33FF33',
			'width': Number($('#mosaic_width').val()),
			'height':Number($('#mosaic_height').val()),
			'text-align': 'center',
			'float' : 'left',
			'border' : '1px solid #990000',
			'margin-left': Number($('#mosaic_rbord').val()),
			'margin-right': Number($('#mosaic_rbord').val()),
			'margin-top': Number($('#mosaic_cbord').val()),
			'margin-bottom': Number($('#mosaic_cbord').val())
		});
		$('[id^=mosaic_open_]').each(function(){
			$(this).css({
				'margin-top': Number($('#mosaic_height').val()/2)
			});
			$(this).click(function(){
				browse_target	=	'#'+$(this).attr('id');
				get_dir();
				$('#window_browse').dialog('open');
			});
		});

		$('.button').hover(
			function() { $(this).addClass('ui-state-hover'); },
			function() { $(this).removeClass('ui-state-hover'); }
		);
	}
//]]>
</script>

<div id="window_mosaic" title="Create Mosaic">
	<table id="mosaic_options">
		<tr>
			<td style="text-align:right">Řádky</td>
			<td>
				<input type="text" name="mosaic_rows" id="mosaic_rows" size="3" value="2"/>
			</td>
			<td style="text-align:right">Posun X</td>
			<td>
				<input type="text" name="mosaic_xoff" id="mosaic_xoff" size="3" value="0" disabled="disabled"/>
			</td>
			<td style="text-align:right">Okraj řádky</td>
			<td>
				<input type="text" name="mosaic_rbord" id="mosaic_rbord" size="3" value="5"/>
			</td>
			<td style="text-align:right">Šířka</td>
			<td>
				<input type="text" name="mosaic_width" id="mosaic_width" size="3" value="100" disabled="disabled"/>
			</td>
		</tr>
		<tr>
			<td style="text-align:right">Sloupce</td>
			<td>
				<input type="text" name="mosaic_cols" id="mosaic_cols" size="3" value="2"/>
			</td>
			<td style="text-align:right">Posun Y</td>
			<td>
				<input type="text" name="mosaic_yoff" id="mosaic_yoff" size="3" value="0" disabled="disabled"/>
			</td>
			<td style="text-align:right">Okraj sloupce</td>
			<td>
				<input type="text" name="mosaic_cbord" id="mosaic_cbord" size="3" value="5"/>
			</td>
			<td style="text-align:right">Výška</td>
			<td>
				<input type="text" name="mosaic_height" id="mosaic_height" size="3" value="100" disabled="disabled"/>
			</td>
		</tr>
	</table>
	<div id="mosaic_bg" class="ui-widget-content" style="background: #3333FF;width:400px; height:300px;text-align: center; vertical-align: middle;">
		<h3 style="margin:0px;font-weight:normal" class="ui-widget-header">Pozadí</h3>
		<table id="mosaic_tiles" class="ui-widget-content" cellpadding="0" cellspacing="0">
			<tr><td colspan="99"><h3 style="margin:0px;cursor:move; font-weight:normal" class="ui-widget-header">Mozaikové dlaždice</h3></td></tr>
			<tr>
				<td class="mosaic"></td>
				<td class="mosaic"></td>
			</tr>
			<tr>
				<td class="mosaic"></td>
				<td class="mosaic"></td>
			</tr>
		</table>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
	var bands	=	new Array('60Hz','170Hz','310Hz','600Hz','1kHz','3kHz','6kHz','12kHz','14kHz','16kHz');
	$(function(){
		$('#window_equalizer').dialog({
			autoOpen: false,
			height: 650,
			width: 500,
			resizable: false,
			buttons:{
				"Obnovit":function(){
					$('.eqBand').each(function(){
						$(this).slider('value',0);
						sendEQCmd({
							command:'equalizer',
							val: 0,
							band: $(this).attr('id').substr(2)
						})
					});

				},
				"Close":function(){
					$(this).dialog("close");
				}
			}
		});
		$('#preamp').slider({
			min: -20,
			max: 20,
			step: 0.1,
			range: "min",
			animate: true,
			stop: function(event,ui){
				$('#preamp_txt').empty().append(ui.value+'dB');
				sendEQCmd({
					command:'preamp',
					val: ui.value
				})
			},
			slide: function(event,ui){
				$('#preamp_txt').empty().append(ui.value+'dB');
			}
		});
	});
//]]>
</script>
<div id="window_equalizer" title="Graphical Equalizer">
	<div style="margin: 5px 5px 5px 5px;">
		<div>Předzesílení: <span id="preamp_txt">0dB</span></div>
	</div>
	<div style="margin: 5px 5px 10px 5px;">
		<div id="preamp" style="font-size: 18px;"></div>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
	$(function(){
		$('#window_batch').dialog({
			autoOpen: false,
			width: 600,
			modal: true,
			buttons:{
				"Odeslat":function(){
					var cmds	=	$('#batchCommand').val().split("\n");
					for(var i=0;i<cmds.length;i++){
						cmds[i]	=	cmds[i].replace(/^#.*$/,'\n');
					}
					cmds	=	cmds.join(";").replace(/\n/g,';').replace(/;+/g,';').replace(/^;/,'');
					sendVLMCmd(cmds);
					$(this).dialog('close');
				},
				"Zrušit":function(){
					$(this).dialog('close');
				}
			}
		});
	})
//]]>
</script>
<div id="window_batch" title="VLM Batch Commands">
<textarea id="batchCommand" cols="50" rows="16">
#paste your VLM commands here
#separate commands with a new line or a semi-colon</textarea>
</div>
<script type="text/javascript">
	$(function(){
		$('#window_error').dialog({
			autoOpen: false,
			width:400,
			modal: true,
			buttons:{
				"Close":function(){
					$('#error_container').empty();
					$(this).dialog('close');
				}
			}
			});
	})
</script>
<div id="window_error" title="Error!">
	<div class="ui-state-error"><div class="ui-icon ui-icon-alert"></div></div>
	<div id="error_container" class="ui-state-error"></div>
</div>
	</body>
</html>
