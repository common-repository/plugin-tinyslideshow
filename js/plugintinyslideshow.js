jQuery(document).ready(function($) {
	// tinysliderlist
	var tinyslideshows = null;
	// show all tinyslideshows
	show_list();
	
	// events
	$(':checkbox').click(function(e) {	
		var id = e.target.id;
		var elname = 'plugintinyslideshow-cb';
		var addcb = $('#plugintinyslideshow-add-auto').is(':checked');
		var updatecb = $('#plugintinyslideshow-update-auto').is(':checked');
		
		if (id.substring(0, elname.length) == elname) {
			$(':checkbox').attr('checked', $(this).is(':checked'));		
		}
		$('#plugintinyslideshow-add-auto').attr('checked', addcb);
		$('#plugintinyslideshow-update-auto').attr('checked', updatecb);
	});
	$('#plugintinyslideshow-add').click(function(e) {
		show_dialog_add();
	});
	$('#plugintinyslideshow-delete').click(function(e) {
		var idlist = [];
		var count = 0;
		$('input:checkbox:checked').each(function(i) {
			if ($(this).attr('id') != 'plugintinyslideshow-add-auto' 
			&& $(this).attr('id') != 'plugintinyslideshow-update-auto') {
				idlist[count] = $(this).val();
			}
		});
        if (idlist.length > 0) {
        	var tinyslideshow_ids = idlist.join(',');
        	var result = remove(tinyslideshow_ids);
        	if (result[0] == true) {
        		show_list();
        		$(this).dialog('close');
        		show_dialog_notification(result[1].responseText);
        	} else {
        		$(this).dialog('close');
        		show_dialog_error(result[1].responseText);
        	}
        } else {
        	$(this).dialog('close');
        	show_dialog_error(message_select_least);
        }
	});
	$('#plugintinyslideshow-update').click(function(e) {
		var checkedcount = 0;
		$('input:checkbox:checked').each(function() {
			if ($(this).attr('id') != 'plugintinyslideshow-add-auto' 
			&& $(this).attr('id') != 'plugintinyslideshow-update-auto') {
				checkedcount++;
			}
		});
        if (checkedcount != 1) {
        	show_dialog_error(message_select_single);
        } else {
        	var id = $('input:checkbox:checked').val();
        	var thetiny = null;
        	$.each(tinyslideshows, function(i) {
        		if (tinyslideshows[i].id == id) {
        			thetiny = tinyslideshows[i];
        		}
        	});
        	$('#plugintinyslideshow-update-title').val(thetiny.title);
        	$('#plugintinyslideshow-update-speed').val(thetiny.speed);
        	$('#plugintinyslideshow-update-gallery').val(thetiny['gallery'][0].gallery);
        	$('#plugintinyslideshow-update-scrollspeed').val(thetiny.scrollspeed);
        	$('#plugintinyslideshow-update-auto').val(thetiny.auto);
        	$('#plugintinyslideshow-update-active').val(thetiny.active);
        	$('#plugintinyslideshow-update-spacing').val(thetiny.spacing);
        	var files = thetiny['gallery'][0].files;
        	$('#update-' + thetiny['gallery'][0].gallery + ' table tbody tr').each(function(i) {
        		var id = $(this).attr('id');
        		$.each(files, function(i) {
        			if (files[i].file.indexOf(id, 0) > -1) {
        				$('#' + id + '-file').text(files[i].file);
        				$('#' + id + '-title').text(files[i].title);
        				$('#' + id + '-description').val(files[i].description);
        			}
        		});
        	});
        	show_dialog_update();
        }
	});
	$('#plugintinyslideshow-add-gallery').change(function() {
		$('#files > div').hide();
        $('select#plugintinyslideshow-add-gallery option:selected').each(function () {
        	if ($(this).text() != '') {
        		$('#add-' + $(this).text()).show();
        	}
        });
	}).change();
	$('#plugintinyslideshow-update-gallery').change(function() {
		$('#files > div').hide();
        $('select#plugintinyslideshow-update-gallery option:selected').each(function () {
        	if ($(this).text() != '') {
        		$('#update-' + $(this).text()).show();
        	}
        });
	}).change();
	// dialogs
	$('#plugintinyslideshow-dialog-add').dialog({
		position: ['center', 'middle'],
		modal: true,
		draggable: false,
		resizable: false, 
		closeText: 'X',
		width: 540,
		height: 560,
		autoOpen: false,
		buttons: {
			'ajouter': function() {
				$('#plugintinyslideshow-add-error').hide();
				if ($('#plugintinyslideshow-add-gallery option:selected').val() == ''
				|| $('#plugintinyslideshow-add-gallery option:selected').size() < 1) {
					$('#plugintinyslideshow-add-error-message').text(message_gallery_required);
					$('#plugintinyslideshow-add-error').show();
				} else {
					//FIXME validate
					var gallery = [];
					var galleryname = '';
	        		$('#add-' + $('#plugintinyslideshow-add-gallery option:selected').val() + ' table tbody').each(function(i) {
	        			galleryname = $('#plugintinyslideshow-add-gallery option:selected').val();
	        			var files = [];
	        			var file = '';
	        			var filename = '';
	        			$(this).children('tr').each(function(j) {
	        				filename = $(this).attr('id');
	        				file = ({
	        					file: $('#' + filename + '-file').text(),
	        					title: $('#' + filename + '-title').val(),
	        					description: $('#' + filename + '-description').val()
	        				});
	        				files[j] = file;
	        			});
		        		gallery[i] = ({
		        			gallery: galleryname,
		        			files: files
		        		});
	        		});
					var tinyslideshow = ({
						title: $('#plugintinyslideshow-add-title').val(),
						speed: $('#plugintinyslideshow-add-speed').val(),
						gallery: gallery, 
						scrollspeed: $('#plugintinyslideshow-add-scrollspeed').val(),
						auto: $('#plugintinyslideshow-add-auto').is(':checked') ? 'true' : 'false',
						active: $('#plugintinyslideshow-add-active').val(),
						spacing: $('#plugintinyslideshow-add-spacing').val(),
					});
					var result = add(tinyslideshow);
					if (result[0] == true) {
						show_list();
						$(this).dialog('close');
						show_dialog_notification(result[1].reponseText);
					} else {
						$('#plugintinyslideshow-add-error-message').text(result[1].responseText);
						$('#plugintinyslideshow-add-error').show();
					}
				}
			},
			'annuler': function() {
				$(this).dialog('close');
			}
		}
	});
	$('#plugintinyslideshow-dialog-update').dialog({
		position: ['center', 'middle'],
		modal: true,
		draggable: false,
		resizable: false, 
		closeText: 'X',
		width: 540,
		height: 560,
		autoOpen: false,
		buttons: {
			'enregistrer': function() {
				$('#plugintinyslideshow-update-error').hide();
				var id = '';
				$('input:checkbox:checked').each(function() {
					if ($(this).attr('id') != 'plugintinyslideshow-add-auto' 
					&& $(this).attr('id') != 'plugintinyslideshow-update-auto') {
						id = $(this).val();
					}
				});
				if (id == '') {
					$('#plugintinyslideshow-update-error-message').text(message_gallery_required);
					$('#plugintinyslideshow-update-error').show();
				} else {
					var gallery = [];
					var galleryname = '';
	        		$('#update-' + $('#plugintinyslideshow-update-gallery option:selected').val() + ' table tbody').each(function(i) {
	        			galleryname = $('#plugintinyslideshow-update-gallery option:selected').val();
	        			var files = [];
	        			var file = '';
	        			var filename = '';
	        			$(this).children('tr').each(function(j) {
	        				filename = $(this).attr('id');
	        				file = ({
	        					file: $('#' + filename + '-file').text(),
	        					title: $('#' + filename + '-title').val(),
	        					description: $('#' + filename + '-description').val()
	        				});
	        				files[j] = file;
	        			});
		        		gallery[i] = ({
		        			gallery: galleryname,
		        			files: files
		        		});
	        		});
					//FIXME validate
	        		var tinyslideshow = ({
	        			id: id,
						title: $('#plugintinyslideshow-update-title').val(),
						speed: $('#plugintinyslideshow-update-speed').val(),
						gallery: gallery, 
						scrollspeed: $('#plugintinyslideshow-update-scrollspeed').val(),
						auto: $('#plugintinyslideshow-update-auto').is(':checked') ? 'true' : 'false',
						active: $('#plugintinyslideshow-update-active').val(),
						spacing: $('#plugintinyslideshow-update-spacing').val(),
					});
					var result = update(tinyslideshow);
					if (result[0] == true) {
						show_list();
						$(this).dialog('close');
						show_dialog_notification(result[1].responseText);
					} else {
						$('#plugintinyslideshow-update-error-message').text(result[1].responseText);
						$('#plugintinyslideshow-update-error').show();
					}
				}
			},
			'annuler': function() {
				$(this).dialog('close');
			}
		}
	});
	$('#plugintinyslideshow-dialog-error').dialog({
		closeOnEscape: false,
		position: ['center', 'middle'],
		modal: true,
		draggable: false,
		resizable: false, 
		closeText: 'X',
		autoOpen: false,
		buttons: {
			'ok': function() {
				$(this).dialog('close');
			}
		}
	});
	$('#plugintinyslideshow-dialog-notification').dialog({
		closeOnEscape: false,
		position: ['center', 'middle'],
		modal: true,
		draggable: false,
		resizable: false, 
		closeText: 'X',
		autoOpen: false,
		buttons: {
			'ok': function() {
				$(this).dialog('close');
			}
		}
	});
	// dialogs action
	function show_dialog_add() {
		$('#plugintinyslideshow-add-error').hide();
		$('#plugintinyslideshow-dialog-add').parent().children().eq(2).children().eq(1).text(label_cancel);
		$('#plugintinyslideshow-dialog-add').parent().children().eq(2).children().eq(0).text(label_add);
		$('#plugintinyslideshow-dialog-add').dialog('open');
	}
	function show_dialog_update() {
		$('#plugintinyslideshow-update-error').hide();
		$('#plugintinyslideshow-dialog-update').parent().children().eq(2).children().eq(1).text(label_cancel);
		$('#plugintinyslideshow-dialog-update').parent().children().eq(2).children().eq(0).text(label_save);
		$('#plugintinyslideshow-dialog-update').dialog('open');
	}
	function show_dialog_error(message) {
		$('#plugintinyslideshow-error-message').text(message);
		$('#plugintinyslideshow-dialog-error').parent().children().eq(2).children().eq(0).text(label_ok);
		$('#plugintinyslideshow-dialog-error').dialog('open');
	}
	function show_dialog_notification(message) {
		$('#plugintinyslideshow-notification-message').text(message);
		$('#plugintinyslideshow-dialog-notification').parent().children().eq(2).children().eq(0).text(label_ok);
		$('#plugintinyslideshow-dialog-notification').dialog('open');
	}
	// tinysliderlist
	function show_list() {
		list();
		$('#plugintinyslideshow-list > *').remove();
		if (tinyslideshows != null) {
			$.each(tinyslideshows, function(i) {
				$('#plugintinyslideshow-list').append('<tr id="tinyslideshow-' + i + '">');
				$('#tinyslideshow-' + i).append('<th id="tinyslideshow-cb-' + i + '" class="check-column" scope="row"></th>');
				$('#tinyslideshow-cb-' + i).append('<input id="tinyslideshow-checkbox-' + i + '" class="administrator" type="checkbox" value="' + tinyslideshows[i].id + '" name="tinyslideshow-checkbox[]">');
				$('#tinyslideshow-' + i).append('<td class="title column-title"><span id="plugintinyslideshow-title-' + tinyslideshows[i].id + '">' + tinyslideshows[i].title + '</span></td>');
				$('#tinyslideshow-' + i).append('<td class="speed column-speed"><span id="plugintinyslideshow-speed-' + tinyslideshows[i].id + '">' + tinyslideshows[i].speed + '</span></td>');
				var gallery = tinyslideshows[i]['gallery'][0].gallery;
				var filescount = tinyslideshows[i]['gallery'][0].files.length;
				$('#tinyslideshow-' + i).append('<td class="gallery column-gallery"><span id="plugintinyslideshow-gallery-' + tinyslideshows[i].id + '">' + gallery + '(' + filescount + ')</span></td>');
				$('#tinyslideshow-' + i).append('<td class="scrollspeed column-scrollspeed"><span id="plugintinyslideshow-scrollspeed-' + tinyslideshows[i].id + '">' + tinyslideshows[i].speed + '</span></td>');
				$('#tinyslideshow-' + i).append('<td class="auto column-auto"><span id="plugintinyslideshow-auto-' + tinyslideshows[i].id + '">' + tinyslideshows[i].auto + '</span></td>');
				$('#tinyslideshow-' + i).append('<td class="active column-active"><span id="plugintinyslideshow-active-' + tinyslideshows[i].id + '">' + tinyslideshows[i].active + '</span></td>');
				$('#tinyslideshow-' + i).append('<td class="spacing column-spacing"><span id="plugintinyslideshow-spacing-' + tinyslideshows[i].id + '">' + tinyslideshows[i].spacing + '</span></td>');
				$('#tinyslideshow-' + i).append('<td class="shortcode column-shortcode"><span>[plugintinyslideshow id="' + tinyslideshows[i].id + '"]</span></td>');
			});
		} else {
			$('#plugintinyslideshow-list').append('<tr><th colspan="7">' + message_no_tinyslideshow + '</th></tr>');
		}
	}
	// actions
	function add(tinyslideshow) {
		var params = ({
			action: 'tinyslideshow_add',
			tinyslideshow_title: tinyslideshow.title,
			tinyslideshow_speed: tinyslideshow.speed,
			tinyslideshow_gallery: $.toJSON(tinyslideshow.gallery),
			tinyslideshow_scrollspeed: tinyslideshow.scrollspeed,
			tinyslideshow_active: tinyslideshow.active,
			tinyslideshow_spacing: tinyslideshow.spacing,
			tinyslideshow_auto: tinyslideshow.auto,
			cookie: encodeURIComponent(document.cookie)
		});
		var data = exec_ajax(params);
		return data;
	}
	function update(tinyslideshow) {
		var params = ({
			action: 'tinyslideshow_update',
			tinyslideshow_id: tinyslideshow.id,
			tinyslideshow_title: tinyslideshow.title,
			tinyslideshow_speed: tinyslideshow.speed,
			tinyslideshow_gallery: $.toJSON(tinyslideshow.gallery),
			tinyslideshow_scrollspeed: tinyslideshow.scrollspeed,
			tinyslideshow_active: tinyslideshow.active,
			tinyslideshow_spacing: tinyslideshow.spacing,
			tinyslideshow_auto: tinyslideshow.auto,
			cookie: encodeURIComponent(document.cookie)
		});
		var data = exec_ajax(params);
		return data;
	}
	function remove(tinyslideshow_ids) {
		var params = ({
			action: 'tinyslideshow_delete',
			tinyslideshow_ids: tinyslideshow_ids,
			cookie: encodeURIComponent(document.cookie)
		});
		var data = exec_ajax(params);
		return data;
	}
	function list() {
		var params = ({
			action: 'tinyslideshow_list',
			cookie: encodeURIComponent(document.cookie)
		});
		tinyslideshows = null;
		var result = exec_ajax(params);
		if (result[0] == true 
		&& result[1] != '0') {
			var data = result[1];
			tinyslideshows = $.evalJSON(data.substring(0, data.length - 1));
		}
	}
	// ajax request
	function exec_ajax(params) {
		var result = [];
		$.ajax({
			async: false,
			type: 'POST',
			url: ajaxurl, 
			data: params,
			success: function(data) {
				result[0] = true;
				result[1] = data;
			},
			error: function(data) {
				result[0] = false;
				result[1] = data;
			}
		});
		return result;
	}
});