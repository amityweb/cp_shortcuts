<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Control Panel Shortcuts
 *
 * This extension works in conjunction with its accessory to add draggable sorting to additional areas of the control panel.
 *
 * @package	Control Panel Shortcuts
 * @author	 Laurence Cope <info@amitywebsolutions.co.uk>
 * @link		http://github.com/amityweb/cp_shortcuts
 * @copyright Copyright (c) 2012 Amity Web Solutions Ltd
 * @license	http://creativecommons.org/licenses/by-sa/3.0/	Attribution-Share Alike 3.0 Unported
 */

class Cp_shortcuts_ext {

	var $name				= 'Control Panel Shortcuts';
	var $description	  = 'Puts all your shortcuts into a single parent menu';
	var $version			= '1.1';
	var $settings_exist  = 'y';
	var $docs_url			  = '';
	var $settings		= array();
	
	function __construct($settings='')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}

	function activate_extension()
	{
		$this->settings = array(
		  'menu_name'	=> "My Shortcuts"
		);
	
		$hooks = array(
			'cp_js_end'
		);
		
		foreach($hooks as $hook)
		{
			$this->EE->db->insert('extensions', array(
				'class'	  => __CLASS__,
				'method'	=> $hook,
				'hook'		=> $hook,
				'settings'  => serialize($this->settings),
				'priority'  => 10,
				'version'	=> $this->version,
				'enabled'	=> 'y'
			));
		}
	}

	function update_extension($current = '')
	{
		 if ($current == '' OR $current == $this->version)
		 {
			  return FALSE;
		 }
	
		 if ($current < '1.0')
		 {
			  // Update to version 1.0
		 }
	
		 $this->EE->db->where('class', __CLASS__);
		 $this->EE->db->update(
			'extensions',
			array('version' => $this->version)
		 );
	}

	function disable_extension()
	{
		 $this->EE->db->where('class', __CLASS__);
		 $this->EE->db->delete('extensions');
	}
	
	function settings()
	{
		 $settings = array();
		 $settings['menu_name'] = array('i', '', "My Shortcuts");
		 return $settings;
	}
	
	function save_settings()
	{
		 if (empty($_POST))
		 {
			  show_error($this->EE->lang->line('unauthorized_access'));
		 }
	
		 unset($_POST['submit']);
	
		 $this->EE->lang->loadfile('cp_shortcuts');
	
		 $this->EE->db->where('class', __CLASS__);
		 $this->EE->db->update('extensions', array('settings' => serialize($_POST)));
	
		 $this->EE->session->set_flashdata(
			'message_success',
			$this->EE->lang->line('preferences_updated')
		 );
	}

	 
	function cp_js_end($data)
	{
		$quick_tabs_rows = $this->get_quick_tabs_rows();
		$menu_name = $this->settings["menu_name"];
		$js = 'var menu_name = "' . $menu_name . '";';
		$js .= 'var quick_tabs_array = new Array();';
		$i = 0;
		foreach($quick_tabs_rows AS $quick_tabs_row)
		{
			$js .= 'quick_tabs_array['.$i.'] = new Array("'. str_replace('|', '","', str_replace('&amp;','&',$quick_tabs_row)). '");';
			$i++;
		}
		
		$js .= '
				var html = "";
				var custom_quick_tab_items = "";
				// Iterate through menu
				$("#navigationTabs > li").each( function()
				{
					var menu_item = $(this);
					// Iterate through custom links
					$.each(quick_tabs_array, function( index, quick_tab_item)
					{
						// Remove from DOM if Link is in Custom Links
						if( menu_item.find("a").attr("href") == quick_tab_item[1] )
						{
							menu_item.remove();
							custom_quick_tab_items += "<li><a href=\""+quick_tab_item[1]+"\">"+quick_tab_item[0]+"</a></li>";
						}
					})
					
				})
				// Find last item in menu and add top level menu
				$("#navigationTabs > li").last().before("<li class=\"parent\"><a href=\"#\" class=\"first_level\">"+menu_name+"</a><ul>"+custom_quick_tab_items+"</ul></li>");
				';
				return $js;
	}
	
	function get_quick_tabs_rows()
	{
	  	$quick_tabs_rows = explode("\n", $this->EE->session->userdata["quick_tabs"]);
		return $quick_tabs_rows;
	}
	
	
}
// END CLASS

/* End of file ext.cp_shortcuts.php */
/* Location: ./system/expressionengine/third_party/draggable/ext.cp_shortcuts.php */