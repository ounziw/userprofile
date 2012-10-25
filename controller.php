<?php
//
defined('C5_EXECUTE') or die(_("Access Denied."));
/**
 * @copyright Fumito MIZUNO 2012
 * @license GNU Affero General Public License ver.3.0 or later
 * http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://php-web.net/
 */

if (!defined('PAGEPATHNAME'))
{
	define('PAGEPATHNAME', 'userprofile');
}
if (!defined('PAGEPATHHANDLE'))
{
	define('PAGEPATHHANDLE', 'userprofile');
}

class UserprofilePackage extends Package {
	protected $pkgDescription = 'userprofile';
	protected $pkgName = "Userprofile";
	protected $pkgHandle = 'userprofile';

	protected $appVersionRequired = '5.5';
	protected $pkgVersion = '1.0';

	public function on_start() {
		Events::extend('on_user_add', 'UserprofilePackage', 'on_user_add', 'packages/userprofile/controller.php');
		Events::extend('on_user_login', 'UserprofilePackage', 'on_user_login', 'packages/userprofile/controller.php');
		Events::extend('on_user_delete', 'UserprofilePackage', 'on_user_delete', 'packages/userprofile/controller.php');
	}

	public function on_user_add($userInfo) {
		$uID = $userInfo->uID;
		$uName = $userInfo->uName;
		if (!empty($uID)) {
			$parentPage = Page::getByPath('/'.PAGEPATHHANDLE);
			$userPage = Page::getByPath('/'.PAGEPATHHANDLE . '/' . $uName);
			if(is_object($userPage) && !$userPage->isError()){
				$data = sprintf(t("The page %s arleady exists."),$uName);
				Log::addEntry($data);
			} else {
				$data = array(
					'name' => sprintf(t("profile %s"),$uName),
					'cHandle' => $uName,
					'uID' => $uID,
					'cDescription' => sprintf(t("profile %s"),$uName),
				);
				// @TODO update for concrete5.6
				if (version_compare(Config::get('SITE_APP_VERSION'),'5.6.0', '>=')) {
					// for concrete 5.6
					$pt = CollectionType::getByHandle("userprofile_detail");
					$newPage = $parentPage->add($pt,$data);

					$pkHandles = array('view_page');
					$newPage->assignPermissions(Group::getByID(GUEST_GROUP_ID), $pkHandles);
					//Log::addEntry( $userInfo);
					$pkHandles = array('view_page');
					$pkHandles[] = 'view_page_versions';
					$pkHandles[] = 'edit_page_properties';
					$pkHandles[] = 'edit_page_contents';
					$pkHandles[] = 'approve_page_versions';
					$newPage->assignPermissions($userInfo, $pkHandles);
				} else {
					// for concrete 5.5
					$pxml->user[$uID]['uID'] = $uID;
					$pxml->user[$uID]['canRead'] = 1;
					$pxml->user[$uID]['canWrite'] = 1;
					$pxml->user[$uID]['canApproveVersions'] = 1;
					$pxml->user[$uID]['canReadVersions'] = 1;
					$newPage->assignPermissionSet($pxml);
				}
			}
		}
	}

	public function on_user_delete($userInfo) {
		$uName = $userInfo->uName;
		$userPage = Page::getByPath('/'.PAGEPATHHANDLE . '/' . $uName);
		if(is_object($userPage) && !$userPage->isError()){
			$userPage->delete();
		} else {
			$data = sprintf(t("The page %s not found."),$uName);
			Log::addEntry($data);
		}
	}	

	public function on_user_login($thais) {
		$u = new User();
		/*
		ob_start();
		print_r($u);
		$data = ob_get_contents();
		ob_end_clean();
		Log::addEntry( $data);
		 */
		$userPage = Page::getByPath('/'.PAGEPATHHANDLE . '/' . $u->uName);
		if(is_object($userPage) && !$userPage->isError()){
			$thais->redirect('/'.PAGEPATHHANDLE.'/'.$u->uName);
		}
	}

	public function install() {

		$pkg = parent::install();

		$this->precheck();
		$this->install_page_types($pkg);
		$this->add_userprofile_page();
		$this->install_default_blocks();

	}	

	function install_page_types($pkg) {
		$userprofile_page_type = CollectionType::getByHandle('userprofile_list');
		if(!is_object($userprofile_page_type)){
			$userprofilePageTypes = array(
				array('ctHandle' => 'userprofile_list',   'ctName' => t('List for Users')),
				array('ctHandle' => 'userprofile_detail',   'ctName' => t('Single Page for Users')),
			);
			foreach( $userprofilePageTypes as $userprofilePageType ) {
				CollectionType::add($userprofilePageType, $pkg);
			}
		}
	}

	function delete_package_from_db() {
		$db = Loader::db();
		$db->execute('DELETE FROM Packages WHERE pkgHandle = "userprofile"');
	}

	function precheck(){
		if( $this->userprofile_page_exists() ) {
			$this->delete_package_from_db();
			throw new Exception(t(PAGEPATHHANDLE.' already exists.'));  
			exit;
		}
	}

	function userprofile_page_exists(){
		$blogPage = Page::getByPath('/'.PAGEPATHHANDLE);
		if(is_object($blogPage) && !$blogPage->isError()){
			$db = Loader::db();
			$db->execute('DELETE FROM Packages WHERE pkgHandle = "userprofile"');
			return true;
		}
		return false;
	}
	function add_userprofile_page() {
		$pageHome = Page::getByID(HOME_CID);
		$pt = CollectionType::getByHandle("userprofile_list");
		$bPage = $pageHome->add($pt, array('name' => PAGEPATHNAME, 'cHandle' => PAGEPATHHANDLE));
	}

	function install_default_blocks() {
		$pageUser = Page::getByPath('/'.PAGEPATHHANDLE);
		$userListCollectionTypeMT = CollectionType::getByHandle('userprofile_list')->getMasterTemplate();
		$userDetailCollectionTypeMT = CollectionType::getByHandle('userprofile_detail')->getMasterTemplate();
		$btUserList = BlockType::getByHandle('page_list');
		$btUserListPrefs  = array('num'=>'100','cParentID'=>$pageUser->getCollectionID());
		$pageUser->addBlock($btUserList, 'Sidebar', $btUserListPrefs);
		$userListCollectionTypeMT->addBlock($btUserList, 'Sidebar', $btUserListPrefs);
		$userDetailCollectionTypeMT->addBlock($btUserList, 'Sidebar', $btUserListPrefs);
	}
}
