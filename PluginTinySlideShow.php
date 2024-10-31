<?php
/*
Plugin Name: PluginTinySlideShow
Plugin URI: http://www.dimgoto.com/open-source/wordpress/plugins/plugin-tinyslideshow
Description: Simple Images SlideShow, <a href="http://www.leigeber.com/" target="_blank">Tiny SlideShow (Michael Leigeber, Web Designer)</a> implementation.
Version: 1.0.0
Author: Dimitri GOY
Author URI: http://www.dimgoto.com
*/

/*  Copyright 2009  DimGoTo  (email : wordpress@dimgoto.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Classe PluginTinySlideShow.
 *
 * This Plugin is based on TinySlideShow powered by Michael Leigeber, implementation to WordPress.
 *
 * @package Plugins
 * @subpackage TinySlideShow
 * @version 1.0.0
 * @author Dimitri GOY
 * @copyright 2009 - DimGoTo
 * @link http://www.dimgoto.com/
 * @link http://www.leigeber.com/
 */
class PluginTinySlideShow {

	protected $_pluginname = null;
	protected $_plugindir = null;
	protected $_langdir = null;
	protected $_jsdir = null;
	protected $_cssdir = null;
	protected $_pluginfile = null;
	protected $_galleriesdir = null;
	protected $_imgdir = null;
	protected $_docdir = null;

	function __construct() {

		add_action('init', array($this, 'init'));
	}

	public function init() {

		$this->_pluginname = get_class($this);
		$this->_pluginfile = plugin_basename(__FILE__);
		$this->_plugindir = '/' . PLUGINDIR . '/' . str_replace('\\', '/', dirname(plugin_basename(__FILE__)));
		$this->_langdir = $this->_plugindir . '/lang';
		$this->_jsdir = $GLOBALS['wpbase'] . $this->_plugindir . '/js';
		$this->_cssdir = get_bloginfo('wpurl') . $GLOBALS['wpbase'] . $this->_plugindir . '/css';
		$this->_galleriesdir = '.' . $GLOBALS['wpbase'] . $this->_plugindir . '/galleries';
		$this->_imgdir = '.' . $GLOBALS['wpbase'] . $this->_plugindir . '/img';
		$this->_docdir = '..' . $this->_plugindir . '/doc';

		load_plugin_textdomain($this->_pluginname, null, $this->_langdir);

		if (is_admin()) {

			register_activation_hook($this->_pluginfile, array($this, 'activate'));
			register_deactivation_hook($this->_pluginfile, array($this, 'deactivate'));

			if (function_exists('register_uninstall_hook')) {
	    		register_uninstall_hook($this->_pluginfile, array($this, 'uninstall'));
			}

			add_action('wp_ajax_tinyslideshow_add', array($this, 'ajax_add'));
			add_action('wp_ajax_tinyslideshow_update', array($this, 'ajax_update'));
			add_action('wp_ajax_tinyslideshow_delete', array($this, 'ajax_delete'));
			add_action('wp_ajax_tinyslideshow_list', array($this, 'ajax_list'));

			if (isset($_GET)
			&& isset($_GET['page'])
			&& $_GET['page'] == $this->_pluginname) {

				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-resizable');
				wp_enqueue_script('jquery-ui-dialog');

				wp_enqueue_script(strtolower($this->_pluginname) . '-jquery-json', $this->_jsdir . '/jquery.json-2.2.js', array(), false);
				wp_enqueue_script(strtolower($this->_pluginname) . '-admin', $this->_jsdir . '/plugintinyslideshow.js', array(), false);

				wp_enqueue_style(strtolower($this->_pluginname) . '-jquery-ui', $this->_cssdir . '/jquery-ui-1.0.0.css', array(), false, 'screen');
				wp_enqueue_style(strtolower($this->_pluginname) . '-style', $this->_cssdir . '/style.css', array(), false, 'screen');

				add_action('admin_head', array($this, 'head'));
				add_filter('contextual_help', array($this, 'help'));
			}

			add_action('admin_menu', array($this, 'menu'));

		} else {

			wp_enqueue_script('jquery');
			wp_enqueue_script(strtolower($this->_pluginname) . '-tinyslideshow', $this->_jsdir . '/tinyslideshow.js', array(), false);
			wp_enqueue_style(strtolower($this->_pluginname) . '-tinyslideshow', $this->_cssdir . '/tinyslideshow.css', array(), false, 'screen');

			add_action('wp_head', array($this, 'head'));

			add_shortcode(strtolower($this->_pluginname), array($this, 'shortcode'));
		}

	}

