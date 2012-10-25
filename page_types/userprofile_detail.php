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
$abefore = new Area('Before');
$abefore->display($c);
?>
<?
$amain = new Area('Main');
$amain->display($c);
?>
<?
$aafter = new Area('After');
$aafter->display($c);
?>
		</div>

		<div class="spacer">&nbsp;</div>		
	</div>

<? //$this->inc('elements/footer.php'); ?>
