<?php

/**
 * The user search page
 *
 * @author Johnnatan Messias <johnme@mpi-sws.org>
 */

require_once 'include/init.php';
require_once 'main.php';

if (!isset($_SESSION['user_logged'])) {
	header("HTTP/1.1 303 See Other");
	header("Location: ./login.php?ecode=not_logged_in");
	exit();
}

try {
	$db = db_connect(DSN_MAIN);
} catch (Exception $e) {
	header("HTTP/1.1 303 See Other");
	header("Location: ./login.php");
}
$flag = trim(get_default($_GET, 'flag', ''));
$query = trim(get_default($_GET, 'query', ''));
$obtained_at = trim(get_default($_GET, 'obtained_at', ''));
$end_date = trim(get_default($_GET, 'end_date', ''));
$page = 0;

$interval = new DateInterval('P1D');
$previous_date = new DateTime($obtained_at);
$next_date = new DateTime($obtained_at);

$previous_date->sub($interval);
$next_date->add($interval);


if ($obtained_at == "") {
	header("HTTP/1.1 303 See Other");
	header("Location: ./index.php?ecode=empty_search_date");
	exit();
}

if ($flag === "images") {
	$images = get_images_by_date($db, $obtained_at);
	if (count($images) == 0) {
		header("HTTP/1.1 303 See Other");
		header("Location: ./index.php?ecode=date_not_found&input_error=" . utf8_encode(strftime('%A, %d de %B de %Y', strtotime($obtained_at))));
		exit();
	}

	$search_for = $obtained_at;
} else {
	header("HTTP/1.1 303 See Other");
	header("Location: ./index.php?ecode=date_not_found&input_error=" . utf8_encode(strftime('%A, %d de %B de %Y', strtotime($obtained_at))));
}

// Start Main Page Display {{{1
?>
<?php include 'header.php' ?>

<link rel="stylesheet" media="screen" href="css/images.css?30">
<link rel="stylesheet" media="screen" href="styles/audio.css?30">
<link rel="stylesheet" media="screen" href="styles/popup_grupo.css?30">
<link rel="stylesheet" media="screen" href="styles/links.css?30">
<link rel="stylesheet" media="screen" href="styles/messages.css?30">
<link rel="stylesheet" media="screen" href="styles/videos.css?21">



<style>
	.form-group input[type="checkbox"] {
		display: none;
	}

	.form-group input[type="checkbox"]+.btn-group>label span {
		width: 20px;
	}

	.form-group input[type="checkbox"]+.btn-group>label span:first-child {
		display: none;
	}

	.form-group input[type="checkbox"]+.btn-group>label span:last-child {
		display: inline-block;
	}

	.form-group input[type="checkbox"]:checked+.btn-group>label span:first-child {
		display: inline-block;
	}

	.form-group input[type="checkbox"]:checked+.btn-group>label span:last-child {
		display: none;
	}
</style>



