<?php
require_once 'spider_vitanherbs.com_parser.php';
require_once 'spider_lib_ag/spider_controller.php';

class Spider_VitanherbsCom_Controller extends Spider_Controller
{
	
	public function __construct()
	{
		parent::__construct( 'http://www.vitanherbs.com/', new Spider_VitanherbsCom_Parser() );
	}
	
	//override openHref to enforce custom user agent
	//TODO include timestamps in openHref ? or similar construct ?
	public function openHref($href, $pageUrl = NULL)
	{
		$bak=XPathHelper::$_curlopts;
		XPathHelper::$_curlopts[CURLOPT_USERAGENT]= "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11";
		
		$res=parent::openHref($href);
		
		XPathHelper::$_curlopts=$bak;
		
		return $res;
	}
	
	public function search($keyword)
	{
		$results=array();
		for($href= '/catalogsearch/result/?'
				. http_build_query(array('q' => $keyword));
			$href !== null;
			$href= $this->getParser()->parseSearchResultPagination($xph)
			)
		{
			$xph= $this->openHref($href);
			$scraping_timestamp = new DateTime("now",new DateTimeZone('UTC'));
			$scraping_timestamp = $scraping_timestamp->format("Y-m-d h:i:s");
			$new_results=$this->getParser()->parseSearchResult($xph);
			foreach($new_results as &$record)
			{
				$record= $this->getParser()->translateRecord($record);
				$record['timestamp']=$scraping_timestamp;
			}
			$results=array_merge($results,$new_results);
		}
		return $results;
	}
	
	public function searchUpc($upc)
	{

		return $this->search($upc);
	}
	
	public function getProductDetails($url)
	{
		return
			$this->getParser()->translateRecord(
				$this->getParser()->parseProductDetails(new XPathHelper($url))
			);
	}
}