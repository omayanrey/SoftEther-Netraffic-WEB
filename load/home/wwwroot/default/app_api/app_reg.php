<?php
/*

=====================================================================================================

--------------->            以下为注册赠送配置 根据需要 自行修改                 <-------------------

=====================================================================================================
*/

//默认注册成功后 是否禁用 0为禁用 1 为不禁用

$_dd_i = 1;


//默认注册成功后 赠送的流量
$_dd_ll = 10;


//默认注册成功后 赠送的天数
$_dd_t = 1;


/*

=====================================================================================================

--------------->            以下为业务处理逻辑 如非必要 请勿修改                 <-------------------

=====================================================================================================
*/


$username = $_POST['username'];
		$password = $_POST['pass'];
	
		$rand = $_POST['rand'];
		$code = $_POST['code'];
		$_SESSION['checkCode'] = file_get_contents("user_tmp/rand/rand_".$rand.".tmp");
		if(strtolower($_SESSION['checkCode']) != strtolower($code) || trim($code) == ""){
			die("验证码错误");
		}

		if(trim($username) == '' || trim($password) == '' ){
			die("用户名和密码均不能为空");
		}

		if(!checkUsername($username)){
			die("用户名不合法");
		}
		if(!checkUsername($password)){
			die("密码不合法");
		}
		$db= db(_openvpn_);

		if($db->where(array(_iuser_=>$username))->find()){
			
				die("用户名已经被注册了！换一个吧！");
		}else{
			$date[_iuser_] = $username;
			$date[_ipass_] = $password;
			$date[_maxll_] = $_dd_ll*1024*1024;
			$date[_isent_] = '0';
			$date[_recv_] = '0';
			$date[_i_] = $_dd_i;
			$date[_starttime_] = SYSTEM_T;
			$date[_endtime_] = SYSTEM_T+($_dd_t*24*60*60);
			$date["url"] = $_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"];//兼容7K最新版
			
			$endtime = $date[_endtime_];
			$date3=date('Y/m/d H:i:s',${endtime});
			$u=$username;
			$p=$password;
			$s=$_dd_i;
			$h=$_dd_ll;
			$arr = explode(",",_other_);
			foreach($arr as  $v){
				$date[$v] = "";
			}
			if($db->insert($date)){
				shell_exec("/bin/sh /vpnserver/cmd/UserCreate.sh {$u}");
				shell_exec("/bin/sh /vpnserver/cmd/UserPasswordSet.sh ${u} ${p}");
				shell_exec("/bin/sh /vpnserver/cmd/Access.sh ${u} ${s}");
				shell_exec("/bin/sh /vpnserver/cmd/UserExpiresSet.sh ${u} ${date3}");
				shell_exec("/bin/sh /vpnserver/cmd/ShellCreateCount.sh ${u} ${h}");
				
				$db = db('server');
				$list = $db->where(array())->order('id DESC')->select();
				foreach($list as $vo){
					$server=$vo['ip'];
					file_get_contents("http://${server}/shell.php?act=appreg&user=${u}&pass=${p}&access=${s}&maxll=${h}");
				}
				
				$_SESSION['code'] = '';
				@unlink("user_tmp/rand/rand_".$rand.".tmp");
				die("success");
			
			}else{
					die("无法注册用户 请检查数据库配置");
				
			}
			
		}