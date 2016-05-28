<?php
declare(strict_types=1);

// Include MySQL class
require_once('inc/basicmysql.class.php');
// Include database connection
require_once('inc/global.inc.php');

/**
 * @author Sérgio Soares 2016
 * 
 * email serguiomicaelo@gmail.com
 * 
 * "PHP datagrid with navigator 4.0"
 * 
 * datagrid.class.php Update for PHP 7
 *
 */

class datagrid {

 private $page;
 private $rows;
 private $cols;
 private $multipleColors;
 private $num_visible_frames;
 
 private $index;
 private $arr;
 private $registo_index;
 
 private $numPage;
 
 //mysql array:
 private $mysqlString;
 
		/**
		 * datagrid constructor
		 * 
		 * @param int $page = page index number.
		 * @param int $rows = number of rows.
		 * @param int $cols = number of collums.
		 * @param bool $multipleColors = for using two diferent colors on row tables if (TRUE).
		 * @param int $num_visible_frames = number of pages to show in navigator between <<prev>> & <<next>> links.
		 */
		public function __construct(int $page, int $rows, int $cols, bool $multipleColors,int $num_visible_frames){
				 	$this->rows=$rows;
				 	$this->cols=$cols;
				 	$this->multipleColors=$multipleColors;
				 	$this->num_visible_frames=$num_visible_frames;
				 	$this->registo_index=$page;
				 	$this->page = $this->registo_index;
				 	
				 	//MySQL Srings:
				 	
				 	
		}
 
	/**
	 * @return string
	 * 
	 * This funtion returns datagrid cells with mysql values
	 * 
	 */
	private function template_db() {
		global $DB;
		
		$result = $DB->query("SELECT url, title, text, price FROM products WHERE id='".$this->arr[$this->registo_index-1]."'");
		
		if (!defined('TABLE')) define ('TABLE',['url','title','text','price']);

			while ($sqlReg=$result->fetch()) {
				
					return "<table Border='0' cellspacing='10' cellpadding='0' width='300'>
						<tr>
							<td rowspan='3'><img src=" . $sqlReg[TABLE[0]] . " height='100'></td>
							<td colspan='2' cellspacing='0'><b><font size='4'>".$sqlReg[TABLE[1]]."</font></b></td>
						</tr>
							<tr><td colspan='2'>".$sqlReg[TABLE[2]]."</td>
						</tr>
							<tr><td><b><font color='white'>Price: </b></font>".$sqlReg[TABLE[3]]."€</td>
							<td>chart</td>
						</tr>
						</table>";
			}
	}

	/**
	 * @return Total of mysql table records
	 */
	
	private function get_num_rows() {
		
		global $DB;
		
		if ($result = $DB->query('SELECT id FROM products')) {

			$row_cnt = $result->size();

		}
		
		return $row_cnt;
	}
	
	/**
	 * fetchs var $arr
	 */
	private function fetch() {

		global $DB;
		$index="0";

		if ($result = $DB->query("SELECT id FROM products")) {
			
			while ($row = $result->fetch()) {
	
				$this->arr[$index] = $row['id'];
				
				$index++;
	
			}
			
		}

	}

	/**
	 * @return # of records left
	 */
	private function findRecsLeft() {
	
		$totalRec=($this->rows*$this->cols);
		$numRows=$this->get_num_rows();
		
		$numPag=floor($numRows/$totalRec)+1;
		$this->numPage = $numPag;
		
		$numMaxPage=$totalRec*$numPag;
		$rowsLeft=$numMaxPage-$numRows;
		$recsLeft=$totalRec-$rowsLeft;
	
		return $recsLeft;
		
	}
	
	
	/**
	 * This function builds full output datagrid table.
	 * 
	 * @param  $cols = number of collums.
	 * 
	 * @param  $rows = number of rows.
	 * 
	 * @param  $index = get number of the page with $_GET['page'] to get 
	 * the page number on url "&page="datagrid page number".
	 * 
	 * @param  $paint -> Boolean, if true, it returns each pair number of the row with a second color.
	 */
	public function drawTable() {
		
		$lastPageN=isset($_GET['i']) ? (int)$_GET['i'] : 1;
		
		$col_index=1;
		$recsLeft = $this->findRecsLeft();
		$this->fetch();

		if ($this->registo_index==null) {

			$this->registo_index=1;

		}

		echo("<table Border='0' cellspacing='0' cellpadding='0'>\r");
		
		for ($l=1; $l<=$this->rows; $l++) {
		
			echo("<tr>\r");
			
			for ($c=1; $c<=$this->cols; $c++) {
				
				if ($this->multipleColors) {
				
					if ($col_index > $this->cols*2) {
						$col_index="1";
					}
					
					if ($recsLeft > 0) {
					
						if ($col_index<=$this->cols) {
	
							echo("<td bgcolor=#7c7c7c>".$this->template_db()."</td>\r");
							
						} else {
	
							echo("<td bgcolor=#8c8c8c>".$this->template_db()."</td>\r");
							
						}
						
						if ($this->numPage == $lastPageN){
							
							$recsLeft--;
							
						}
						
					}

					$this->registo_index++;
					$col_index ++;

				} else {

					if ($recsLeft > 0) {
						
						echo("<td>".$this->template_db()."</td>\r");
						$this->registo_index ++;
						$recsLeft--;
						
					}
					
				}
			}
			
			echo "</tr>\r";

		}
		
		echo "</table>\r";
	}