<body class="nav-md" data-spy="scroll" data-target=".navbar" data-offset="50">
	<div class="container body">
		<?php include_once "include/analyticstracking.php" ?>
		<div class="main_container">
			<!-- page content -->
			<div class="right_col" role="main">
				<?php include 'tiles.php' ?>
				<section>
					<div class="clearfix"></div>
					<!-- page content -->
					<div class="right_col" role="main">
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div class="input-group col-md-8 col-sm-8 col-xs-8 center-block">
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<center style="margin-bottom: 9px;">
												<strong>
													<div class="lang" key="findcontent"></div>
												</strong>

												<? if (isset($_SESSION['last_upd_date'])) : ?>
													<div>
														<h5>Last update date: <?= DateTime::createFromFormat('Y-m-d', $_SESSION['last_upd_date'])->format('m/d/Y'); ?></h5>
													</div>
												<? endif ?>
											</center>
											<!-- Date Picker Input -->
											<div>
												<p class="lang" key="start_date"></p>
												<div class="input-group">
													<span class="input-group-addon">
														<i class="glyphicon glyphicon-calendar"></i>
													</span>
													<input type="text" class="form-control date-picker date" placeholder="mm/dd/yyyy" id="datepicker1" size="24">
												</div>
												<br>
												<p class="lang" key="end_date"></p>
												<div class="input-group">
													<span class="input-group-addon">
														<i class="glyphicon glyphicon-calendar"></i>
													</span>
													<input type="text" class="form-control date-picker date" placeholder="mm/dd/yyyy" id="datepicker2" size="24">
												</div>
												<br>
												<button class="btn btn-primary" id="clickDate">
													<div class="lang" key="search"></div>
												</button>
												<div id="output"></div>

											</div>


										</div>
									</div>
								</div>
							</div>
							<section>
								<div class="container">
									<ul class="pager">
										<li class="previous"><a href="app.php?flag=images&obtained_at=<?= $previous_date->format('Y-m-d') ?>">
												<div class="lang" key="previousday"></div>
											</a></li>
										<li class="next"><a href="app.php?flag=images&obtained_at=<?= $next_date->format('Y-m-d') ?>">
												<div class="lang" key="nextday"></div>
											</a></li>
									</ul>
								</div>
							</section>
							<?php include 'tabs_info.php' ?>
							<section>
								<div class="container">
									<ul class="pager">
										<button type="button" value="<?= MAX_IMAGES ?>" class="btn btn-primary" id="load_more" onclick="load_more_data()">
											<div class="lang" key="load"></div>
										</button>
									</ul>
								</div>
							</section>
							<div class="clearfix"></div>
						</div>
					</div>
			</div>
		</div>
		</section>
		<!-- /page content -->

	</div>
	</div>
	<div id="custom_notifications" class="custom-notifications dsp_none">
		<ul class="list-unstyled notifications clearfix" data-tabbed_notifications="notif-group">
		</ul>
		<div class="clearfix"></div>
		<div id="notif-group" class="tabbed_notifications"></div>
	</div>

	<!-- bootstrap progress js -->
	<script src="js/progressbar/bootstrap-progressbar.min.js"></script>
	<script src="js/nicescroll/jquery.nicescroll.min.js"></script>
	<script src="js/custom.js"></script>

	<!-- flot js -->
	<!--[if lte IE 8]><script type="text/javascript" src="js/excanvas.min.js"></script><![endif]-->
	<script type="text/javascript" src="js/flot/jquery.flot.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.pie.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.orderBars.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.time.min.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.spline.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.stack.js"></script>
	<script type="text/javascript" src="js/flot/curvedLines.js"></script>
	<script type="text/javascript" src="js/flot/jquery.flot.resize.js"></script>


	<script type="text/javascript" src="styles/links.js"></script>
	<script type="text/javascript" src="styles/popup_grupo.js"></script>

	<script type="text/javascript">
		var last_upd_date = "<?= DateTime::createFromFormat('Y-m-d', $_SESSION['last_upd_date'])->format('m/d/Y'); ?>";
	</script>
	<script type="text/javascript">
		var url_obtained_at = "<?= $obtained_at ?>";
	</script>
	<script type="text/javascript">
		var url_end_date = "<?= $end_date ?>";
	</script>

	<script type="text/javascript" src="get_data.js"></script>
	<script type="text/javascript" src="datepicker.js" charset="utf-8"></script>


	<script language="Javascript">
		var html_audios = '<div id="big-box-audio" > <tbody>';

		function update_audios(data) {
			var elem = document.getElementById("session_tab_audios");
			var i = 0;
			var start = total_audios - step_audios;
			var max_items = Math.min(data.length, total_audios);
			//data.forEach(function(entry) { //i++; 
			for (var i = start; i < max_items; ++i) {
				var entry = data[i];
				var j = 0;
				var index = i.toString();
				var modalId = "openModalAudio" + index;
				var modalIdForm = "openModalAudioForm" + index;
				//	alert(entry['sharedGroups'].length)
				var htmlGroups = 'Lista de nomes de grupos indisponível para esse áudio!'
				if (entry['shared_groups']) {
					htmlGroups = '';
					for (j = 0; j < entry['shared_groups'].length; j++) {
						htmlGroups += "<li>" + entry['shared_groups'][j] + "</li>";
					}
				}

				html_audios += "<!– AUDIO " + i + "–>" +
					"			<li class='link_list'>" +
					"				<div id='audio-description' class='collapsed'>" +
					"					<!TITLE–>" +
					"					<a class='message_title' >" +
					"						AUDIO " + index +
					"					</a>" +
					"					<!PLAYER>" +
					"					<div class='audio_player' id='audio-players'>" +
					"						<audio controls='controls'  preload='metadata'>" +
					"						<source src='" + entry['url'] + "' type='audio/mp3'>" +
					"						</audio>" +
					"					</div>" +
					"					<!-EXPANDED DETAILS->" +
					"					<div class='flex-container'>" +
					"						<div class='box1'>" +
					"						<!-TABLE WHATSAPP->" +
					"						<table class='t1'>" +
					"							<colgroup>" +
					"								<col style='width: 50px'>" +
					"								<col style='width: 22px'>" +
					"							</colgroup>" +
					"							<tr>" +
					"								<th class='WP' colspan='2'>Compartilhamento no WhatsApp</th>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["total"] + "</td>" +
					"								<td class='tab_content'>" + entry['shareNumber'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["groups"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumberGroups'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["users"] + "</td>" +
					"								<td class='tab_content'>" + entry['shareNumberUsers'] + "</td>" +
					"							</tr>" +
					"						</table>" +
					"						" +
					"						</div>						" +
					"						<!-TABLE KEYWORDS->" +
					"						<div class='box2'>" +
					"						" +
					"							" +
					"							<div class='inner_btn darkred' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'> <a class='buttonStyle'  >" + language["evaluate"].toUpperCase() + "</a> </div>" +
					"				            <div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' href='#" + modalId + "'>" + language["groups"].toUpperCase() + "</a> </div>" +
					//"							<div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' onclick='redirect_text(" + '"' + entry['message'] + '"' + ")'>OUTRAS FONTES</a></div>"+
					"				        " +
					"													" +
					"						<div id= '" + modalId + "' class='grupos_modalDialog'>" +
					"							<div>" +
					"								<a href='#close' title='Close' class='grupos_close'>&times;</a>" +
					"								" +
					"								<div class='grupos_modal_header'>" +
					"								 <h2>Grupos em que o Conteúdo foi Compartilhado</h2>" +
					"								</div>" +
					"								<p>Lista dos nomes dos grupos onde este conteúdo foi compartilhado.</p>" +
					"								<div class='group_container'>" +
					"									<ul>" +
					"										" + htmlGroups +
					"									</ul>" +
					"								</div>" +
					"							</div>" +
					"						</div>								" +
					"							" +
					"							" +
					"							</div>" +
					"						" +
					"						</div>" +
					"					</div>						" +
					"				<!-EXPAND BUTTON->" +
					"				<div id='expand_button' class='expand_btnclass' onclick='clickHandler(this)' > <a class='link_btn blue'>" + language["details"].toUpperCase() + "</a> </div>" +
					"			</li>" + build_modal_form(modalIdForm, entry['url'], 'audio')
			}
			html_audios += "</tbody>	</div>"
			elem.innerHTML = html_audios;
		}



		function stopVideo(modalVideoId) {
			document.getElementById(modalVideoId).pause();
		}


		var html_videos = '';

		function update_videos(data) {
			var elem = document.getElementById("session_tab_videos");
			var i = 0;
			var start = total_videos - step_videos;
			var max_items = Math.min(data.length, total_videos);
			//data.forEach(function(entry) { //i++; 
			for (var i = start; i < max_items; ++i) {
				var entry = data[i];
				var j = 0;
				var index = i.toString();
				var modalId = "openModalVideo" + index;
				var modalIdGrupo = "openModalVideoGroup" + index;
				var modalIdForm = "openModalVideoForm" + index;
				var modalVideoId = 'closeVideoSource' + index;
				var video_path = "http://www.monitor-de-whatsapp.dcc.ufmg.br/indonesia/data/videos/" + entry['videoid'];
				if (entry['nsfw_score'] >= 0.8) {
					video_path = '<img src="http://www.monitor-de-whatsapp.dcc.ufmg.br/data/images/improprio.png">'
				}
				//	alert(entry['sharedGroups'].length)
				var htmlGroups = "";
				for (j = 0; j < entry['shared_groups'].length; j++) {
					htmlGroups += "<li>" + entry['shared_groups'][j] + "</li>";
				}
				html_videos += "<!– VIDEO " + i + "–>" +
					"<div class='gal-item_video' >" +
					"	  <!-- Trigger the modal with a button --> " +
					"	  <a  href='#modalVideo' class='gal_box' >" +
					"			<div class='thumb'><video src='" + video_path + "#t=0.5'></div>" +
					"			" +
					"	  </a>" +
					"	  <a class='item_hover'>" +
					"	  <button type='button' class='gal_btn blue' data-toggle='modal' data-target='#" + modalId + "'>" + language["details"].toUpperCase() + "</button>" +
					"	  <button type='button' class='gal_btn blue' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'>" + language["evaluate"].toUpperCase() + "</button>" +
					"	  </a>" +
					"	</div> " +
					"  <!-- Modal -->" +
					"  <div class='modal fade' id='" + modalId + "' role='dialog'>" +
					"    <div class='modal-dialog'>" +
					"    " +
					"      <!-- Modal content-->" +
					"      <div class='modal-content'>" +
					"        <div class='modal-header'>" +
					"			<a href='#close' title='Close' class='grupos_close' class='grupos_close' data-dismiss='modal' onclick='stopVideo(\"" + modalVideoId + "\")'>&times;</a>" +
					"          <h4 class='modal-title'>Top Whatsapp Video " + index + "</h4>" +
					"        </div>" +
					"        <div class='modal-body'>" +
					"			<div class='flex-container_modal'>" +
					"				<div class='box1'>" +
					"					<video id='" + modalVideoId + "' class='vid-item' controls='controls' preload='metadata'>" +
					"					<source src='" + video_path + "' type='video/mp4'>" +
					"					<video><div class='col-md-12 description'> </div>" +
					"				</div>		" +
					"				<div class='box2'>" +
					"					<!--TABLE WHATSAPP-->" +
					"					<div class='content_box'><table class='t1'>" +
					"						<colgroup>" +
					"							<col style='width: 50px'>" +
					"							<col style='width: 22px'>" +
					"						</colgroup>" +
					"						<tr>" +
					"							<th class='WP' colspan='2'>" + language["sharetable"] + "</th>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["total"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumber'] + "</td>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["groups"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumberGroups'] + "</td>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["users"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumberUsers'] + "</td>" +
					"						</tr>" +
					"					</table>" +
					"						" +
					"						<div class='gal_btn blue large'> <a class='buttonStyle' href='#" + modalIdGrupo + "'>" + language["groups"].toUpperCase() + "</a> </div>" +
					"				        <div class='gal_btn blue large' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'><a class='buttonStyle'>" + language["evaluate"].toUpperCase() + "</a></div>" +
					"				</div></div>" +
					"			" +
					"			</div>" +
					"       </div>" +
					"        <div class='modal-footer'>" +
					"         <button type='button' class='gal_btn blue' data-dismiss='modal' onclick='stopVideo(\"" + modalVideoId + "\")'>" + language["close"].toUpperCase() + "</button>" +
					"        </div>" +
					"      </div>" +
					"</div>" +
					"		<!--MODAL DOS GRUPOS-->" +
					"		<div id='" + modalIdGrupo + "' class='grupos_modalDialog'>" +
					"		<div>" +
					"			<a href='#close' title='Close' class='grupos_close'>&times;</a>" +
					"			" +
					"			<div class='grupos_modal_header'>" +
					"			 <h2>" + language["groupsmodal"] + "</h2>" +
					"			</div>" +
					"			<p>" + language["groupslist"] + "</p>" +
					"			<div class='list'>" +
					"				<ul>" +
					"					" + htmlGroups +
					"				</ul>" +
					"			</div>	" +
					"		</div>" +
					"	</div>" +
					"</div>" + build_modal_form(modalIdForm, entry['videoid'], 'video')
			}
			elem.innerHTML = html_videos;

		}


		function openUrl(url) {
			if (confirm(language["redirectwarning"]))
				document.location = url;
		}

		var html_links = '<div id="big-box" > <tbody>';

		function update_links(data) {
			var elem = document.getElementById("session_tab_links");
			var i = 0;
			var start = total_links - step_links;
			var max_items = Math.min(data.length, total_links);
			//data.forEach(function(entry) { //i++; 
			for (var i = start; i < max_items; ++i) {
				var entry = data[i];
				var j = 0;
				var index = i.toString();
				var modalId = "openModal" + index;
				var modalIdForm = "openModalLinkForm" + index;
				//	alert(entry['sharedGroups'].length)
				var htmlGroups = "";
				for (j = 0; j < entry['shared_groups'].length; j++) {
					htmlGroups += "<li>" + entry['shared_groups'][j] + "</li>";
				}

				html_links += "<!– LINK " + i + "–>" +
					"			<li class='link_list'>" +
					"				<div id='link-description' class='collapsed'>" +
					"					<!–FEATURED IMAGE–>" +
					"					<div class='link_thumbnail'>" +
					"						<img class='link_thumbnail_img' src='" + entry['link_image'] + "'>" +
					"					</div>" +
					"					<!TITLE–>" +
					"					<a class='link_title' href='#' onclick=" + '"' + "openUrl('" + entry['LinkID'] + "')" + '"' + " >" +
					"						" + entry['link_title'] +
					"					</a>" +
					"					<!–AUTHOR–>" +
					"					<a class='link_author' href='#' onclick=" + '"' + "openUrl('" + entry['LinkID'] + "')" + '"' + " >" +
					"						<br>Por/Em: <b>" + entry['link_author'] + "</b>" +
					"					</a>" +
					"					<!–DATE–>" +
					"					<div class='link_date'>" +
					"						" + language["date"] + ": <b>" + entry['obtained_at'] + "</b>" +
					"					</div>" +
					"				<!–DESCRIPTION–>" +
					"					<div>" +
					"						<p class='text'> " + entry['link_description'] + " </p>" +
					"					</div>" +
					"					<!-EXPANDED DETAILS->" +
					"					<div class='flex-container'>" +
					"						<div class='box1'>" +
					"						<!-TABLE WHATSAPP->" +
					"						<table class='t1'>" +
					"							<colgroup>" +
					"								<col style='width: 50px'>" +
					"								<col style='width: 22px'>" +
					"							</colgroup>" +
					"							<tr>" +
					"								<th class='WP' colspan='2'>" + language["sharetable"] + "</th>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["total"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumber'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["groups"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumberGroups'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["users"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumberUsers'] + "</td>" +
					"							</tr>" +
					"						</table>" +
					"						" +
					"						</div>						" +
					"						<!-TABLE KEYWORDS->" +
					"						<div class='box2'>" +
					"						" +
					"							<table class='t2'>" +
					"								<tr>" +
					"									<td class='tab_title'>Keywords:</td>" +
					"									<td class='keywords'> " + entry['link_keywords'] + "</td>" +
					"								</tr>" +
					"							</table>" +
					"							" +
					"							<div class='inner_btn darkred' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'> <a class='buttonStyle'  >" + language["evaluate"].toUpperCase() + "</a> </div>" +
					"							<div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' href='#" + modalId + "'>" + language["groups"].toUpperCase() + "</a> </div>" +
					"							<div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' onclick='redirect_text(" + '"' + entry['link_title'] + '"' + ")'>" + language["othersources"].toUpperCase() + "</a></div>" +
					"													" +
					"						<div id= '" + modalId + "' class='grupos_modalDialog'>" +
					"							<div>" +
					"								<a href='#close' title='Close' class='grupos_close'>&times;</a>" +
					"								" +
					"								<div class='grupos_modal_header'>" +
					"								 <h2>" + language["groupsmodel"] + "</h2>" +
					"								</div>" +
					"								<p>" + language["groupslist"] + "</p>" +
					"								<div class='group_container'>" +
					"									<ul>" +
					"										" + htmlGroups +
					"									</ul>" +
					"								</div>" +
					"							</div>" +
					"						</div>								" +
					"							" +
					"							" +
					"							</div>" +
					"						" +
					"						</div>" +
					"					</div>						" +
					"				<!-EXPAND BUTTON->" +
					"				<div id='expand_button' class='expand_btnclass' onclick='clickHandler(this)' > <a class='link_btn blue'>" + language["details"].toUpperCase() + "</a> </div>" +
					"			</li>" + build_modal_form(modalIdForm, entry['LinkID'], 'link')
			}
			html_links += "</tbody>	</div>"
			elem.innerHTML = html_links;
		}



		var html_messages = '<div id="big-box" > <tbody>';

		function update_messages(data) {
			var elem = document.getElementById("session_tab_mensagem");
			var i = 0;
			var start = total_messages - step_messages;
			var max_items = Math.min(data.length, total_messages);
			//data.forEach(function(entry) { //i++; 
			for (var i = start; i < max_items; ++i) {
				var entry = data[i];
				var j = 0;
				var index = i.toString();
				var modalId = "openModalMessage" + index;
				var modalIdForm = "openModalMessageForm" + index;
				//	alert(entry['sharedGroups'].length)
				var htmlGroups = "";
				for (j = 0; j < entry['shared_groups'].length; j++) {
					htmlGroups += "<li>" + entry['shared_groups'][j] + "</li>";
				}

				html_messages += "<!– LINK " + i + "–>" +
					"			<li class='link_list'>" +
					"				<div id='message-description' class='collapsed'>" +
					"					<!TITLE–>" +
					"					<a class='message_title' >" +
					"						MENSAGEM " + index +
					"					</a>" +
					"					<!–AUTHOR–>" +

					"				<!–DESCRIPTION–>" +
					"					<div>" +
					"						<p class='text'> " + entry['message'] + " </p>" +
					"					</div>" +
					"					<!-EXPANDED DETAILS->" +
					"					<div class='flex-container'>" +
					"						<div class='box1'>" +
					"						<!-TABLE WHATSAPP->" +
					"						<table class='t1'>" +
					"							<colgroup>" +
					"								<col style='width: 50px'>" +
					"								<col style='width: 22px'>" +
					"							</colgroup>" +
					"							<tr>" +
					"								<th class='WP' colspan='2'>" + language["sharetable"] + "</th>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["total"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumber'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["groups"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumberGroups'] + "</td>" +
					"							</tr>" +
					"							<tr>" +
					"								<td class='tab_title'>" + language["users"] + ":</td>" +
					"								<td class='tab_content'>" + entry['shareNumberUsers'] + "</td>" +
					"							</tr>" +
					"						</table>" +
					"						" +
					"						</div>						" +
					"						<!-TABLE KEYWORDS->" +
					"						<div class='box2'>" +
					"						" +
					"							" +
					"							<div class='inner_btn darkred' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'> <a class='buttonStyle'  >" + language["evaluate"].toUpperCase() + "</a> </div>" +
					"				            <div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' href='#" + modalId + "'>" + language["groups"].toUpperCase() + "</a> </div>" +
					"							<div class='inner_btn darkred'> <a class='buttonStyle' style='text-decoration:none' onclick='redirect_text(" + '"' + entry['message'] + '"' + ")'>" + language["othersources"].toUpperCase() + "</a></div>" +
					"				        " +
					"													" +
					"						<div id= '" + modalId + "' class='grupos_modalDialog'>" +
					"							<div>" +
					"								<a href='#close' title='Close' class='grupos_close'>&times;</a>" +
					"								" +
					"								<div class='grupos_modal_header'>" +
					"								 <h2>" + language["groupsmodal"] + "</h2>" +
					"								</div>" +
					"								<p>" + language["groupslist"] + "</p>" +
					"								<div class='group_container'>" +
					"									<ul>" +
					"										" + htmlGroups +
					"									</ul>" +
					"								</div>" +
					"							</div>" +
					"						</div>								" +
					"							" +
					"							" +
					"							</div>" +
					"						" +
					"						</div>" +
					"					</div>						" +
					"				<!-EXPAND BUTTON->" +
					"				<div id='expand_button' class='expand_btnclass' onclick='clickHandler(this)' > <a class='link_btn blue'>" + language["details"].toUpperCase() + "</a> </div>" +
					"			</li>" + build_modal_form(modalIdForm, entry['messageid'], 'message')
			}
			html_messages += "</tbody>	</div>"
			elem.innerHTML = html_messages;
		}

		var html_images = '';

		function update_images(data) {
			var elem = document.getElementById("session_tab_images");
			elem_but = document.getElementById("load_more");
			var i = 0;
			var start = total_images - step_images;
			//value = parseInt(elem_but.value);
			//i = value - parseInt(<?= MAX_IMAGES ?>);
			var max_items = Math.min(data.length, total_images);
			//data.forEach(function(entry) {
			for (var i = start; i < max_items; ++i) {
				var entry = data[i];
				//i++; 
				var j = 0;
				var index = i.toString();
				var modalId = "openModalImage" + index;
				var modalIdGrupo = "openModalImageGroup" + index;
				var modalIdForm = "openModalImageForm" + index;
				var image_path = 'http://www.monitor-de-whatsapp.dcc.ufmg.br/indonesia/data/images/' + entry['imageid'];
				if (entry['nsfw_score'] >= 0.8) {
					image_path = 'http://www.monitor-de-whatsapp.dcc.ufmg.br/data/images/improprio.png'
				}
				var htmlGroups = 'Lista de nomes de grupos indisponível para essa imagem!'

				if (entry['shared_groups']) {
					htmlGroups = '';
					for (j = 0; j < entry['shared_groups'].length; j++) {
						htmlGroups += "<li>" + entry['shared_groups'][j] + "</li>";
					}
				}
				html_images += "<!– IMAGEM " + i + "–>" +
					"<div class='gal-item_video' >" +
					"	  <!- Trigger the modal with a button -> " +
					"	  <a  href='#modalVideo' class='gal_box' >" +
					"			<div class='thumb'>" +
					"           <img src='" + image_path + "'> </div>" +
					"			" +
					"	  </a>" +
					"	  <a class='item_hover'>" +
					"	  <button type='button' class='gal_btn blue' data-toggle='modal' data-target='#" + modalId + "'>" + language["details"].toUpperCase() + "</button>" +
					"	  <button type='button' class='gal_btn blue' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'>" + language["evaluate"].toUpperCase() + "</button>" +
					"	  </a>" +
					"	</div> " +
					"  <!- Modal ->" +
					"  <div class='modal fade' id='" + modalId + "' role='dialog'>" +
					"    <div class='modal-dialog'>" +
					"    " +
					"      <!- Modal content->" +
					"      <div class='modal-content'>" +
					"        <div class='modal-header'>" +
					"			<a href='#close' title='Close' class='grupos_close' class='grupos_close' data-dismiss='modal'>&times;</a>" +
					"          <h4 class='modal-title'>Top Whatsapp Image " + index + "</h4>" +
					"        </div>" +
					"        <div class='modal-body'>" +
					"			<div class='flex-container_modal'>" +
					"				<div class='box1'>" +
					"           		<img class ='expanded_image' src='" + image_path + "'>" +
					"				</div>" +
					"				<div class='box2'>" +
					"					<!-TABLE WHATSAPP->" +
					"					<div class='content_box'><table class='t1'>" +
					"						<colgroup>" +
					"							<col style='width: 50px'>" +
					"							<col style='width: 22px'>" +
					"						</colgroup>" +
					"						<tr>" +
					"							<th class='WP' colspan='2'>" + language["sharetable"] + "</th>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["total"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumber'] + "</td>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["groups"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumberGroups'] + "</td>" +
					"						</tr>" +
					"						<tr>" +
					"							<td class='tab_title'>" + language["users"] + ":</td>" +
					"							<td class='tab_content'>" + entry['shareNumberUsers'] + "</td>" +
					"						</tr>" +
					"					</table>" +
					"						" +
					"						<div class='gal_btn blue large'> <a class='buttonStyle' href='#" + modalIdGrupo + "'>" + language["groups"].toUpperCase() + "</a> </div>" +
					"						<div class='gal_btn blue large' value='" + image_path + "' onclick='redirect_image(" + '"' + image_path + '"' + ")'><a class='buttonStyle'>" + language["othersources"].toUpperCase() + "</a></div>" +
					"				        <div class='gal_btn blue large' data-toggle='modal' data-target='#modal-form-" + modalIdForm + "'><a class='buttonStyle'>" + language["evaluate"].toUpperCase() + "</a></div>" +
					"				</div></div>" +
					"			" +
					"			</div>" +
					"       </div>" +
					"        <div class='modal-footer'>" +
					"         <button type='button' class='gal_btn blue' data-dismiss='modal'>" + language["close"].toUpperCase() + "</button>" +
					"        </div>" +
					"      </div>" +
					"</div>" +
					"		<!-MODAL DOS GRUPOS->" +
					"		<div id='" + modalIdGrupo + "' class='grupos_modalDialog'>" +
					"		<div>" +
					"			<a href='#close' title='Close' class='grupos_close'>&times;</a>" +
					"			" +
					"			<div class='grupos_modal_header'>" +
					"			 <h2>" + language["groupsmodal"] + "</h2>" +
					"			</div>" +
					"			<p>" + language["groupslist"] + "</p>" +
					"			<div class='list'>" +
					"				<ul>" +
					"					" + htmlGroups +
					"				</ul>" +
					"			</div>	" +
					"		</div>" +
					"	</div>" +
					"</div>" + build_modal_form(modalIdForm, entry['imageid'], 'image')
			}
			elem.innerHTML = html_images;

		}


		function build_modal_form(modalIdForm, imageid, data_type) {

			html_modal_form = '<div class="modal fade" id="modal-form-' + modalIdForm + '" role="dialog">'
			html_modal_form += '<div class="modal-dialog">'

			html_modal_form += '<div class="modal-content">'
			html_modal_form += '<div class="modal-header">'
			html_modal_form += '<button type="button" class="close" data-dismiss="modal">&times;</button>'
			html_modal_form += '<h4 class="modal-title">Avaliação</h4>'
			html_modal_form += '</div>'
			html_modal_form += '<div class="modal-body">'
			html_modal_form += '<p>Escolha uma ou mais categorias que caracterizam a Imagem selecionada</p>'

			html_modal_form += '<div class="row">'
			html_modal_form += '<form name="form-' + imageid + '">'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="conteudo_improprio"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_1" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_1" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_1" class="[ btn btn-default ]" data-toggle="tooltip" title="Presença de conteúdo pornográfico.">Conteúdo Impróprio</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="conteudo_politico" id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_2" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_2" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_2" class="[ btn btn-default  ]" data-toggle="tooltip" title="Contém informações de algum candidato ou partido com o objetivo de divulgar e enaltecer alguma figura política">Conteúdo Político</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="disseminacao_de_odio"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_3" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_3" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_3" class="[ btn btn-default ]" data-toggle="tooltip" title="Cujo conteúdo promove, racismo, homofobia, e outras formas de discriminação.">Disseminação de Ódio</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="falsa"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_4" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_4" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_4" class="[ btn btn-default ]" data-toggle="tooltip" title="Cujo conteúdo não é confiável e está comprovadamente incorreto">Falsa</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="merece_investigacao"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_5" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_5" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_5" class="[ btn btn-default ]" data-toggle="tooltip" title="Cujo conteúdo merece ser investigado">Merece Investigação</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'


			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="noticia"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_6" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_6" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_6" class="[ btn btn-default ]" data-toggle="tooltip" title="Exposição de informações sobre algum acontecimento ou evento, contendo citação ou referência a algum jornal, revista ou portal de notícias">Notícia</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="promocao_de_produtos_ilicitos"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_7" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_7" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_7" class="[ btn btn-default ]" data-toggle="tooltip" title="Cujo objetivo é promover ofertas de produtos não permitidos pela lei como notas falsas, drogas e outros produtos ilícitos.">Promoção de produtos Ilícitos</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="propaganda"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_8" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_8" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_8" class="[ btn btn-default ]" data-toggle="tooltip" title="Contém comerciais e ofertas de algum produto ou estabelecimento.">Propaganda</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="satira"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_9" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_9" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_9" class="[ btn btn-default ]" data-toggle="tooltip" title="Informação e conteúdo humorístico a respeito de eventos atuais.">Sátira</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="selfie"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_10" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_10" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_10" class="[ btn btn-default ]" data-toggle="tooltip" title="Cujo conteúdo expõe a imagem de uma figura não conhecida, não se tratando de figuras públicas.">Selfie</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="suspeita_a_ser_falsa"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_11" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_11" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_11" class="[ btn btn-default ]" data-toggle="tooltip" title="A informação está contraditória ou ainda não há comprovação dos fatos.">Suspeita a ser falsa</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="suspeita_a_ser_verdadeira"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_12" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_12" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_12" class="[ btn btn-default ]" data-toggle="tooltip" title="a informação está correta, mas precisa de mais explicações ou ainda não há uma comprovação.">Suspeita a ser verdadeira</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="verdadeira"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_13" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_13" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_13" class="[ btn btn-default ]" data-toggle="tooltip" title="A informação está comprovadamente correta.">Verdadeira</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="violencia"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_14" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_14" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_14" class="[ btn btn-default ]" data-toggle="tooltip" title="Contém violência e expõe ferimentos, lesões e golpes.">Violência</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'


			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="ativismo"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_15" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_15" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_15" class="[ btn btn-default ]" data-toggle="tooltip" title="Movimentos populares, manifestações e protestso.">Ativismo</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'



			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="opiniao"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_16" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_16" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_16" class="[ btn btn-default ]" data-toggle="tooltip" title="Movimentos populares, manifestações e protestso.">Opinião</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'




			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="foto"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_17" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_17" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_17" class="[ btn btn-default ]" data-toggle="tooltip" title="Movimentos populares, manifestações e protestso.">Foto/Paisagem</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'




			html_modal_form += '<div class="col-md-6 col-sm-6 col-xs-12 [ form-group ]">'
			html_modal_form += '<input type="checkbox" name="tags" value="diversos"  id="fancy-checkbox-' + imageid + '_' + modalIdForm + '_18" autocomplete="off" />'
			html_modal_form += '<div class="[ btn-group ]">'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_18" class="[ btn btn-info ]">'
			html_modal_form += '<span class="[ glyphicon glyphicon-ok ]"></span>'
			html_modal_form += '<span> </span>'
			html_modal_form += '</label>'
			html_modal_form += '<label for="fancy-checkbox-' + imageid + '_' + modalIdForm + '_18" class="[ btn btn-default ]" data-toggle="tooltip" title="Movimentos populares, manifestações e protestso.">Diversos</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="col-md-12 col-sm-12 col-xs-12 [ form-group ]">'
			html_modal_form += '<label class="col-md-12 col-sm-12 col-xs-12 " data-toggle="tooltip" title="Cujo conteúdo não se encaixa em nenhuma das categorias descritas ou possíveis comentários.">'
			html_modal_form += 'Comentários: <textarea row="5" name="outros" class="form-control"></textarea>'
			html_modal_form += '</label>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'

			html_modal_form += '<div class="modal-footer">'
			html_modal_form += '<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>'
			html_modal_form += '<button type="button" class="btn btn-primary" value="' + imageid + '" onclick="send_form(this.value, \'' + data_type + '\')" data-dismiss="modal">Enviar</button>'
			html_modal_form += '</div>'
			html_modal_form += '</div>'
			html_modal_form += '</form>'

			html_modal_form += '</div>'
			html_modal_form += '</div>'

			return html_modal_form
		}
	</script>


	<script language="Javascript" type="text/javascript">
		function redirect_image(query) {
			var image_url = 'http://www.monitor-de-whatsapp.dcc.ufmg.br/indonesia/data/images/' + query;
			var search_url = 'http://images.google.com/searchbyimage?image_url=' + query;
			window.open(search_url, '_blank');
		}

		function redirect_text(query) {
			var search_url = 'http://images.google.com/search?q=' + query;
			window.open(search_url, '_blank');
		}

		function send_form(id, data_type) {
			var tags_dict = {};
			form_name = "form-" + id;
			form = document.forms[form_name];
			comments = form.outros.value;
			tags = form.tags;
			type = data_type.replace("'", "_");
			tags.forEach(function(tag) {
				tags_dict[tag.value] = tag.checked ? 'TRUE' : 'FALSE';
			});

			var op = 102312;
			var email = "<?= $_SESSION['user']['email'] ?>";
			var params = "op=" + op + "&email=" + email + "&imageid=" + id + "&comments=" + comments + "&tags=" + JSON.stringify(tags_dict) + "&type=" + type;
			http.open("POST", "exec_process.php");
			http.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			http.onreadystatechange = process_form_response;
			http.send(params);
		}


		function process_form_response() {
			if (http.readyState == 4) {
				var response = http.responseText;
				data = JSON.parse(response);

				//document.getElementById("load_more").disabled = true;
			}

		}
	</script>


	<script language="Javascript" type="text/javascript">
		// Altered to datepicker.js
	</script>

	<script>
		$(document).ready(function() {
			// Load the data from server
			load_initial_data();
			//get_data_from_server();

			//The default language is English
			var lng = localStorage.getItem('lang') || 'en';
			var allInputs = document.getElementsByTagName("option");
			var results = [];
			for (var x = 0; x < allInputs.length; x++)
				if (allInputs[x].value == lng)
					allInputs[x].selected = "true";

			loadlang();
		});
	</script>

	<?php include 'footer.php' ?>
</body>

</html>