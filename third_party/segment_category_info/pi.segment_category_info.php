<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Segment Category Info Plugin
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Plugin
 * @author		Trevor Davis
 * @link		http://trevordavis.net/
 */

$plugin_info = array(
	'pi_name'		=> 'Segment Category Info',
	'pi_version'	=> '1.0',
	'pi_author'		=> 'Trevor Davis',
	'pi_author_url'	=> 'http://trevordavis.net/',
	'pi_description'=> 'Return category info by passing in the category_url_title and channel_short_name.',
	'pi_usage'		=> Segment_category_info::usage()
);


class Segment_category_info {

	public $return_data;
    
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		
		$segment = $this->EE->TMPL->fetch_param('segment', NULL);
		$channel = $this->EE->TMPL->fetch_param('channel', NULL);
		
		if($segment && $channel) {
			
			$query = $this->EE->db->select('cat_group')
			       ->from('channels')
				   ->where('channel_name', $channel)
			       ->get();
			
			foreach ($query->result() AS $row) {
				$query = $this->EE->db->select('cat_id, cat_name, cat_description, cat_image')
				       ->from('categories')
					   ->where('cat_url_title', $segment)
					   ->where('group_id', $row->cat_group)
				       ->get();
				
				foreach ($query->result() AS $row) {
					
					$tagdata = $this->EE->TMPL->tagdata;
					
					$variables[] = array(
										'category_id' => $row->cat_id,
										'category_name' => $row->cat_name,
										'category_description' => $row->cat_description,
										'category_image' => $row->cat_image
										);
					
					$this->return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);
					
				}
			}
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Plugin Usage
	 */
	public static function usage()
	{
		ob_start();
?>

	{exp:segment_category_info segment="{segment_3}" channel="blog"}
		{category_id}
		{category_name}
		{category_description}
		{category_image}
	{/exp:segment_category_info}
	
<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}


/* End of file pi.segment_category_info.php */
/* Location: /system/expressionengine/third_party/segment_category_info/pi.segment_category_info.php */