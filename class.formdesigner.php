<?php

class FormDesigner {
	const API_URL = 'https://i.formdesigner.loc';
	
	private $hash;
	private $cryptKey;
	
	public function __construct($hash, $cryptKey){
		$this->hash = $hash;
		$this->cryptKey = $cryptKey;
		
		register_deactivation_hook( FORMDESIGNER__PLUGIN_DIR.'/formdesigner.php', array($this, 'uninstall'));

		add_action( 'admin_menu', array($this, 'adminMenu') );
		add_action( 'admin_enqueue_scripts', array($this, 'loadResources') );
		add_action( 'wp_ajax_formdesigner_popup', array($this, 'popup') );
		
		$hash = get_option('formdesignerHash');
		$cryptKey = get_option('formdesignerCryptKey');
		if($hash && $cryptKey) {
			add_filter('mce_external_plugins', array($this, 'mceRegistr'));
			add_filter('mce_buttons', array($this, 'mceButtons'), 0);
			add_filter('the_content', array($this, 'handleContentTags'), 9 );
		}
	}
	
	public function popup() {
		$projects = array();
		$error = null;
		$hash = get_option('formdesignerHash');
		$cryptKey = get_option('formdesignerCryptKey');
		if($hash && $cryptKey){
			$k = json_encode(array(
				'hash' => $hash, 
				'createdOn' => date('Y-m-d h:i:s'),
			));
			$code = $this->encrypt($k, $cryptKey);
			$res = @file_get_contents(self::API_URL.'/getProjectList?k=zaa'.$hash.rawurlencode($code));
			if($res){
				$res = json_decode($res, true);
				if($res['status'] == 'OK') {
					$projects = $res['data']['projects'];
				}
				else {
					$error = $res['error'];
				}
			}
			else {
				$error = 'Не удалось получить список проектов';
			}
		}

		$this->renderPartial('popup', array(
			'projects' => $projects,
			'error' => $error,
		));
		wp_die();
	}
	
	
	public function mceRegistr($plugin_array){
		$plugin_array['FormDesigner'] = plugins_url('src/formdesigner.js', __FILE__ );
        return $plugin_array;
	}
	
	public function mceButtons($buttons){
		array_push($buttons, "separator", "FormDesigner");
    	return $buttons;
	}
	
	public function adminMenu(){
		add_menu_page( 'Конструктор форм FormDesigner', 'FormDesigner', 8, 'formdesigner', array($this, 'mainContent') );
	}
	
	public function loadResources($hook){
		if(!empty($_GET['page']) && $_GET['page'] == 'formdesigner') {
			wp_enqueue_script( 'common.js', FORMDESIGNER__PLUGIN_URL . 'src/common.js' );
			wp_enqueue_style( 'style.css', FORMDESIGNER__PLUGIN_URL . 'src/style.css' );	
		}
	}
	
	public function handleContentTags($content) {
        $pattern = '/\[formdesigner id=\"(?<id>.*)\"\]/';
        if (preg_match($pattern, $content)) {
           $content = preg_replace_callback($pattern, array($this, "replaceTags"), $content);
        }
        return $content;
    }
    
    public function replaceTags($matches) {
		return $this->renderPartial('code', array(
			'id' => $matches["id"]
		), true);
    }
	
