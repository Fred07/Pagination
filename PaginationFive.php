<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * @author: Five
 * @package: CodeIgniter
 * @version: 1.5
 * 2013/06/13:	增加 public $cur_page_anchor, $cur_page_prefix, $cur_page_suffix,並調整頁籤 html結構
 * 				增加 public $mode, private $page_key
 * 				增加 setUrlParameter() function
 * 				增加 _url_builder() function
 * 				修改 public $prev_tag_name, $next_tag_name default value
 * 				修改 public $cur_page default value: 1, public $total_page default value:0
 * 				修正 程式中的判斷式 (似乎原本的錯誤並無影響)
 * 				修正錯誤訊息 - 第一條
 * 				修正 _prevent_error 為 _error_occur
 * 				修正 error發生時的流程(_error_occur)
 * 				移除 public $cur_page_mark
 */

class PaginationFive
{
	public $mode = 1;				// 0 for CI, 1 for normal query string type
	public $base_url = '';
	public $para = '';				//$parameters behind 'page' param
	public $total_page = 0;
	public $link_display = 10;
	public $cur_page = 1;
	public $cur_page_pos = 1;		//set current page's position in the link queue.
	public $cur_page_prefix = '<b style="font-size:30px;">';
	public $cur_page_suffix = '</b>';
	public $cur_page_anchor = FALSE;	//current page has anchor or not
	public $prefix = '';
	public $suffix = '';
	public $first_and_last = TRUE;
	public $first_tag_name = '第一頁';
	public $last_tag_name = '最末頁';
	public $prev_next = TRUE;
	public $prev_tag_name = '上一頁';
	public $next_tag_name = '下一頁';
	public $debug_mode = FALSE;
	public $isPreview = FALSE;
	public $page_preview = 2;		//number of page to preview
	public $conjunction = '... ';

	private $space = '&nbsp;';
	private $page_key = 'page';		// for query string, name of the value

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
		if(!$this->_error_occur())
		{
			if($this->first_and_last)	//print first anchor
			{
				$url = $this->_url_builder(FIRST_PAGE);
				$link .= $this->_anchor_construct($url,$this->first_tag_name);
			}
			if($this->prev_next && $this->cur_page > FIRST_PAGE)	//print prev tag anchor
			{
				$url = $this->_url_builder($this->cur_page-1);
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
					$url = $this->_url_builder($i);
					$link .= $this->_anchor_construct($url,$i);
				}
				$link .= $this->conjunction;
			}

			//print each link
			for($page_n = $first_link_n;$page_n <= $first_link_n + $this->link_display - 1;$page_n++)
			{
				$url = $this->_url_builder($page_n);
				if($page_n == $this->cur_page)
				{
					if($this->cur_page_anchor)
					{
						$link .= $this->_anchor_construct($url,$page_n,$this->cur_page_prefix,$this->cur_page_suffix);
					}else{
						$link .= $this->cur_page_prefix.$page_n.$this->cur_page_suffix;
					}
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
					$url = $this->_url_builder($i);
					$link .= $this->_anchor_construct($url,$i);
				}
			}

			if($this->prev_next && $this->cur_page < $this->total_page)	//print next tag anchor
			{
				$url = $this->_url_builder(($this->cur_page+1));
				$link .= $this->_anchor_construct($url,$this->next_tag_name);
			}
			if($this->first_and_last)	//print last anchor
			{
				$url = $this->_url_builder($this->total_page);
				$link .= $this->_anchor_construct($url,$this->last_tag_name);
			}
		}
		return $link;
	}
	
	/**
	 * combine query string or CI parameters
	 * help you combine your parameter into query string or CI type, and set this object's para
	 * @param	$params array: parameters, index to be name, value to be value
	 * @return	$query_string string: query string
	 */
	public function setUrlParameter($params = array())
	{
		$query_string = '';
		if($this->mode == 0)
		{
			foreach($params AS $key => $para)
			{
				$query_string .= '/'.$para;
			}
		}elseif($this->mode == 1){
			foreach($params AS $key => $para)
			{
				$query_string .= '&' . $key . '=' . $para;
			}
		}
		$this->para = $query_string;
		return $query_string;
	}
	
	/**
	 * 組合參數形成 Url, 有 CI的參數型態 or 一般的 query string
	 * @param	$page int: page number
	 * @return	$url string: url
	 */
	private function _url_builder($page)
	{
		$url = '';
		$params = $this->para;
		if($this->mode == 0)
		{
			// For CI
			$url = $this->base_url.'/'.$page.(($params)?$params:NULL);
		}elseif($this->mode == 1){
			// For normal query string
			$url = $this->base_url.'?'.$this->page_key.'='.$page.(($params)?$params:NULL);
		}
		return $url;
	}

	/**
	 * build anchor
	 * 
	 * @access	private
	 * @param	$url String: url of link
	 * @param	$text String: text of anchor
	 * @param	$dom_beg, $dom_end String: html dom to cladding anchor
	 * @return	$anchor String
	 */
	private function _anchor_construct($url, $text, $pre_elem = NULL, $suffix_elem = NULL)
	{
		$pre_elem = (empty($pre_elem))?$this->prefix:$pre_elem;
		$suffix_elem = (empty($suffix_elem))?$this->suffix:$suffix_elem;
		$anchor = '';
		$anchor .= $pre_elem;
		$anchor .= "<a href='" . $url . "'>";
		$anchor .= $text;
		$anchor .= "</a>";
		$anchor .= $suffix_elem;
		return $anchor;
	}

	/**
	 * prevent invalid situation and params
	 * 
	 * @access	private
	 * @return	void
	 */
	private function _error_occur()
	{
		$error_occur = FALSE;
		if($this->cur_page < FIRST_PAGE OR $this->cur_page > $this->total_page)
		{
			$this->_echo_error('Wrong parameters!! Check "cur_page" OR "total_page" Please!');
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
		if($error_occur AND $this->debug_mode)
		{
			die();
		}
		return $error_occur;
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