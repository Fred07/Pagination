<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author: Fred07
 * @package: CodeIgniter
 * @version: 1.0
 * 2012/11/18
 */

class PaginationFive
{	
	var $base_url = '';
	var $para = '';				//$parameters behind 'page' param
	var $total_page = '';
	var $link_display = 10;
	var $cur_page = '';
	var $cur_page_pos = 1;		//set current page's position in the link queue.
	var $cur_page_mark = TRUE;
	var $mark_beg = "<b style='font-size:30px;'>";
	var $mark_end = "</b>";
	var $first_and_last = TRUE;
	var $first_tag_name = "第一頁";
	var $last_tag_name = "最末頁";
	var $prev_next = TRUE;
	var $prev_tag_name = "上一頁";
	var $next_tag_name = "下一頁";
	var $space = "&nbsp;";
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	function __construct($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}
	}
	
	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}
	
	/**
	 * Create pagination link
	 * 
	 * @access	public
	 * @return	$link String
	 */
	function create_link()
	{
		$link = "";
		
		//check current page is valid num
		$this->_prevent_error();
		
		if($this->first_and_last)	//print first anchor
		{
			$url = $this->base_url."/1".(($this->para?"/".$this->para:NULL));
			$link .= $this->_anchor_construct($url,$this->first_tag_name);
		}
		if($this->prev_next && $this->cur_page > 1)	//print prev tag anchor
		{
			$url = $this->base_url."/".($this->cur_page-1).(($this->para?"/".$this->para:NULL));
			$link .= $this->_anchor_construct($url,$this->prev_tag_name);
		}
		
		//caculate first link number
		$first_link_n = 1;
		if(($this->cur_page - $this->cur_page_pos) >= 1)
		{
			$first_link_n = $this->cur_page - $this->cur_page_pos + 1;
		}
		if($first_link_n  + $this->link_display > $this->total_page)
		{
			$first_link_n = $this->total_page - $this->link_display + 1;
			//avoid the situation that link_display > total_page, some minus number may appear
			if($first_link_n < 1)
			{
				$first_link_n = 1;
			}
		}
		
		//print each link
		for($page_n = $first_link_n;$page_n <= $first_link_n + $this->link_display - 1;$page_n++)
		{
			$url = $this->base_url."/".$page_n.(($this->para?"/".$this->para:NULL));
			if($page_n == $this->cur_page AND $this->cur_page_mark)
			{
				$link .= $this->_anchor_construct($url,$page_n, $this->mark_beg, $this->mark_end);
			}else{
				$link .= $this->_anchor_construct($url,$page_n);
			}
			
			if($page_n == $this->total_page)	//break loop at last link
			{
				break;
			}
		}
		
		if($this->prev_next && $this->cur_page < $this->total_page)	//print next tag anchor
		{
			$url = $this->base_url."/".($this->cur_page+1).(($this->para?"/".$this->para:NULL));
			$link .= $this->_anchor_construct($url,$this->next_tag_name);
		}
		if($this->first_and_last)	//print last anchor
		{
			$url = $this->base_url."/".$this->total_page.(($this->para?"/".$this->para:NULL));
			$link .= $this->_anchor_construct($url,$this->last_tag_name);
		}
		return $link;
	}

	/**
	 * constrcut anchor
	 * 
	 * @access	private
	 * @param	$url String: url of link
	 * @param	$text String: text of anchor
	 * @param	$dom_beg, $dom_end String: html dom to cladding anchor
	 * @return	$anchor String
	 */
	private function _anchor_construct($url, $text, $dom_beg = NULL, $dom_end = NULL)
	{
		$anchor = "<a";
		$anchor .= " href='" . $url . "'";
		$anchor .= ">";
		$anchor .= $dom_beg;
		$anchor .= $text;
		$anchor .= $dom_end;
		$anchor .= "</a>" . $this->space;
		return $anchor;
	}

	/**
	 * prevent invalid situation and params
	 * 
	 * @access	private
	 * @return	void
	 */
	private function _prevent_error()
	{
		$error_occur = FALSE;
		if($this->cur_page < 1 OR $this->cur_page > $this->total_page OR $this->link_display == 0)
		{
			$this->_echo_error('Wrong parameters!! Check "cur_page" Please!');
			$error_occur = TRUE;
		}
		if($this->link_display == 0)
		{
			$this->_echo_error('Wrong parameters!! "link_display" can not be 0!!');
			$error_occur = TRUE;
		}
		if($this->cur_page_pos > $this->link_display)
		{
			$this->_echo_error('Wrong parameters!! Check "cur_page_pos" and "link_display" please');
			$error_occur = TRUE;
		}
		if($error_occur)
		{
			die();
		}
	}
	
	/**
	 * echo Error message
	 * 
	 * @access	private
	 * @param	$error String: error message
	 * @return	void
	 */
	private function _echo_error($error)
	{
		echo "<b>" . $error . "</b>";
	}
}
// END PaginationFive Class
/* End of file PaginationFive.php */