	public function mainContent() {
		$hash = get_option('formdesignerHash');
		$cryptKey = get_option('formdesignerCryptKey');
		
		if($hash!==false && $cryptKey!==false && empty($_GET['force'])) {
			$this->login();
		}
		elseif(!empty($_GET['signup'])) {
			$this->signUp();
		}
		elseif(!empty($_GET['signin'])) {
			$this->signIn();
		}
		else {
			$this->render('welcome');
		}
	}
	
	
	public function login() {
		$hash = get_option('formdesignerHash');
		$k = json_encode(array(
			'hash' => $hash, 
			'createdOn' => date('Y-m-d h:i:s'),
		));
		$code = $this->encrypt($k, get_option('formdesignerCryptKey'));
		echo $this->showIframe(self::API_URL.'/cryptLogin/'.$this->hash.'?k=zaa'.$hash.rawurlencode($code));
	}
	
	
	public function signUp() {
		global $current_user;
		get_currentuserinfo();
		$email = $current_user->user_email;
		$name = $current_user->display_name;
		$errors = array();
				
		if(!empty($_POST['formdesigner'])) {
			$email = !empty($_POST['formdesigner']['email']) ? sanitize_email( $_POST['formdesigner']['email'] ): null;
			$name = !empty($_POST['formdesigner']['name']) ? sanitize_text_field( $_POST['formdesigner']['name'] ): null;
			if($this->isEmpty( $email )) {
				$errors[] = 'Необходимо заполнить поле "E-mail адрес".';
			}
			elseif(!is_email( $email )) {
				$errors[] = $email . ' не является правильным e-mail адресом.';
			}
			if($this->isEmpty( $name )) {
				$errors[] = 'Необходимо заполнить "Имя"';
			}
		}
		
		if($errors===array()) {
			try {
				$res = $this->httpRequest( '/signup/'.$this->hash, array(
					'email' => $email,
					'first_name' => $name
				), 'post');
				if(!empty($res['data']) && $res['status'] == 'OK') {
					$this->uninstall();
					add_option( 'formdesignerHash', $res['data']['hash'] );
					add_option( 'formdesignerCryptKey', $res['data']['cryptKey'] );		
					$this->login();
					exit;
				}
				else {
					$errors = !empty($res['error'])? $res['error']: 'При регистрации возникла ошибка. Повторите свой запрос позже.';
				}
			}
			catch(Exception $e) {
				$errors = $e->getMessage();
			}
		}	
		
		$this->render('signup', array(
			'email' => $email,
			'name' => $name,
			'errors' => $errors
		));
	}
	
	
	public function signIn() {
		$email = null;
		$pass = null;
		$errors = array();
				
		if(!empty($_POST['formdesigner'])) {
			$email = !empty($_POST['formdesigner']['email']) ? sanitize_email( $_POST['formdesigner']['email'] ): null;
			$pass = !empty($_POST['formdesigner']['pass']) ? sanitize_text_field( $_POST['formdesigner']['pass'] ): null;
			if($this->isEmpty( $email )) {
				$errors[] = 'Необходимо заполнить поле "E-mail адрес".';
			}
			elseif(!is_email( $email )) {
				$errors[] = $email . ' не является правильным e-mail адресом.';
			}
			if($this->isEmpty( $pass )) {
				$errors[] = 'Необходимо заполнить "Пароль"';
			}

			if($errors===array()) {
				try {
					$k = json_encode(array(
						'email' => $email,
						'pass' => $pass
					));
					$code = $this->encrypt($k, $this->cryptKey);
					$res = $this->httpRequest( '/getUserCryptData', array(
						'k' => 'zaa'.$this->hash.rawurlencode($code),
					));
					if(!empty($res['data']) && $res['status'] == 'OK') {
						$this->uninstall();
						add_option('formdesignerHash', $res['data']['hash']);
						add_option('formdesignerCryptKey', $res['data']['cryptKey']);		
						$this->login();
						exit;
					}
					else {
						$errors = !empty($res['error'])? $res['error']: 'При авторизации возникла ошибка. Повторите свой запрос позже.';
					}
				}
				catch(Exception $e) {
					$errors = $e->getMessage();
				}
			}
		}
		
		$this->render('signin', array(
			'email' => $email,
			'pass' => $pass,
			'errors' => $errors
		));
	}
	
	
	public function showIframe($src, $htmlOptions = array()) {
		$options = array_merge(array(
			'width' => '100%',
			'height' => '550px',
			'frameborder' => 0,
			'src' => $src,
			'marginwidth' => 0,
			'marginheight' => 0,
			'name' => 'formdesigner',
			'id' => 'formdesigner',
		), $htmlOptions);
		$optionsInline = '';
		foreach($options as $option => $value) {
			$optionsInline .= ' '.$option.'="'.$value.'"';
		}

		return '<iframe'.$optionsInline.'></iframe>';
	}
	
	
	public function isEmpty($value, $trim=true) {
		return $value===null || $value===array() || $value==='' || $trim && is_scalar($value) && trim($value)==='';
	}
	
	
	public function errorSummary($errors) {
		if(
			(is_array($errors) && $errors === array()) || 
			(is_scalar($errors) && trim($errors)==='') || 
			$errors === false
		) {
			return;
		}
		$html = '<div class="errorSummary"><p>Необходимо исправить следующие ошибки:</p><ul>';
		if(is_array($errors)){
			foreach($errors as $error){
				$html .= '<li>'.esc_attr( $error ).'</li>';
			}
		}
		else{
			$html .= '<li>'.esc_attr( $errors ).'</li>';
		}
		$html .= '</ul></div>';
		return $html;
	}
	
