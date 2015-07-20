<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>blakeFez的聊天室</title>
	<link rel="stylesheet" href="static/css/style.css" type="text/css"/>
	<link rel="stylesheet" href="static/css/bootstrap.min.css">
	<script type="text/javascript" src="static/js/jquery.js"></script>
	<script type="text/javascript" src="static/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="static/js/jquery.websocket.js"></script>
</head>
<body>
	<div class="content">
		<div class="message"></div>
		<div class="tool">
			<span class="empty">清空记录</span>
		</div>
		<div class="send">
			<textarea class="chat form-control" name="chat"></textarea>
			<p><input type="submit" class="submit btn" name="submit" value="发送" /></p>
		</div>
		<div class="list">
			<h3>在线用户<strong class="online">0</strong></h3>
			<ul>
			</ul>
		</div>
	</div>
	
	<button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#myModal" style="display:none">
	   设置昵称
	</button>
	
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	   <div class="modal-dialog">
		  <div class="modal-content">
			 <div class="modal-header">
				<button type="button" class="close"  data-dismiss="modal" aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title" id="myModalLabel">
				   第一次进入，请设置您的昵称
				</h4>
			 </div>
			 <div class="modal-body">
				<input type="text" placeholder="输入您的昵称" class="form-control" id="nickName">
			 </div>
			 <div class="alert alert-warning" style="display:none">
				<a href="#" class="close" data-dismiss="closealert">
					&times;
				</a>
			   <strong>抱歉，该昵称已被占用！</strong>
			</div>
			 <div class="modal-footer">
				<button type="button" class="btn" onclick="setNickName()">
				   提交
				</button>
			 </div>
		  </div>
		</div>
	</div>

	<script type="text/javascript">
		var number = '<?php echo $user["number"];?>';
		var nickName = '<?php echo $user["nickName"];?>';
		var tryTime = 0;
		$(function(){
			var t = $('.message');
			$.wsmessage('msg', function(data){
				t.append(data);
				$('.message').animate({scrollTop: $('.message')[0].scrollHeight} ,0);
			});
			
			$.wsmessage('chat', function(data){
				t.append(data);
				$('.message').animate({scrollTop: $('.message')[0].scrollHeight} ,0);
			});
			
			$.wsmessage('name', function(data){
				if(data){
					t.html('');
				}
			});
			
			$.wsmessage('error', function(data){
				setTimeout(closeWin,6000);
			});
			
			$.wsmessage('list', function(data){
				if(!data){
					return false;
				}
				$.each(data, function(k, v){
					if(v[1]){
						var w = $('<li>' + v[0] + '</li>').click(function(){
							$('.send .chat').val('@' + v[0] + ' ');
						});
						$('.list ul').append(w);
					} else{
						$(".list ul li").each(function(){
							if($(this).html() == v[0]){
								$(this).remove();
								return false;
							}
						});
					}
				});
				$('.online').html($('.list ul li').size());
			});
			$.wsclose(function(data){
				$(".list ul li").html('');
				$('.online').html(0);
				t.append('<div class="msg info">连接已断开, 6秒后自动重试</div>');
			});
			$.wsopen(function(data){
				t.append('<div class="msg info">连接服务器成功</div>');
			});
			$('.send .submit').click(function(){
				if($('.send .chat').val()){
					$.wssend($.param({chat : $('.send .chat').val()}));
					$('.send .chat').val('');
				}
				return false;
			});
			$('.send  .chat').keydown(function(e){
				if(e.keyCode == 13){
					$('.send .submit').click();
					return false;
				}
			});
			$('.tool .empty').click(function(){
				t.html('');
			});
			$('[data-dismiss="closealert"]').click(function(){
				$(this).parent().hide();
			});
			//连接服务器
			linkServer();
		});
		
		function linkServer(){
			if(tryTime > 10){
				closeWin();
				return;
			}
			if(nickName){
			//建立连接
				$.ws.link();
				setTimeout(sentName,500);
			}else{
				$('[data-toggle="modal"]').click();
			}
		};
		function sentName(){
			if($.ws.status){
				tryTime = 0;
				$.wssend('name=' + nickName + '&number=' + nickName);
			}else{
				setTimeout(sentName,500);
			}
		};
		function closeWin(){
			var userAgent = navigator.userAgent;
			if (userAgent.indexOf("Firefox") != -1 || userAgent.indexOf("Chrome") !=-1) {
				window.location.href="about:blank";
			}else{
				window.opener = null;
				window.open("", "_self");
				window.close();
			}
		}
		function setNickName(){
			var name = $('#nickName').val();
			$.ajax({
				url: 'index.php?c=chat&a=setNickName',
				type: 'post',
				timeout: 30000,
				data: {name:name},
				dataType: 'json',
				success:function(result){
					if(result.flag){
						$('.alert.alert-warning').find('strong').html('设置成功！');
						$('.alert.alert-warning').show();
						setTimeout("$('.close').click()",1000);
						nickName = name;
						linkServer();
					}else{
						$('.alert.alert-warning').find('strong').html(result.msg);
						$('.alert.alert-warning').show();
					}
				},
				error:function(){
					$('.alert.alert-warning').find('strong').html('网络异常，请稍后重试！');
					$('.alert.alert-warning').show();
				}
			});
		}
	</script>
</body>
</html>