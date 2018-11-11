<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

class PageControl{
	private $pageNumber;
	private $startIndex;
	private $endIndex;
	private $limit = 100;
	private $currentPage = 1;
	private $indexRange = [];
	private $dataCount;
	private $pageAvail = false;
	
	/**
	 * Initiate Page Controller
	 * @param array $indexRange
	 * @param integer $currentPage
	 * @param integer $limit
	 */
	public function begin($indexRange, $currentPage, $limit){
		if(empty($indexRange)) return false;
		if(!is_array($indexRange)) die("Index Range must be an array!");
		//Re-order 
		$this->dataCount = count($indexRange);
		for($i=0; $i < $this->dataCount; $i++){
			$this->indexRange[$i] = $indexRange[$i];
		}
		if($currentPage == "" || $currentPage == "0" || !is_numeric($currentPage)) $currentPage = 1;
		$this->currentPage = $currentPage;
		if($limit == "" || $limit == "0" || !is_numeric($limit)) $limit = 100;
		$this->limit = $limit;
		
		//Start Calculating
		$this->pageNumber = floor($this->dataCount / $this->limit) + (($this->dataCount % $this->limit) > 0?1:0);
		if($this->currentPage > $this->pageNumber){
			$this->currentPage = 1;
			$this->pageAvail = false;
		}else{
			$this->pageAvail = true;
		}
		$this->endIndex = $this->currentPage * $this->limit;
		$this->startIndex = $this->endIndex - $this->limit;
	}
	
	/**
	 * Check if this page available
	 * @return bool
	 */
	public function pageAvailable(){
		return($this->pageAvail);
	}
	
	/**
	 * Get item range to be displayed in this page
	 * @return array
	 */
	public function getIndexRange(){
		$index = [];
		for($i=$this->startIndex; $i < $this->endIndex; $i++){
			if(!isset($this->indexRange[$i])) continue;
			$index[$i] = $this->indexRange[$i];
		}
		return($index);
	}
	
	/**
	 * Get HTML navigation
	 * @param string $redir
	 * @comment Put $redir with something like this "?s=%S%&something", where %S% will be the page number
	 * @return string
	 */
	public function getNavigationHTML($redir){
		echo('<style>.pagination li a{cursor:pointer;}</style>');
		$l = new Language;$l->app = "page_control";
		ob_start();
		?>
		<nav aria-label="Page navigation">
		<ul class="pagination pagination-sm">
		<?php if(($this->currentPage-1) >= 1):?>
		<li class="page-item"><a href="<?php echo str_replace("%S%",$this->currentPage-1,$redir);?>" class="page-link" aria-label="Previous">
			<?php $l->dump("prev");?>
		  </a></li>
		<?php endif;?>
		<?php
			$countPage = 1; //Max to 5
			$maxCount = 5;
			if($this->pageNumber < $maxCount) $maxCount = $this->pageNumber;
			$posPage = 1;
			$negPage = 1;
			$countType = 0; //0=>Forward, 1=>Backward
			$pgnum_check = $this->currentPage;
			$buffer = '<li class="page-item active"><a class="page-link">'.$pgnum_check.'</a></li>';
			while($countPage < $maxCount){
				if($countType == 0){
					if(($pgnum_check+$posPage) <= $this->pageNumber){
						$countPage++;
						$buffer .= '<li class="page-item"><a href="'.str_replace("%S%",$pgnum_check+$posPage,$redir).'" class="page-link" >'.($pgnum_check+$posPage).'</a></li>'."\r\n";
						$posPage++;
					}
				}else if($countType == 1){
					if(($pgnum_check-$negPage) >= 1){
						$countPage++;
						$buffer = '<li class="page-item"><a href="'.str_replace("%S%",$pgnum_check-$negPage,$redir).'" class="page-link" >'.($pgnum_check-$negPage).'</a></li>' ."\r\n" . $buffer;
						$negPage++;
					}
				}
				$countType = ($countType==0?1:0);
			}
			echo $buffer;
			unset ($buffer);
		?>
		<?php if(($this->currentPage+1) <= $this->pageNumber):?>
		<li class="page-item"><a href="<?php echo str_replace("%S%",$this->currentPage+1,$redir);?>" class="page-link"  aria-label="Next">
			<?php $l->dump("next");?>
		  </a>
		</li>
		<?php endif;?>
		</ul>
		</nav>
		<?php
		return(ob_get_clean());
	}
	
	/**
	 * Print HTML navigation
	 * @param string $redir
	 * @comment Put $redir with something like this "?s=%S%&something", where %S% will be the page number
	 */
	public function dumpNavigation($redir){
		echo $this->getNavigationHTML($redir);
	}
}