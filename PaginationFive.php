<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author: Fred07
 * @package: CodeIgniter
 * @version: 1.2
 * 2012/11/28: 新增 $isPreview 控制是否顯示預覽連結(最前2頁與最後2頁)
 * 				$page_preview 預覽的頁數
 */

class PaginationFive
{
	public $base_url = '';
	public $para = '';				//$parameters behind 'page' param
	public $total_page = '';
	public $link_display = 10;
	public $cur_page = '';
	public $cur_page_pos = 1;		//set current page's position in the link queue.
	public $cur_page_mark = TRUE;
	public $prefix = "<b style='font-size:30px;'>";
	public $suffix = "</b>";
	public $first_and_last = TRUE;
	public $first_tag_name = "第一頁";
	public $last_tag_name = "最末頁";
	public $prev_next = TRUE;
	public $prev_tag_name = "<";
	public $next_tag_name = ">";
	public $debug_mode = FALSE;
	public $isPreview = FALSE;
	public $page_preview = 2;		//number of page to preview
	public $conjunction = '... ';
	
	private $space = "&nbsp;";
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	function __construct($params = array())
	{
		define("FIRST_PAGE", 1, TRUE);
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
		if($this->prev_next && $this->cur_page > FIRST_PAGE)	//print prev tag anchor
		{
			$url = $this->base_url."/".($this->cur_page-1).(($this->para?"/".$this->para:NULL));
			$link .= $this->_anchor_construct($url,$this->prev_tag_name);
		}
		
		//caculate first link number
		$first_link_n = FIRST_PAGE;
		if(($this->cur_page - $this->cur_page_pos) >= FIRST_PAGE)
		{
			$first_link_n = $this->cur_page - $this->cur_page_pos + 1;
		}
		if($first_link_n  + $this->link_display > $this->total_page)
		{
			$first_link_n = $this->total_page - $this->link_display + 1;
			//avoid the situation that link_display > total_page, some minus number may appear
			if($first_link_n < FIRST_PAGE)
			{
				$first_link_n = FIRST_PAGE;
			}
		}
		
		//page preview
		if($this->isPreview && $first_link_n > FIRST_PAGE + $this->page_preview)
		{
			for($i = FIRST_PAGE; $i < FIRST_PAGE+$this->page_preview; $i++)
			{
				$url = $this->base_url.'/'.$i.(($this->para)?'/'.$this->para:NULL);
				$link .= $this->_anchor_construct($url,$i);
			}
			$link .= $this->conjunction;
		}
		
		//print each link
		for($page_n = $first_link_n;$page_n <= $first_link_n + $this->link_display - 1;$page_n++)
		{
			$url = $this->base_url."/".$page_n.(($this->para)?"/".$this->para:NULL);
			if($page_n == $this->cur_page AND $this->cur_page_mark)
			{
				$link .= $this->_anchor_construct($url,$page_n, $this->prefix, $this->suffix);
			}else{
				$link .= $this->_anchor_construct($url,$page_n);
			}
			
			if($page_n == $this->total_page)	//break loop at last link
			{
				break;
			}
		}
		
		//page preview
		if($this->isPreview && $first_link_n + $this->link_display - 1 < $this->total_page - $this->page_preview)
		{
			$link .= $this->conjunction;
			for($i = $this->total_page-$this->page_preview+1; $i <= $this->total_page;$i++)
			{
				$url = $this->base_url.'/'.$i.(($this->para)?'/'.$this->para:NULL);
				$link .= $this->_anchor_construct($url,$i);
			}
			
		}
		
		if($this->prev_next && $this->cur_page < $this->total_page)	//print next tag anchor
		{
			$url = $this->base_url."/".($this->cur_page+1).(($this->para)?"/".$this->para:NULL);
			$link .= $this->_anchor_construct($url,$this->next_tag_name);
		}
		if($this->first_and_last)	//print last anchor
		{
			$url = $this->base_url."/".$this->total_page.(($this->para)?"/".$this->para:NULL);
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
		if($this->cur_page < FIRST_PAGE OR $this->cur_page > $this->total_page)
		{
			$this->_echo_error('Wrong parameters!! Check "cur_page" Please!');
			$error_occur = TRUE;
		}
		if($this->link_display == 0)
		{
			//$this->_echo_error('Wrong parameters!! "link_display" can not be 0!!');
			$error_occur = TRUE;
		}
		if($this->cur_page_pos > $this->link_display)
		{
			$this->_echo_error('Wrong parameters!! Check "cur_page_pos" and "link_display" please');
			$error_occur = TRUE;
		}
		if($error_occur)
		{
			//return $this;
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
		if($this->debug_mode)
		{
			echo "<b>" . $error . "</b>";
		}
	}
}
// END PaginationFive Class
/* End of file PaginationFive.php */