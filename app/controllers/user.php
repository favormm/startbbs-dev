<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
#doc
#	classname:	User
#	scope:		PUBLIC
#	StartBBS起点轻量开源社区系统
#	author :doudou QQ:858292510 startbbs@126.com
#	Copyright (c) 2013 http://www.startbbs.com All rights reserved.
#/doc

class User extends SB_Controller
{

	function __construct ()
	{
		parent::__construct();
		$this->load->model ('user_m');
		$this->load->library('myclass');

	}
	public function info ($uid)
	{
		$data = $this->user_m->get_user_by_id($uid);
		//用户大头像
		$this->load->model('upload_m');
		$data['big_avatar']=$this->upload_m->get_avatar_url($uid, 'big');
		//此用户发贴
		$this->load->model('forum_m');
		$data['user_posts'] = $this->forum_m->get_forums_by_uid($uid,5);
		//此用户回贴
		$this->load->model('comment_m');
		$data['user_comments'] = $this->comment_m->get_comments_by_uid($uid,5);
		//是否被关注
		$this->load->model('follow_m');
		$data['is_followed'] = $this->follow_m->follow_user_check($this->session->userdata('uid'), $uid);
		
		$this->load->view('userinfo', $data);
		
	}
	public function reg ()
	{

		//加载form类，为调用错误函数,需view前加载
		$this->load->helper('form');
		
		$data['title'] = '注册新用户';
		if ($this->auth->is_login()) {
			$this->myclass->notice('alert("已登录，请退出再注册");window.location.href="'.site_url().'";');
			exit;
		}
		if($_POST && $this->validate_reg_form()){
			$ip = $this->myclass->get_ip();
			$data = array(
				'username' => strip_tags($this->input->post('username')),
				'password' => md5($this->input->post('password',true)),
				'openid' => strip_tags($this->input->post('openid')),
				'email' => $this->input->post('email',true),
				'ip' => $ip,
				'gid' => 1,
				'regtime' => time(),
				'is_active' => 1
			);
			$check_reg = $this->user_m->check_reg($data['email']);
			$check_username = $this->user_m->check_username($data['username']);
			$captcha = $this->input->post('captcha_code');
			if(!empty($check_reg)){
				$this->myclass->notice('alert("邮箱已注册，请换一个邮箱！");history.back();');
			} elseif(!empty($check_username)){
				$this->myclass->notice('alert("用户名已存在!!");history.back();');
				} elseif(md5($this->input->post('password_c'))!=$data['password']){
					$this->myclass->notice('alert("密码输入不一致!!");history.back();');
				} elseif($this->config->item('show_captcha')=='on' && $this->session->userdata('yzm')!=$captcha) {
					$this->myclass->notice('alert("验证码不正确!!");history.back();');
				} else {
					if($this->user_m->reg($data)){
						$uid = $this->db->insert_id();
						$this->session->set_userdata(array ('uid' => $uid, 'username' => $data['username'], 'password' =>$data['password'], 'gid' => $data['gid']) );
						//去除session
						$this->session->unset_userdata('yzm');
					}
					redirect();
				}

		} else{
			$this->load->view('reg',$data);
		}
	}
	
	public function username_check($username)
	{  
		if(!preg_match('/^(?!_|\s\')(?!.*?_$)[A-Za-z0-9_\x{4e00}-\x{9fa5}\s\']+$/u', $username)){
			$this->form_validation->set_message('username_check', '%s 只能含有汉字、数字、字母、下划线（不能开头或结尾)');
  			return false;
		} else{
			return true;
		}
	}
	