	public function head() {

		$html = '<script type="text/javascript">';

		if (is_admin()
		&& isset($_GET)
		&& isset($_GET['page'])
		&& $_GET['page'] == $this->_pluginname) {
			$html .= 'var auto_true = "' . __('Oui', $this->_pluginname) . '";';
			$html .= 'var auto_false = "' . __('Non', $this->_pluginname) . '";';
			$html .= 'var label_ok = "' . __('Ok', $this->_pluginname) . '";';
			$html .= 'var label_cancel = "' . __('Annuler', $this->_pluginname) . '";';
			$html .= 'var label_add = "' . __('Ajouter', $this->_pluginname) . '";';
			$html .= 'var label_update = "' . __('Modifier', $this->_pluginname) . '";';
			$html .= 'var label_delete = "' . __('Supprimer', $this->_pluginname) . '";';
			$html .= 'var label_save = "' . __('Enregistrer', $this->_pluginname) . '";';
			$html .= 'var message_no_tinyslideshow = "' . __('Aucun TinySlideShow!', $this->_pluginname) . '";';
			$html .= 'var message_select_least = "' . __('Sélectionnez au moins un élément!', $this->_pluginname) . '";';
			$html .= 'var message_select_single = "' . __('Sélectionnez un seul élément!', $this->_pluginname) . '";';
			$html .= 'var message_gallery_required = "' . __('Sélectionnez une galerie!', $this->_pluginname) . '";';
		}
		$html .= '</script>';

		echo $html;
	}

	public function menu() {

		add_submenu_page('plugins.php',
				__('TinySlideShow', $this->__pluginname),
				__('TinySlideShow', $this->__pluginname),
				'activate_plugins',
				$this->_pluginname,
				array($this, 'control'));
	}

	public function activate() {

		$tinysliders = get_option(strtolower($this->_pluginname));
		if (empty($tinysliders)) {
			add_option(strtolower($this->_pluginname), '');
		}
	}

	public function deactivate() {

	}

	public function uninstall() {

		delete_option(strtolower($this->_pluginname));
	}

	public function help($context = '') {

		$help = '';
		if (file_exists($this->_docdir . '/' . $this->_pluginname . '-' . WPLANG . '.html')) {
			$help .= file_get_contents($this->_docdir . '/' . $this->_pluginname . '-' . WPLANG . '.html');
		} else if (file_exists($this->_docdir . '/' . $this->_pluginname . '-fr_FR.html')) {
			$help .= file_get_contents($this->_docdir . '/' . $this->_pluginname . '-fr_FR.html');
		}
		$help .= $context;

		return $help;
	}