	public function render($view, $data = null, $return = false) {
		$output = $this->renderPartial($view, $data, true);
		$output = $this->renderPartial('layout', array('content' => $output), true);
		if($return)
	        return $output;
	    else
	        echo $output;
	}
	
	public function renderPartial( $_viewFile_, $_data_ = null, $_return_=false ){
		$_viewFile_ = FORMDESIGNER__PLUGIN_DIR . 'views/' . $_viewFile_. '.php';
		if( is_array( $_data_ ) )
	        extract( $_data_, EXTR_PREFIX_SAME, 'data' );
	    else
	        $data = $_data_;
	        
		if(file_exists( $_viewFile_ )) {
			if( $_return_ ) {
		        ob_start();
		        ob_implicit_flush( false );
		        require( $_viewFile_ );
		        return ob_get_clean();
		    }
		    else
		        require( $_viewFile_ );
		}
		else{
			throw new Exception('Не удалось найти шаблон вида: ' . $view);
		}
	}
	
	
	private function encrypt($string, $key='%key&') {
		if(empty($key)) {
			$key='%key&';
		}
		$len = strlen($key);
		$result = '';
		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % $len)-1, 1);
			$ordChar = ord($char);
			$ordKeychar = ord($keychar);
			$sum = $ordChar + $ordKeychar;
			$char = chr($sum);
			$result.=$char;
		}
		return base64_encode($result);
	}
	
	
	public function uninstall() {
		delete_option('formdesignerHash');
		delete_option('formdesignerCryptKey');
	}
	
	
	public function httpRequest( $request, $params = array(), $method = 'get', $jsonDecode = true) {
		$params = http_build_query($params);
		
		$url = self::API_URL.'/'.ltrim($request, '/');
		if($method == 'get' && trim($params) != '') {
			$url .= '?'.$params;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		
		if($method == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		
		$content = curl_exec($ch);

		if (curl_errno($ch)) {
			throw new Exception(curl_error($ch));
	    }
	    
	    $requestInfo = curl_getinfo($ch);

		curl_close($ch);

		if($jsonDecode) {
			$content = json_decode($content, true);
			if (version_compare(PHP_VERSION, '5.3') > 0) {
				if(json_last_error()!==JSON_ERROR_NONE){
					$error = ' - Unable to parse responce as JSON';
					switch (json_last_error()) {
				        case JSON_ERROR_DEPTH:
				            $error = ' - Maximum stack depth exceeded';
				        break;
				        case JSON_ERROR_STATE_MISMATCH:
				            $error = ' - Underflow or the modes mismatch';
				        break;
				        case JSON_ERROR_CTRL_CHAR:
				            $error = ' - Unexpected control character found';
				        break;
				        case JSON_ERROR_SYNTAX:
				            $error = ' - Syntax error, malformed JSON';
				        break;
				        case JSON_ERROR_UTF8:
				            $error = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				        break;
				    }
					throw new Exception($error.': '.$content);
				}
			}
			elseif(is_null($content)) {
				throw new Exception('Unable to parse responce as JSON');
			}
		}
		return $content;
	}
}