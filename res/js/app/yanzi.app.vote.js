ZXC.Require("ZXC.util");
ZXC.Namespace("Yanzi.App");
Yanzi.App.Export("Vote");

ZXC.Class({
	name: "Yanzi.App.Vote",
	construct:
		function (options) {
			this.initialize();
			this.result = true;
			this.tally = new Array();
			this.votekey = $('.votekeyishere').attr('key');
			var arrCols = new Array();
			$.each($('.col_area'), function(k, v){
				arrCols.push($(v).attr('col'));
				$(v).addClass($(v).attr('col'));
			});
			this.Columns = arrCols;
			var _this = this;
			$.ajax({
				type: "POST",
				url:js_context.base_url+'ajax/vote/get_setting',
				data: {'votekey': this.votekey, 'columns': this.Columns.join(',')},
				dataType: 'json',
				success: function(msg){
					if(msg.success)
					{
						_this.verify = msg.json;
						_this.timecheck();

						if(msg.selected != undefined)
						{
							$.each(msg.selected, function(k, v){
								$.each(v, function(dk, dv){
									$('.' + k + ' input').each(function(ik, iv){
										if(iv.value == dv.option){
											if(iv.value == 'others')
											{
												//for others
												iv.checked = true;										}
											else
											{
												iv.checked = true;
											}
										}
									});
								});
								
							});
						}
					}

				},
				error: function(msg){
					alert('出错了');
					console.log(msg);
				}
			});
			
		},
	methods: {
		initialize: function() {
		},
		timecheck:function() {
			var _this = this;
			var now = (new Date()).getTime()/1000;
			//console.log(now);
			var rst = true;
			if(now < _this.verify.time.starttime)
			{
				alert('投票尚未开始');
				rst = false;
			}
			else if( _this.verify.time.endtime != 0){
				if(now > _this.verify.time.endtime)
				{
					alert('投票已经结束');
					rst = false;
				}
			}
			
			return rst;
		},
		submit:function(event)
		{
			event.preventDefault();
			var _this = this;
			console.log(_this.verify);
			if(!_this.timecheck())
			{
				return;
			}
			
			var error = new Array();
			var data = new Array();
			$('.col_area').each(function(k, v){
				var col_class = $(v).attr('col');
				$('.error_' + col_class).html('');
				$('.error_area').hide();

				var col_data = new Array();
				$('.' + col_class + ' input').each(function(ik, iv){
					if(iv.checked){
						if(iv.value == 'others')
						{
							//for others
							var op = '{"option" : "' + iv.value + '"}';
							col_data.push(op);
						}
						else
						{
							var op = '{"option" : "' + iv.value + '"}';
							col_data.push(op);
						}
					}
				});

				//check code here
				if(_this.verify.col_setting != undefined){
					var min = _this.verify.col_setting[col_class].min;
					var max = _this.verify.col_setting[col_class].max;

					if(min == 0 && max == 0)
					{
						//do nothing
					}
					else
					{
						var actual = col_data.length;
						if(actual < min)
						{
							error.push({'col': col_class, 'msg' : '数量不能少于 ' + min + ' 个'});
						}
						else if(actual > max && max != 0)
						{
							error.push({'col': col_class, 'msg' : '数量不能多于 ' + max + ' 个'});						
						}
					}
				}
				else{
					var actual = col_data.length;
					if(actual == 0)
					{
						error.push({'col': col_class, 'msg' : '请至少选择 1 个选项'});						
					}
				}
				data.push('"' + col_class + '":[' + col_data.join(',') + ']');
			});

			console.log(data);
			console.log(error);

			if(error.length > 0)
			{
				$.each(error, function(k, v){
					$('.error_' + v.col).html(v.msg);					
				});
				$('.error_area').show();
			}
			else
			{
				data = '{' + data.join(',') + '}';
				var type = $('.vote_submit').attr('status');
				if(type == 'end')
				{
					_this._setvote(data);
				}
				else if(type == 'next')
				{
					_this._settmp(data, $('.vote_submit').attr('href'));
				}
			}
		},
		_setError: function(errors){
			$.each(errors, function(k, v){
					$('.error_' + v.col).html(v.msg);					
				});
		},
		_ajaxDone: function(msg) {
			if(msg.errorCode != 'success')
        	{
        		if(msg.msg.type == 'time')
        		{

        		}
        		else if(msg.msg.type == 'col'){
        			var error = new Array();
        			$.each(msg.msg.msg, function(k, v){
        				error.push({'col': k, 'msg' : v});
        			});
					_this._setError(error);
        		}
        		return false;						
        	}
        	else
        		return true;
		},
		_settmp: function(vote, next)
		{
			_this = this;
			$.ajax({
				type: "POST",
				url:js_context.base_url+'ajax/vote/settmp',
				data: { 'data': vote,
					    'votekey': _this.votekey}, //"data="+_this.verifyarray+"&votekey="+_this.votekey+"&userkey="+_this.userkey,
				dataType: 'json',
				success: function(msg){
				    if(msg.success)
			        {			        	
			        	if(_this._ajaxDone(msg)) {
				    	    window.location.href = next;
			        	}
			        }
				    else
				     {
				    	 $('#message').empty();
				    	 $('#message').append(msg.message);
				    	 _this.result = false;
				     }
				}
			});
		},
		_setvote:function(vote)
		{
			_this = this;
			var sharemsg = $('.vote_share_message').html();
			sharemsg = sharemsg == null ? '' : sharemsg;
			$.ajax({
				type: "POST",
				url:js_context.base_url+'ajax/vote/setvote',
				data: { 'data': vote,
					    'votekey': _this.votekey,
					    'sharemsg': sharemsg
					    }, //"data="+_this.verifyarray+"&votekey="+_this.votekey+"&userkey="+_this.userkey,
				dataType: 'json',
				success: function(msg){
				    if(msg.success)
			        {
			        	if(_this._ajaxDone(msg)) {
				    		alert('投票成功！');
				    		if(msg.sharemsg != null)
				    			window.location = 'http://service.weibo.com/share/share.php?title=' + msg.sharemsg;
			        	}
			        }
				    else
				     {
				    	 $('#message').empty();
				    	 $('#message').append(msg.message);
				    	 _this.result = false;
				     }
				}
			});
		},
	},
	statics: {
		instance: (function() {
			var _inst = null;
			return function(options) {
				if (!_inst) {
					_inst = new this(options);
				}
				return _inst;
			};
		})()
	}

});

Page.onPageLoad.add(function(){
	ZXC.Import("Yanzi.App.Vote");
	ZXC.util.bind(Vote.instance(), "Vote","event");
});


