<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
#doc
#	classname:	Install
#	scope:		PUBLIC
#	StartBBS起点轻量开源社区系统
#	author :doudou QQ:858292510 startbbs@126.com
#	Copyright (c) 2013 http://www.startbbs.com All rights reserved.
#/doc

class Install extends Install_Controller
{
	function __construct ()
	{
		parent::__construct();
		$this->load->library('myclass');

	}
	public function index ()
	{
		$file=FCPATH.'install.lock';
		if (file_exists($file)){
			$this->myclass->notice('alert("系统已安装过");window.location.href="'.site_url().'";');
		} else {
		$this->load->view('install');
		}

	}

	/**
	 * 检查数据库连接
	 */
	public function check(){
		$dbhost = $_REQUEST["dbhost"];
		$dbport = $_REQUEST["dbport"];
		$dbname = $_REQUEST["dbname"];
		$dbuser = $_REQUEST["dbuser"];
		$dbpwd = $_REQUEST["dbpwd"];
		$res = array("msg"=>"");		
		$conn = mysql_connect($dbhost.":".$dbport,$dbuser,$dbpwd);
		$db = mysql_select_db($dbname,$conn);
		if($db){
			$res["code"] = 1;
			$res["msg"] = "数据库连接成功！";
			mysql_close($conn);
		}else{
			$res["code"] = 0;
			$res["msg"] = "数据库连接失败";
		}
		echo json_encode($res);
	}
	
	public function step(){

		if($_POST){
			$dbhost = $this->input->post('dbhost');
			$dbport = $this->input->post('dbport');
			$dbname = $this->input->post('dbname');
			$dbuser = $this->input->post('dbuser');
			$dbpwd = $this->input->post('dbpwd')?$this->input->post('dbpwd'):'';
			$dbprefix = $this->input->post('dbprefix');
			$userid = $this->input->post('admin');
			$pwd = md5($this->input->post('pwd'));
			$email = $this->input->post('email');
			$sub_folder = '/'.$this->input->post('base_url').'/';
			$conn = mysql_connect($dbhost.':'.$dbport,$dbuser,$dbpwd);
			if(mysql_select_db($dbname,$conn)){
				$sql = file_get_contents(FCPATH.'app/config/startbbs.sql');
				$sql = str_replace("sb_",$dbprefix,$sql);
				$explode = explode(";",$sql);
				$data['msg1']="创建表".$dbname."成功，请稍后……<br/>";
			 	foreach ($explode as $key=>$value){
			    	if(!empty($value)){
			    		if(trim($value)){
				    		mysql_query($value.";");
			    		}
			    	}
			  	}
			  	$ip=$this->myclass->get_ip();
			  	$insert= "INSERT INTO ".$dbprefix."users (gid,is_active,username,password,email,regtime,ip) VALUES ('0','1','".$userid."','".$pwd."','".$email."','".time()."','".$ip."')";
			  	mysql_query($insert);
				mysql_close($conn);
				$data['msg2']="安装完成，正在保存配置文件，请稍后……"; 
				$dbconfig = "<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');\n"
				."\$active_group = 'default';\n"
				."\$active_record = TRUE;\n"
				."\$db['default']['hostname'] = '".$dbhost."';\n"
				."\$db['default']['port'] = '".$dbport."';\n"
				."\$db['default']['username'] = '".$dbuser."';\n"
				."\$db['default']['password'] = '".$dbpwd."';\n"
				."\$db['default']['database'] = '".$dbname."';\n"
				."\$db['default']['dbdriver'] = 'mysql';\n"
				."\$db['default']['dbprefix'] = '".$dbprefix."';\n"
				."\$db['default']['pconnect'] = TRUE;\n"
				."\$db['default']['db_debug'] = TRUE;\n"
				."\$db['default']['cache_on'] = FALSE;\n"
				."\$db['default']['cachedir'] = 'app/cache';\n"
				."\$db['default']['char_set'] = 'utf8';\n"
				."\$db['default']['dbcollat'] = 'utf8_general_ci';\n"
				."\$db['default']['swap_pre'] = '';\n"
				."\$db['default']['autoinit'] = TRUE;\n"
				."\$db['default']['stricton'] = FALSE;";
				$file = FCPATH.'/app/config/database.php';
				file_put_contents($file,$dbconfig);
		 
				//保存config文件
				if($sub_folder){
					$this->config->update('myconfig','sub_folder', $sub_folder);
				}

				$data['msg3']="保存配置文件完成！";
				touch(FCPATH.'install.lock'); 
				$data['msg4']="创建锁定安装文件install.lock成功";
				$data['msg5']="安装startbbs成功！";
			} else{
				$this->myclass->notice('alert("数据库没有链接成功");history.go(-1);');
			}
		}

		$this->load->view('install_step',$data);
	}


}
