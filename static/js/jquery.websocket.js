WebSocket = window.WebSocket || window.MozWebSocket;
$(function($){
	$.ws ={obg:{}, message:{}, open:[], close:[], status: false, link:function(){}};
	
	// 添加消息回调函数 1 参数 消息 keys 2参数 回调函数
	$.wsmessage = function(k, f){
		if(!k || !$.isFunction(f)){
			return false;
		}
		$.ws.message[k] = $.ws.message[k] || [];
		$.ws.message[k].push(f);
	};
	
	// 注册 打开回调函数
	$.wsopen = function(f){
		if(!$.isFunction(f)){
			return false;
		}
		$.ws.open.push(f);
	};
	
	// 注册 关闭回调函数
	$.wsclose = function(f){
		if(!$.isFunction(f)){
			return false;
		}
		$.ws.close.push(f);
	};
	
	// 注册 发送信息
	$.wssend = function(d){
		return $.ws.status && $.ws.obg.send(d);
	};
	
	$.ws.link = function(){
		$.ws.obg = new WebSocket('ws://10.1.102.56:843/');
		
		// 打开
		$.ws.obg.onopen = function(){
			$.ws.status = true;
			$.each($.ws.open,function(k, v){
				v.call(this);
			})
		};
		
		// 关闭
		$.ws.obg.onclose = function(){
			$.ws.status = false;
			$.each($.ws.close,function(k, v){
				v.call(this);
			})
		};
		
		// 接收消息
		$.ws.obg.onmessage = function(msg){
			var d = $.parseJSON(msg.data);
			d = d || [];
			$.each(d,function(k, v){
				$.ws.message[k] = $.ws.message[k] || [];
				$.each($.ws.message[k],function(kk, vv){
					vv.call(this, v);
				})
			})
		};
	};
	
	//关闭自动重新连接
	$.wsclose(function(){
		tryTime++;
		setTimeout(linkServer, 6000);
	});
	
	
	//定时呼吸time
	$.wsopen(function(){
		$.ws.time = setInterval(function(){$.wssend("time=ture");}, 30000);
	});
	$.wsclose(function(){
		$.ws.time && clearInterval($.ws.time);
	});
});