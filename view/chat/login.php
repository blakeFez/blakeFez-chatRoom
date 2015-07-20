<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>登录</title>
	<link rel="stylesheet" href="static/css/bootstrap.min.css">
	<link rel="stylesheet" href="static/css/index.css">
	<script src="static/js/jquery.js"></script>
	<script type="text/javascript" src="static/js/bootstrap.min.js"></script>
</head>
<body>
	<?php if($browser){ ?>
		<p style="color: #c7254e; background: #f9f2f4; margin: 0; padding: 10px 0; font-size: 14px; text-align: center">请使用Chrome或者Firefox打开该聊天室！</p>
	<?php } ?>
    <div class="container-full">
		<div style="position: relative;">
			<div class="userIcon">
				<span class="glyphicon glyphicon-user"></span>
			</div>
			<div class="auth-input">
				<div class="auth-span">
					<input class="form-control" type="text" placeholder="工号" id="number"/>
				</div>
				<div class="auth-span">
					<input class="form-control" type="text" placeholder="输入验证码" id="auth-code" disabled />
				</div>
				<div class="auth-span">
					<button class='btn' id="getAuthCode" disabled>获取验证码</button>
					<button class='btn' id="login" disabled>登录</button>
				</div>
			</div>
		</div>
	</div>
	
	<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" style="display:none">
	   告警
	</button>
	
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	   <div class="modal-dialog">
		  <div class="modal-content">
			 <div class="modal-header">
				<button type="button" class="close"  data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel">
				   提示
				</h4>
			 </div>
			 <div class="alert alert-warning">
			   <strong id='alertmsg'></strong>
			</div>
		  </div>
		</div>
	</div>
	
	<script>
		var timer;
		$(function(){
			$('#number').keyup(function(event){
				var e = event;
				if(e.keyCode == 13 && $(this).val()){
					$('#getAuthCode').trigger('click');
					return;
				}
				if($(this).val()){
					$('#getAuthCode').attr('disabled',false);
				}else{
					$('#getAuthCode').attr('disabled',true);
				}
			});
			
			$('#auth-code').keyup(function(event){
				var e = event;
				if(e.keyCode == 13 && $(this).val()){
					$('#login').trigger('click');
					return;
				}
				if($(this).val()){
					$('#login').attr('disabled',false);
				}else{
					$('#login').attr('disabled',true);
				}
			});
			
			$('#getAuthCode').click(function(){
				$(this).attr('disabled',true);
				var number = $('#number').val();
				if(number.toLowerCase().indexOf('gz') != 0){
					$(this).attr('disabled',false);
					altermsg('工号不正确！');
					return;
				}
				
				$.ajax({
					url: 'index.php?c=chat&a=getAutoCode',
					type: 'post',
					timeout: 30000,
					data: {number:number},
					dataType: 'json',
					success:function(result){
						if(result.flag){
							altermsg('验证码已经发送到您的企业qq，请确认！');
							$('#auth-code').attr('disabled',false);
						}else{
							altermsg('验证码发送失败，请稍后重试！');
						}
					},
					error:function(){
						altermsg('验证码发送失败，请稍后重试！');
					}
				});
				refreshTime(60000);
			});
			
			$('#login').click(function(){
				$(this).attr('disabled',true);
				var number = $('#number').val(),
					authCode = $('#auth-code').val();
				$.ajax({
					url: 'index.php?c=chat&a=checkAuthCode',
					type: 'post',
					timeout: 30000,
					data: {number:number,authCode:authCode},
					dataType: 'json',
					success:function(result){
						if(result.flag){
							location.href = 'index.php?c=chat&a=index';
						}else{
							altermsg('验证码错误！');
							$('#login').attr('disabled',false);
						}
					},
					error:function(){
						altermsg('网络出错，请稍后重试！');
						$('#login').attr('disabled',false);
					}
				});
			});
		});
		
		function refreshTime(time){
			timer = setInterval(function(){
				time -= 1000;
				if(time > 0){
					var date = new Date(time);
					$("#getAuthCode").html(date.getSeconds()+'秒后重试');
				}else{
					clearInterval(timer)
					$("#getAuthCode").html('获取验证码');
					$("#getAuthCode").attr('disabled',false);
				}
			},1000);
		}
		function altermsg(msg){
			$('#alertmsg').html(msg);
			$('[data-toggle="modal"]').click();
		}
	</script>
</body>
</html>