	private function validate_reg_form(){
		$this->load->library('form_validation');

		$this->form_validation->set_rules('email', 'Email' , 'trim|required|min_length[3]|max_length[50]|valid_email');
		$this->form_validation->set_rules('username', '昵称' , 'trim|required|min_length[2]|max_length[20]|callback_username_check|xss_clean');
		$this->form_validation->set_rules('password', '用户密码' , 'trim|required|min_length[6]|max_length[40]|matches[password_c]');
		$this->form_validation->set_rules('password_c', '密码验证' , 'trim|required|min_length[6]|max_length[40]');
		$this->form_validation->set_message('required', "%s 不能为空！");
		$this->form_validation->set_message('min_length', "%s 最小长度不少于 %s 个字符或汉字！");
		$this->form_validation->set_message('max_length', "%s 最大长度不多于 %s 个字符或汉字！");
		$this->form_validation->set_message('matches', "两次密码不一致");
		$this->form_validation->set_message('valid_email', "邮箱格式不对");
		$this->form_validation->set_message('alpha_dash', "邮箱格式不对");
		if ($this->form_validation->run() == FALSE){
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	public function login ()
	{
		$data['title'] = '用户登录';
		$data['referer']=$this->input->get('referer',true);
		//$data['referer']=($this->input->server('HTTP_REFERER')==site_url('user/login'))?'/':$this->input->server('HTTP_REFERER');
		$data['referer']=$data['referer']?$data['referer']: $this->input->server('HTTP_REFERER');
		if($this->auth->is_login()){
			redirect();
			//$this->myclass->notice('alert("此用户已登录");window.location.href="/";');
		}
		if($_POST){
			$username = $this->input->post('username',true);
			$password = $this->input->post('password',true);
			$user = $this->user_m->check_login($username, $password);
			$captcha = $this->input->post('captcha_code');
			if($this->config->item('show_captcha')=='on' && $this->session->userdata('yzm')!=$captcha) {
				$this->myclass->notice('alert("验证码不正确!!");history.go(0);');
			} elseif(count($user)){
				//更新session
				$this->session->set_userdata(array ('uid' => $user['uid'], 'username' => $user['username'], 'password' =>$user['password'], 'gid' => $user['gid']) );
				//设置cookie
				$cookie = array(
                   'name'   => 'uid',
                   'value'  => $user['uid'],
                   'expire' => '86400',
                   'domain' => '.'.$this->myclass->get_domain(site_url()),
                   'path'   => '/'
               );
               
	            $this->input->set_cookie($cookie);
	            $cookie = array(
	                'name'   => 'username',
	                'value'  => $user['username'],
	                'expire' => '86400',
	                'domain' => '.'.$this->myclass->get_domain(site_url()),
	                'path'   => '/'
	            );
            	$this->input->set_cookie($cookie);
	            $cookie = array(
	                'name'   => 'password',
	                'value'  => $user['password'],
	                'expire' => '86400',
	                'domain' => '.'.$this->myclass->get_domain(site_url()),
	                'path'   => '/'
	            );
            	$this->input->set_cookie($cookie);
	            $cookie = array(
	                'name'   => 'gid',
	                'value'  => $user['gid'],
	                'expire' => '86400',
	                'domain' => '.'.$this->myclass->get_domain(site_url()),
	                'path'   => '/'
	            );
            	$this->input->set_cookie($cookie);
	            //更新openidQQ
				$openid = strip_tags($this->input->post('openid'));
				if($openid){
					$this->user_m->update_user($user['uid'], array('openid'=>$openid));
				}
				header("location: ".$data['referer']);
				//redirect($data['referer']);
				exit;
				
			} else {
				$this->myclass->notice('alert("用户名或密码错误!!");history.back();');
			}
		} else {
			$this->load->view('login',$data);
		}
		
	}

	public function logout ()
	{
		$this->session->sess_destroy();
		
		$this->load->helper('cookie');
		delete_cookie('uid');
		delete_cookie('username');
		delete_cookie('password');
		delete_cookie('gid');
		delete_cookie('openid');
		
		Header("Location: ".site_url('user/login'));
		exit;
	}


	public function findpwd()
	{
		if($_POST){
			$username = $this->input->post('username');
			$data = $this->user_m->getpwd_by_username($username);
			if(@$data['email']==$this->input->post('email')){

//           $config['protocol'] = 'sendmail';
//$config['charset'] = 'utf8';
//$config['wordwrap'] = TRUE;
// $config['mailtype'] = 'text';
//$this->load->library('email',$config);

 
				$config['protocol']=$this->config->item('protocol');
				$config['smtp_host']=$this->config->item('smtp_host');
				$config['smtp_user']=$this->config->item('smtp_user');
				$config['smtp_pass']=$this->config->item('smtp_pass');
				$config['smtp_port']=$this->config->item('smtp_port');
				$config['charset'] = 'utf-8';
				$config['wordwrap'] = TRUE;
				$config['mailtype'] = 'html';
				$this->load->library('email',$config);
				$x = md5($username.'+').@$data['password'];
				$string = base64_encode($username.".".$x);
				
				$this->email->from($config['smtp_user'],'startbbs');
				$this->email->to($this->input->post('email'));
				$this->email->subject('重置密码-'.$this->config->item('site_name'));
				$message = '尊敬的用户'.$username.':<br/>你使用了本站提供的密码找回功能，如果你确认此密码找回功能是你启用的，请点击下面的链接，按流程进行密码重设。<br/><a href="'.site_url("user/resetpwd?p=").$string.'">'.site_url('user/reset_pwd?p=').$string.'</a><br/>如果不能打开链接，请复制链接到浏览器中。<br/>如果本次密码重设请求不是由你发起，你可以安全地忽略本邮件。';
				$this->email->message($message);
				if($this->email->send()){
					$data['msg'] = '密码重置链接已经发到您邮箱:'.$data['email'].',请注意查收！';
				}else{
					$data['msg'] = '没有发送成功';
				}
				$data['title'] =  '信息提示';
				$this->load->view('msg',$data);
				//echo $this->email->print_debugger();
			} else {
				$this->myclass->notice('alert("用户名或邮箱错误!!");history.back();');
			}
		} else{
			$data['title'] = '找回密码';
			$this->load->view('findpwd',$data);
		}

	}

	public function resetpwd()
	{

		$array = explode('.',base64_decode(@$_GET['p']));
		$data = $this->user_m->getpwd_by_username($array['0']);
		//$sql = "select passwords from member where username = '".trim($_array['0'])."'";
		$checkCode = md5($array['0'].'+').@$data['password'];
			
		if(@$array['1'] === $checkCode ){
			if($_POST){
				$password = md5($this->input->post('password'));
				if($this->user_m->update_user(@$data['uid'], array('password'=>$password))){
					$this->session->set_userdata(array ('uid' => $data['uid'], 'username' => $array['0'],'password' => $password, 'gid' => $data['gid']));
					redirect('/');
				}
			}
		} else{
			$this->myclass->notice('alert("非法重置!!");history.back();');
		}
		$data['title'] = '设置新密码';
		$data['p'] = $_GET['p'];
		$this->load->view('findpwd',$data);
	}

}