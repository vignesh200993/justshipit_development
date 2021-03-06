<?php defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' ); 

require APPPATH . '/libraries/quickbook/autoload.php';

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;


/**
 * Class : BaseController
 * Base Class to control over all the classes
 * @author : Kishor Mali
 * @version : 1.1
 * @since : 15 November 2016
 */
class BaseController extends CI_Controller {
	protected $role = '';
	protected $vendorId = '';
	protected $name = '';
	protected $roleText = '';
	protected $global = array ();
	protected $lastLogin = '';
	
	protected $aQuickConfig = array(
		'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
		'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
		'client_id' => 'ABVD9f1wvB4zvafoy7IPM2V76h5OoubpUQohejTrKBnEUremtz',
		'client_secret' => 'gDdhvw6zb9ognzY9GjB9AhwonuozP0ibxuubDXqN',
		'oauth_scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
		'oauth_redirect_uri' => 'https://development.justshipit.com/callback',
		'base_url' => 'development',
		'qbo_realm_id' => '4620816365172153260',
	);
	
	/**
	 * Takes mixed data and optionally a status code, then creates the response
	 *
	 * @access public
	 * @param array|NULL $data
	 *        	Data to output to the user
	 *        	running the script; otherwise, exit
	 */
	public function response($data = NULL) {
		$this->output->set_status_header ( 200 )->set_content_type ( 'application/json', 'utf-8' )->set_output ( json_encode ( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) )->_display ();
		exit ();
	}
	
	/**
	 * This function used to check the user is logged in or not
	 */
	function isLoggedIn() {
		$isLoggedIn = $this->session->userdata ( 'isLoggedIn' );
		
		if (! isset ( $isLoggedIn ) || $isLoggedIn != TRUE) {
			redirect ( 'login' );
		} else {
			$this->role = $this->session->userdata ( 'role' );
			$this->vendorId = $this->session->userdata ( 'userId' );
			$this->name = $this->session->userdata ( 'name' );
			$this->roleText = $this->session->userdata ( 'roleText' );
			$this->lastLogin = $this->session->userdata ( 'lastLogin' );
			
			$this->global ['name'] = $this->name;
			$this->global ['role'] = $this->role;
			$this->global ['role_text'] = $this->roleText;
			$this->global ['last_login'] = $this->lastLogin;
		}
	}
	
	/**
	 * This function is used to check the access
	 */
	function isAdmin() {
		if ($this->role != ROLE_ADMIN) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * This function is used to check the access
	 */
	function isTicketter() {
		if ($this->role != ROLE_ADMIN || $this->role != ROLE_MANAGER) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * This function is used to load the set of views
	 */
	function loadThis() {
		$this->global ['pageTitle'] = 'JustShipIt : Access Denied';
		
		$this->load->view ( 'includes/header', $this->global );
		$this->load->view ( 'access' );
		$this->load->view ( 'includes/footer' );
	}
	
	/**
	 * This function is used to logged out user from system
	 */
	function logout() {
		$this->session->sess_destroy ();
		
		redirect ( 'login' );
	}

	/**
     * This function used to load views
     * @param {string} $viewName : This is view name
     * @param {mixed} $headerInfo : This is array of header information
     * @param {mixed} $pageInfo : This is array of page information
     * @param {mixed} $footerInfo : This is array of footer information
     * @return {null} $result : null
     */
    function loadViews($viewName = "", $headerInfo = NULL, $pageInfo = NULL, $footerInfo = NULL){

		
		$dataService = DataService::Configure(array(
			'auth_mode' => 'oauth2',
			'ClientID' => $this->aQuickConfig['client_id'],
			'ClientSecret' =>  $this->aQuickConfig['client_secret'],
			'RedirectURI' => $this->aQuickConfig['oauth_redirect_uri'],
			'scope' => $this->aQuickConfig['oauth_scope'],
			'baseUrl' => $this->aQuickConfig['base_url'],
		));

		$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
		$headerInfo['quickauthUrl'] = $OAuth2LoginHelper->getAuthorizationCodeURL();

		if (isset($_SESSION['sessionAccessTokenQB'])) {

			$accessToken = $_SESSION['sessionAccessTokenQB'];
			$headerInfo['accessTokenJson'] = array(
				'token_type' => 'bearer',
				'access_token' => $accessToken->getAccessToken(),
				'refresh_token' => $accessToken->getRefreshToken(),
				'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
				'expires_in' => $accessToken->getAccessTokenExpiresAt()
			);
			$dataService->updateOAuth2Token($accessToken);
			$oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
			$CompanyInfo = $dataService->getCompanyInfo();
			if(empty($CompanyInfo) && !empty($accessToken)){
				$oauth2LoginHelper = new OAuth2LoginHelper($accessToken->getclientID(),$accessToken->getClientSecret());
				$newAccessTokenObj = $oauth2LoginHelper->
								refreshAccessTokenWithRefreshToken($accessToken->getRefreshToken());
				$newAccessTokenObj->setRealmID($accessToken->getRealmID());
				$newAccessTokenObj->setBaseURL($accessToken->getBaseURL());
				
				$_SESSION['sessionAccessTokenQB'] = $newAccessTokenObj;
				$accessToken = $_SESSION['sessionAccessTokenQB'];
				
				$headerInfo['accessTokenJson'] = array(
					'token_type' => 'bearer',
					'access_token' => $accessToken->getAccessToken(),
					'refresh_token' => $accessToken->getRefreshToken(),
					'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
					'expires_in' => $accessToken->getAccessTokenExpiresAt()
				);
				//$headerInfo['accessTokenJson'] = 'expired';
			}
			
		}
		
        $this->load->view('includes/header', $headerInfo);
        $this->load->view($viewName, $pageInfo);
        $this->load->view('includes/footer', $footerInfo);
    }
	
	/**
	 * This function used provide the pagination resources
	 * @param {string} $link : This is page link
	 * @param {number} $count : This is page count
	 * @param {number} $perPage : This is records per page limit
	 * @return {mixed} $result : This is array of records and pagination data
	 */
	function paginationCompress($link, $count, $perPage = 10, $segment = SEGMENT) {
		$this->load->library ( 'pagination' );

		$config ['base_url'] = base_url () . $link;
		$config ['total_rows'] = $count;
		$config ['uri_segment'] = $segment;
		$config ['per_page'] = $perPage;
		$config ['num_links'] = 5;
		$config ['full_tag_open'] = '<nav><ul class="pagination">';
		$config ['full_tag_close'] = '</ul></nav>';
		$config ['first_tag_open'] = '<li class="arrow">';
		$config ['first_link'] = 'First';
		$config ['first_tag_close'] = '</li>';
		$config ['prev_link'] = 'Previous';
		$config ['prev_tag_open'] = '<li class="arrow">';
		$config ['prev_tag_close'] = '</li>';
		$config ['next_link'] = 'Next';
		$config ['next_tag_open'] = '<li class="arrow">';
		$config ['next_tag_close'] = '</li>';
		$config ['cur_tag_open'] = '<li class="active"><a href="#">';
		$config ['cur_tag_close'] = '</a></li>';
		$config ['num_tag_open'] = '<li>';
		$config ['num_tag_close'] = '</li>';
		$config ['last_tag_open'] = '<li class="arrow">';
		$config ['last_link'] = 'Last';
		$config ['last_tag_close'] = '</li>';
		$config ['reuse_query_string'] = true;
	
		$this->pagination->initialize ( $config ); 
		$page = $config ['per_page'];
		$segment = $this->uri->segment ( $segment );
	
		return array (
				"page" => $page,
				"segment" => $segment
		);
	}
}