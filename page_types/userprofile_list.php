<?
defined('C5_EXECUTE') or die("Access Denied.");
/**
 * @copyright Fumito MIZUNO 2012
 * @license GNU Affero General Public License ver.3.0 or later
 * http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://php-web.net/
 */
//$this->inc('elements/header.php'); 
?>

	<div id="central">
		<div id="sidebar">
<?
$as = new Area('Sidebar');
$as->display($c);
?>		
		</div>

		<div id="body">	
<?
$a = new Area('Main');
$a->display($c);
?>
<?
$page = Page::getCurrentPage();
$children = $page->getCollectionChildrenArray(true);
//order by rand
shuffle($children);
foreach ($children as $ID) {
	$childpage = Page::getByID($ID);
	$blocks = $childpage->getBlocks('main');
	foreach ($blocks as $block) {
		$block->display();
	}
}


?>
		</div>

		<div class="spacer">&nbsp;</div>		
	</div>

<? //$this->inc('elements/footer.php'); ?>