	public function control() {

		$html = '';

		$galleries = array();
		$dir = realpath('.' . $this->_galleriesdir);
		if (is_dir($dir)) {
    		if ($dh = opendir($dir)) {
        		while (($file = readdir($dh)) !== false) {
        			if (is_dir($dir . '/' . $file . '/')
        			&& $file != '.'
        			&& $file != '..'
        			&& $file != '.svn') {
        				$gallery = array(
        									'gallery'	=> $file,
        									'files'		=> null);
        				array_push($galleries, $gallery);
        			}
        		}
        		closedir($dh);
    			$tmpgalleries = array();
				foreach ($galleries as $gallery) {
					$files = array();
					$dir .= '/' . $gallery['gallery'];
					if ($dh = opendir($dir)) {
        				while (($file = readdir($dh)) !== false) {
        					if (is_file($dir . '/' . $file . '/')
        					&& $file != '.'
        					&& $file != '..'
        					&& $file != '.svn') {
        						array_push($files, $file);
        					}
        				}
        				closedir($dh);
					}
					$tmpgallery = array(
											'gallery'	=> $gallery['gallery'],
											'files'		=> $files);
					array_push($tmpgalleries, $tmpgallery);
				}
				$galleries = $tmpgalleries;
				sort($galleries);
    		}
		}

		// begin wrap > title
		$html .= '<div class="wrap" id="' . strtolower($this->_pluginname) . '">';
		$html .= '<div id="icon-options-general" class="icon32"><br /></div>';
		$html .= '<h2>' . __('Configuration TinySlideShow', $this->pluginname) . '</h2>';
		$html .= '<br/>';

		// list tinyslideshow
		$html .= '<table class="widefat fixed" cellspacing="0" id="' . strtolower($this->_pluginname) . '-tinysliders">';
		$html .= '<thead>';
		$html .= '<tr class="thead">';
		$html .= '<th id="hcb" class="manage-column column-cb check-column" style="" scope="col">';
		$html .= '<input type="checkbox" name="tinyslideshow-checkbox[]" id="' . strtolower($this->_pluginname) . '-cb0"/>';
		$html .= '</th>';
		$html .= '<th id="htitle" class="manage-column" style="" scope="col">' . __('Titre', $this->_pluginname) . '</th>';
		$html .= '<th id="hspeed" class="manage-column" style="" scope="col">' . __('Vitesse', $this->_pluginname) . '</th>';
		$html .= '<th id="hgallery" class="manage-column" style="" scope="col">' . __('Galerie', $this->_pluginname) . '</th>';
		$html .= '<th id="hscrollspeed" class="manage-column" style="" scope="col">' . __('Vit. Défilement', $this->_pluginname) . '</th>';
		$html .= '<th id="hauto" class="manage-column" style="" scope="col">' . __('Auto', $this->_pluginname) . '</th>';
		$html .= '<th id="hactive" class="manage-column" style="" scope="col">' . __('Active', $this->_pluginname) . '</th>';
		$html .= '<th id="hspacing" class="manage-column" style="" scope="col">' . __('Espacement', $this->_pluginname) . '</th>';
		$html .= '<th id="hshortcode" class="manage-column" style="" scope="col">' . __('ShortCode', $this->_pluginname) . '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tfoot>';
		$html .= '<tr class="thead">';
		$html .= '<th id="fcb" class="manage-column column-cb check-column" style="" scope="col">';
		$html .= '<input type="checkbox" name="tinyslideshow-checkbox[]" id="' . strtolower($this->_pluginname) . '-cb1"/>';
		$html .= '</th>';
		$html .= '<th id="ftitle" class="manage-column" style="" scope="col">' . __('Titre', $this->_pluginname) . '</th>';
		$html .= '<th id="fspeed" class="manage-column" style="" scope="col">' . __('Vitesse', $this->_pluginname) . '</th>';
		$html .= '<th id="fgallery" class="manage-column" style="" scope="col">' . __('Galerie', $this->_pluginname) . '</th>';
		$html .= '<th id="fscrollspeed" class="manage-column" style="" scope="col">' . __('Vit. Défilement', $this->_pluginname) . '</th>';
		$html .= '<th id="fauto" class="manage-column" style="" scope="col">' . __('Auto', $this->_pluginname) . '</th>';
		$html .= '<th id="factive" class="manage-column" style="" scope="col">' . __('Active', $this->_pluginname) . '</th>';
		$html .= '<th id="fspacing" class="manage-column" style="" scope="col">' . __('Espacement', $this->_pluginname) . '</th>';
		$html .= '<th id="fshortcode" class="manage-column" style="" scope="col">' . __('ShortCode', $this->_pluginname) . '</th>';
		$html .= '</tr>';
		$html .= '</foot>';
		$html .= '<tbody id="' . strtolower($this->_pluginname) . '-list">';
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '<p class="description">' . __('Pour modifier l\'apparence du TinySlideShow veuillez utiliser l\'éditeur d\'Extensions et modifier le style (tinyslideshow.css, uniquement!)', $this->_pluginname) . '</p>';

		// buttons
		$html .= '<p class="submit">';
		$html .= '<input class="button-secondary" type="button" value="' . __('Ajouter', $this->_pluginname) . '" id="' . strtolower($this->_pluginname) . '-add" name="' . strtolower($this->_pluginname) . '-add"/>';
		$html .= '<input class="button-secondary" type="button" value="' . __('Supprimer', $this->_pluginname) . '" id="' . strtolower($this->_pluginname) . '-delete" name="' . strtolower($this->_pluginname) . '-delete"/>';
		$html .= '<input class="button-secondary" type="button" value="' . __('Modifier', $this->_pluginname) . '" id="' . strtolower($this->_pluginname) . '-update" name="' . strtolower($this->_pluginname) . '-update"/>';
		$html .= '</p>';

		$html .= '</div>'; // end wrap

		// dialog notification:
		$html .= '<div id="' . strtolower($this->_pluginname) . '-dialog-notification" title="' . __('Notification', $this->_pluginname) . '">';
		$html .= '<div class="ui-state-highlight ui-corner-all">';
		$html .= '<p class="ui-icon ui-icon-info" style="float: left; margin: 1em;"/>';
		$html .= '<p id="' . strtolower($this->_pluginname) . '-notification-message"/>';
		$html .= '</div>';
		$html .= '</div>';

		// dialog error:
		$html .= '<div id="' . strtolower($this->_pluginname) . '-dialog-error" title="' . __('Erreur', $this->_pluginname) . '">';
		$html .= '<div  class="ui-state-error ui-corner-all">';
		$html .= '<p class="ui-icon ui-icon-alert" style="float: left; margin: 1em;"/>';
		$html .= '<p id="' . strtolower($this->_pluginname) . '-error-message"/>';
		$html .= '</div>';
		$html .= '</div>';

		// dialog add tinyslideshow:
		$html .= '<div id="' . strtolower($this->_pluginname) . '-dialog-add" title="' . __('Ajouter TinySlideShow', $this->_pluginname) . '">';
		$html .= '<p class="description"><span class="required">*</span> : ' . __('Champ obligatoire', $this->_pluginname) . '</p>';
		$html .= '<div id="' . strtolower($this->_pluginname) . '-add-error" class="ui-state-error ui-corner-all" style="margin: 2em; display: none;">';
		$html .= '<p class="ui-icon ui-icon-alert" style="float: left; margin: 1em;"/>';
		$html .= '<p id="' . strtolower($this->_pluginname) . '-add-error-message"/>';
		$html .= '</div>';
		$html .= '<form>';
		$html .= '<table class="form-table">';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-title">' . __('Titre', $this->_pluginname) . '</label></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-add-title" name="' . strtolower($this->_pluginname) . '-add-title"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-speed">' . __('Vitesse', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-désactivé, 1-rapide, ... 10-lent)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-add-speed" name="' . strtolower($this->_pluginname) . '-add-speed" value="5"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-scrollspeed">' . __('Vitesse défilement', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-désactivé, 1-rapide, ... 10-lent)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-add-scrollspeed" name="' . strtolower($this->_pluginname) . '-add-scrollspeed" value="4"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-auto">' . __('Auto', $this->_pluginname) . '</label></th>';
		$html .= '<td><input type="checkbox" id="' . strtolower($this->_pluginname) . '-add-auto" name="' . strtolower($this->_pluginname) . '-add-auto" checked="checked"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-spacing">' . __('Espacement', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-10, petit à grand)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-add-spacing" name="' . strtolower($this->_pluginname) . '-add-spacing" value="5"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-active">' . __('Active', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(couleur hexa, ex. #fff)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-add-active" name="' . strtolower($this->_pluginname) . '-add-active" value="#fff"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-add-gallery">' . __('Galerie images', $this->_pluginname) . '</label><span class="required">*</span></th>';
		$html .= '<td><select type="text" id="' . strtolower($this->_pluginname) . '-add-gallery" name="' . strtolower($this->_pluginname) . '-add-gallery">';
		$html .= '<option value="">' . __('sélectionner...', $this->_pluginname) . '</option>';
		foreach ($galleries as $gallery) {
			$html .= '<option value="' . $gallery['gallery'] . '">' . $gallery['gallery'] . '</option>';
		}
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td colspan="2" id="add-files">';
		foreach ($galleries as $gallery) {
			$html .= '<div id="add-' . $gallery['gallery'] . '" style="display:none;">';
			$html .= '<table>';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th><strong>' . __('Image', $this->_pluginname) . '</strong></th>';
			$html .= '<th><strong>' . __('Titre', $this->_pluginname) . '</strong></th>';
			$html .= '<th><strong>' . __('Description', $this->_pluginname) . '</strong></th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';
			foreach ($gallery['files'] as $file) {
				$pi = pathinfo($file);
				$filename = $pi['filename'];
				$html .= '<tr id="add-' . $filename . '">';
				$html .= '<td id="add-' . $filename . '-file">' . $file . '</td>';
				$html .= '<td><input type="text" id="add-' . $filename . '-title" /></td>';
				$html .= '<td><input type="text" id="add-' . $filename . '-description" /></td>';
				$html .= '</tr>';
			}
			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</div>';
		}
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</form>';
		$html .= '</div>';

		// dialog update tinyslider:
		$html .= '<div id="' . strtolower($this->_pluginname) . '-dialog-update" title="' . __('Modifier TinySlideShow', $this->_pluginname) . '">';
		$html .= '<p class="description"><span class="required">*</span> : ' . __('Champ obligatoire', $this->_pluginname) . '</p>';
		$html .= '<div id="' . strtolower($this->_pluginname) . '-update-error" class="ui-state-error ui-corner-all" style="margin: 2em; display: none;">';
		$html .= '<p class="ui-icon ui-icon-alert" style="float: left; margin: 1em;"/>';
		$html .= '<p id="' . strtolower($this->_pluginname) . '-update-error-message"/>';
		$html .= '</div>';
		$html .= '<form>';
		$html .= '<table class="form-table">';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-title">' . __('Titre', $this->_pluginname) . '</label></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-update-title" name="' . strtolower($this->_pluginname) . '-update-title"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-speed">' . __('Vitesse', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-désactivé, 1-rapide, ... 10-lent)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-update-speed" name="' . strtolower($this->_pluginname) . '-update-speed" value="5"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-scrollspeed">' . __('Vitesse défilement', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-désactivé, 1-rapide, ... 10-lent)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-update-scrollspeed" name="' . strtolower($this->_pluginname) . '-update-scrollspeed" value="4"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-auto">' . __('Auto', $this->_pluginname) . '</label></th>';
		$html .= '<td><input type="checkbox" id="' . strtolower($this->_pluginname) . '-update-auto" name="' . strtolower($this->_pluginname) . '-update-auto" checked="checked"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-spacing">' . __('Espacement', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(0-10, petit à grand)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-update-spacing" name="' . strtolower($this->_pluginname) . '-update-spacing" value="5"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-active">' . __('Active', $this->_pluginname) . '</label><span class="required">*</span>';
		$html .= '<br/><span class="description">' . __('(couleur hexa, ex. #fff)', $this->_pluginname) . '</span></th>';
		$html .= '<td><input type="text" id="' . strtolower($this->_pluginname) . '-update-active" name="' . strtolower($this->_pluginname) . '-update-active" value="#fff"/></td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th scope="row"><label for="' . strtolower($this->_pluginname) . '-update-gallery">' . __('Galerie images', $this->_pluginname) . '</label><span class="required">*</span></th>';
		$html .= '<td><select type="text" id="' . strtolower($this->_pluginname) . '-update-gallery" name="' . strtolower($this->_pluginname) . '-update-gallery">';
		$html .= '<option value="">' . __('sélectionner...', $this->_pluginname) . '</option>';
		foreach ($galleries as $gallery) {
			$html .= '<option value="' . $gallery['gallery'] . '">' . $gallery['gallery'] . '</option>';
		}
		$html .= '</select>';
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<td colspan="2" id="update-files">';
		foreach ($galleries as $gallery) {
			$html .= '<div id="update-' . $gallery['gallery'] . '" style="display:none;">';
			$html .= '<table>';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th><strong>' . __('Image', $this->_pluginname) . '</strong></th>';
			$html .= '<th><strong>' . __('Titre', $this->_pluginname) . '</strong></th>';
			$html .= '<th><strong>' . __('Description', $this->_pluginname) . '</strong></th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';
			foreach ($gallery['files'] as $file) {
				$pi = pathinfo($file);
				$filename = $pi['filename'];
				$html .= '<tr id="update-' . $filename . '">';
				$html .= '<td id="update-' . $filename . '-file">' . $file . '</td>';
				$html .= '<td><input type="text" id="update-' . $filename . '-title" /></td>';
				$html .= '<td><input type="text" id="update-' . $filename . '-description" /></td>';
				$html .= '</tr>';
			}
			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</div>';
		}
		$html .= '</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '</form>';
		$html .= '</div>';

		echo $html;
	}

	public function shortcode($attributes, $content = '') {
		extract(shortcode_atts(array(
												'id' => ''
											),
											$attributes));

		if (isset($id)) {
			$thetiny = null;
			$tinyslideshows = get_option(strtolower($this->_pluginname));
			foreach ($tinyslideshows as $tinyslideshow) {
				if ($tinyslideshow['id'] == $id) {
					$thetiny = $tinyslideshow;
					break;
				}
			}

			if (!is_null($thetiny)) {

				$html = '';

				// title
				if (!empty($thetiny['title'])) {
					$html .= '<h2>' . $thetiny['title'] . '</h2>';
				}

				$gallery = $thetiny['gallery'][0];
				$files = $gallery['files'];
				// slideshow
				$html .= '<ul id="tiny-slideshow">';
				foreach ($files as $file) {
					$html .= '<li>';
					$html .= '<h3>' . $file['title'] . '</h3>';
					$html .= '<span>' . get_bloginfo('wpurl') . '/' . substr($this->_galleriesdir, 2) . '/' . $gallery['gallery'] . '/' . $file['file'] . '</span>';
					$html .= '<p>' . $file['description'] . '</p>';
					$html .= '<a href="#"><img src="' . get_bloginfo('wpurl') . '/' . substr($this->_galleriesdir, 2) . '/' . $gallery['gallery'] . '/thumbnails/' .$file['file'] . '" alt="' . $file['title'] . '"/></a>';
					$html .= '</li>';
				}
				$html .= '</ul>';

				// wrapper
				$html .=	 '<div id="tiny-slideshow-wrapper">';
				$html .= '<div id="fullsize">';
				$html .= '<div id="imgprev" class="imgnav" title="Previous Image"></div>';
				$html .= '<div id="imglink"></div>';
				$html .= '<div id="imgnext" class="imgnav" title="Next Image"></div>';
				$html .= '<div id="image"></div>';
				$html .= '<div id="information">';
				$html .= '<h3></h3>';
				$html .= '<p></p>';
				$html .= '</div>';
				$html .= '</div>';

				// thumbnails
				$html .= '<div id="thumbnails">';
				$html .= '<div id="slideleft" title="Slide Left"></div>';
				$html .= '<div id="slidearea">';
				$html .= '<div id="slider"></div>';
				$html .= '</div>';
				$html .= '<div id="slideright" title="Slide Right"></div>';
				$html .= '</div>';
				$html .= '</div>';

				// script
				$html .= '<script type="text/javascript">';
				$html .= '$(\'tiny-slideshow\').style.display=\'none\';';
				$html .= '$(\'tiny-slideshow-wrapper\').style.display=\'block\';';
				$html .= 'var slideshow=new TINYSlideShow.slideshow(\'slideshow\');';
				$html .= 'window.onload=function(){';
				$html .= 'slideshow.auto=' . $thetiny['auto'] . ';';
				$html .= 'slideshow.speed=' . $thetiny['speed'] . ';';
				$html .= 'slideshow.link=\'linkhover\';';
				$html .= 'slideshow.info=\'information\';';
				$html .= 'slideshow.thumbs=\'slider\';';
				$html .= 'slideshow.left=\'slideleft\';';
				$html .= 'slideshow.right=\'slideright\';';
				$html .= 'slideshow.scrollSpeed=' . $thetiny['scrollspeed'] . ';';
				$html .= 'slideshow.spacing=' . $thetiny['spacing'] . ';';
				$html .= 'slideshow.active=\'' . $thetiny['active'] . '\';';
				$html .= 'slideshow.init(\'tiny-slideshow\', \'image\', \'imgprev\', \'imgnext\', \'imglink\');';
				$html .= '}';
				$html .= '</script>';

				$content .= $html;
			}
		}

		return $content;
	}

	public function ajax_add() {
		if (!isset($_POST)) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètres TinySlideShow manquants!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_title'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Titre manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_speed'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Vitesse manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_gallery'])
		|| empty($_POST['tinyslideshow_gallery'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Galerie manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_scrollspeed'])
		|| empty($_POST['tinyslideshow_scrollspeed'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Vit. Défilement manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_spacing'])
		|| empty($_POST['tinyslideshow_spacing'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Espacement manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_auto'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Auto manquant!', $this->_pluginname));
		} else {
			$tinyslideshows = get_option(strtolower($this->_pluginname));
			if (empty($tinyslideshows)) {
				$tinyslideshows = array();
			}
			$tinyslideshow = array(
										'id'					=> time(),
										'title'				=> $_POST['tinyslideshow_title'],
										'speed'			=> (empty($_POST['tinyslideshow_speed'])) ? 0 : $_POST['tinyslideshow_speed'],
										'gallery'			=> json_decode(stripslashes($_POST['tinyslideshow_gallery']), true),
										'scrollspeed'		=> (empty($_POST['tinyslideshow_scrollspeed'])) ? 0 : $_POST['tinyslideshow_scrollspeed'],
										'auto'				=> (isset($_POST['tinyslideshow_auto']) || $_POST['tinyslideshow'] == 'true') ? 'true' : 'false',
										'spacing'			=> (empty($_POST['tinyslideshow_spacing'])) ? 0 : $_POST['tinyslideshow_spacing'],
										'active'			=> $_POST['tinyslideshow_active']
										);
			if (!in_array($tinyslideshows['gallery'], $tinyslideshows)) {
				array_push($tinyslideshows, $tinyslideshow);
				update_option(strtolower($this->_pluginname), $tinyslideshows);
				echo sprintf(__('TinySlideShow ID: %s ajouté!', $this->_pluginname), $tinyslideshow['id']);
			} else {
				header("Status: 400 Bad Request", true, 400);
				die(__('TinySlideShow existe déjà pour cette galerie!', $this->_pluginname));
			}
		}
	}

	public function ajax_update() {
	if (!isset($_POST)) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètres TinySlider manquants!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_id'])
		|| empty($_POST['tinyslideshow_id'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre ID manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_title'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Titre manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_speed'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Vitesse manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_gallery'])
		|| empty($_POST['tinyslideshow_gallery'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Galerie manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_scrollspeed'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Vit. Défilement manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_spacing'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Espacement manquant!', $this->_pluginname));
		} else if (!isset($_POST['tinyslideshow_auto'])) {
			header("Status: 400 Bad Request", true, 400);
			die(__('Paramètre Auto manquant!', $this->_pluginname));
		} else {
			$tinyslideshows = get_option(strtolower($this->_pluginname));
			if (!empty($tinyslideshows)) {
				$thetiny = array(
									'id'					=> $_POST['tinyslideshow_id'],
									'title'				=> $_POST['tinyslideshow_title'],
									'speed'			=> (empty($_POST['tinyslideshow_speed'])) ? 0 : $_POST['tinyslideshow_speed'],
									'gallery'			=> json_decode(stripslashes($_POST['tinyslideshow_gallery']), true),
									'scrollspeed'		=> (empty($_POST['tinyslideshow_scrollspeed'])) ? 0 : $_POST['tinyslideshow_scrollspeed'],
									'auto'				=> (isset($_POST['tinyslideshow_auto']) || $_POST['tinyslideshow'] == 'true') ? 'true' : 'false',
									'spacing'			=> (empty($_POST['tinyslideshow_spacing'])) ? 0 : $_POST['tinyslideshow_spacing'],
									'active'			=> $_POST['tinyslideshow_active']
							);
				$replacetinyslideshows = array();
				$exists = false;
				foreach ($tinyslideshows as $tinyslideshow) {
					if ($thetiny['id'] != $tinyslideshow['id']) {
						array_push($replacetinyslideshows, $tinyslideshow);
					} else {
						array_push($replacetinyslideshows, $thetiny);
						$exists = true;
					}
				}
				if ($exists == true) {
					$tinyslideshows = $replacetinyslideshows;
					update_option(strtolower($this->_pluginname), $tinyslideshows);
					echo sprintf(__('TinySlideShow ID: %s modifié!', $this->_pluginname), $thetiny['id']);
				} else {
					header("Status: 400 Bad Request", true, 400);
					die(sprintf(__('TinySlideShow ID: %s n\'existe pas!', $this->_pluginname), $thetiny['id']));
				}
			} else {
				header("Status: 400 Bad Request", true, 400);
				die(__('Aucun TinySlideShow configuré! Veuillez l\'ajouter', $this->_pluginname));
			}
		}
	}

	public function ajax_delete() {

		if (isset($_POST)
		&& isset($_POST['tinyslideshow_ids'])) {
			$tinyslideshow_ids = explode(',', $_POST['tinyslideshow_ids']);
			$tinyslideshows = get_option(strtolower($this->_pluginname));
			if (!empty($tinyslideshows)) {
				$tmptinyslideshows = array();
				foreach ($tinyslideshows as $tinyslideshow) {
					$ondelete = false;
					foreach ($tinyslideshow_ids as $tinyslideshow_id) {
						if ($tinyslideshow['id'] == $tinyslideshow_id) {
							$ondelete = true;
						}
					}
					if ($ondelete == false) {
						array_push($tmptinyslideshows, $tinyslideshow);
					}
				}
				update_option(strtolower($this->_pluginname), $tmptinyslideshows);
				echo sprintf(__('TinySlideShow ID(s): %s supprimé(s)!', $this->_pluginname), implode(',', $tinyslideshow_ids));
			} else {
				header("Status: 400 Bad Request", true, 400);
				die(__('ID TinySlideShow n\'existe pas!', $this->_pluginname));
			}
		} else {
			header("Status: 400 Bad Request", true, 400);
			die(__('ID TinySlideShow manquant ou vide!', $this->_pluginname));
		}
	}

	public function ajax_list() {

		$tinysliders = get_option(strtolower($this->_pluginname));
		if (!empty($tinysliders)) {
			usort($tinysliders, array($this, 'sort'));
			$data = json_encode($tinysliders);
		} else {
			$data = '';
		}
		echo $data;
	}

	private function sort($arr1, $arr2) {
		$ret = strnatcmp($arr1['title'], $arr2['title']);
		if (!$ret) {
			$ret = strnatcmp($arr1['gallery'], $arr2['gallery']);
		}
		return $ret;
	}
}
new PluginTinySlideShow();
?>