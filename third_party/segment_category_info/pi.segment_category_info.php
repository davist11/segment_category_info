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
	'pi_version'	=> '1.2',
	'pi_author'		=> 'Trevor Davis',
	'pi_author_url'	=> 'http://trevordavis.net/',
	'pi_description'=> 'Return category info by passing in the category_url_title and channel_short_name.',
	'pi_usage'		=> Segment_category_info::usage()
);


class Segment_category_info {

	public $return_data = '';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		$segment = $this->EE->TMPL->fetch_param('segment');
		$channel = $this->EE->TMPL->fetch_param('channel');

		# Require both segment= and channel=
		if ($segment && $channel) {
			# Support multiple, pipe-separated values.
			$segments = preg_split('/\|/', $segment, -1, PREG_SPLIT_NO_EMPTY);
			$channels = preg_split('/\|/', $channel, -1, PREG_SPLIT_NO_EMPTY);
			$variables = array();

			$this->EE->db->select('cat_id, cat_name, cat_description, cat_image')
					->from('channels chan')
					->where_in('channel_name', $channels)
					->where_in('cat_url_title', $segments)
					->join('categories cat', 'chan.cat_group = cat.group_id');
					//->limit(1);

			$sql = $this->EE->db->_compile_select();

			# multiple= support. This is an ugly hack, so lets make sure the user really wants it.
			if ($this->EE->TMPL->fetch_param('multiple', 'no') == 'yes') {
				$sql = preg_replace('/\s+ON\s+.*?\s+WHERE\s+/si', ' ON FIND_IN_SET(cat.group_id, REPLACE(chan.cat_group, "|", ",")) WHERE ', $sql);
			}

			$query = $this->EE->db->query($sql);
			$this->EE->db->_reset_select();

			foreach ($query->result() as $count => $row) {
				$tagdata = $this->EE->TMPL->tagdata;

				//Parse file paths
				$this->EE->load->library('typography');
				$this->EE->typography->parse_images = TRUE;
				$cat_image = $this->EE->typography->parse_file_paths($row->cat_image);

				$variables[] = array(
					'category_id' => $row->cat_id,
					'category_name' => $row->cat_name,
					'category_description' => $row->cat_description,
					'category_image' => $cat_image,
					'total' => $query->num_rows(),
					'count' => $count
				);
			}

			if ($query->num_rows()) {
				# We get automatic backspace= support with this.
				$this->return_data = $this->EE->TMPL->parse_variables($tagdata, $variables);
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

	{exp:segment_category_info segment="{segment_3}" channel="blog" multiple="y"}
		{category_id}
		{category_name}
		{category_description}
		{category_image}
	{/exp:segment_category_info}

	NOTE: multiple= enables matching when a channel has multiple category groups. Values are 'y' or 'n' (Default: 'n').

<?php
		return ob_get_clean();
	}
}


/* End of file pi.segment_category_info.php */
/* Location: /system/expressionengine/third_party/segment_category_info/pi.segment_category_info.php */