	/**
	 * This function it's for navigating the datagrid's pages.
	 * 
	 * @param $page -> get number of the page with $_GET['page'] to get 
	 * the page number on url "&page="datagrid page number".
	 * 
	 * @param $num -> you can choose how much pages represented by a page number will appear into the 
	 * navigator, between "prev" and "Next" link buttons.
	 */
	public function navigator() {
		
		(int)$url_i_var = $_GET['i'] ?? 1; //php 7 only!
		(int)$url_bindex_var = $_GET['bindex'] ?? 1; //php 7 only!
		//$url_i_var = isset($_GET['i']) ? (int)$_GET['i'] : 1;
		//$url_bindex_var = isset($_GET['bindex']) ? (int)$_GET['bindex'] : 1;
		
		$group_index = 1;

		if ($this->get_num_rows() < ($this->rows*$this->cols)) {

			$num_pag = 1;

		} else {

			$num_pag = ceil(($this->get_num_rows()/($this->rows*$this->cols))+1);

		}

		if ($url_i_var > 1) {

			$a=$url_i_var-1;

			$index = $url_bindex_var-1;
			$page_prev = $this->page-($this->rows*$this->cols);

			if ($url_bindex_var <= 1) {

				echo "<a href=index.php?page=".$page_prev."&bindex=1&i=".$a.">Prev</a>\n";

			} else {

				echo "<a href=index.php?page=".$page_prev."&bindex=".$index."&i=".$a.">Prev</a>\n";

			}			
		}

		if ($this->get_num_rows() > ($this->rows*$this->cols)) {
			
			$limit = $this->get_num_rows();
			
			if ($url_bindex_var == null) {

				$this->index = 1;

			} else {
				if ($num_pag > $this->num_visible_frames) {
					if ($url_i_var > floor($this->num_visible_frames/2)) {
						$this->index = $url_i_var-floor($this->num_visible_frames/2);
					} 
				} else {

				$this->index = $url_bindex_var;
				}
			}
			
			for ($r=1; $r <= $this->num_visible_frames; $r++) {

				$group_index=$this->index-$r+1;
				
				if ($this->index != 1) {

					$pag=($this->index*($this->cols*$this->rows)+1)-($this->cols*$this->rows);

				} else {

					$pag=1;

				}				
				
				if ($pag <= $limit) {

					if ($url_i_var == $this->index) {

						echo "| <a href=index.php?page=".$pag."&bindex=".$group_index."&i=".$this->index."><b><font color='green'>$this->index</font></b></a> | ";
	
					} else {

						echo "| <a href=index.php?page=".$pag."&bindex=".$group_index."&i=".$this->index.">$this->index</a> | ";

					}

				}

				$this->index++;

			}

		}

		if ($url_i_var < $num_pag-1) {

			$last_set = ($num_pag - $this->num_visible_frames);

			if ($url_i_var == "") {
			
				$i = 2;

			} else {

				$i=$url_i_var + 1;

			}			

			if ($url_bindex_var == null) {

				if ($num_pag > $this->num_visible_frames) {

					$index = 2;

				} else {

					$index = 1;

				}

			} else {

				if ($num_pag > $this->num_visible_frames) {

					$index = $url_bindex_var+1;

				} else {

					$index = $url_bindex_var;

				}

			}

  			if ($this->page == null) {

  				$page_next=$this->page+(($this->rows*$this->cols)+1);
  	
  			} else {

				$page_next=$this->page+($this->rows*$this->cols);

  			}

			if ($index != $last_set) {

				echo "<a href=index.php?page=".$page_next."&bindex=".$index."&i=".$i.">Next</a>\n";

			} else {

				echo "<a href=index.php?page=".$page_next."&bindex=".$last_set."&i=".$i.">Next</a>\n";	

  			}

		}

	}

}
?>
