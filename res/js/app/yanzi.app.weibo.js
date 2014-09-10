ZXC.Require("ZXC.util");
ZXC.Namespace("YANZI.App");

YANZI.App.Export("Weibo");


 
var warning_empty = function(jqy_elem)
{
	alert('请输入内容!');
	jqy_elem.focus();
}


ZXC.Class({
name: "YANZI.App.Weibo",
construct:
	function () {
	 
		this.comments = false;
		
		ZXC.util.bind(this, $(".tform"), "submit");
		$(".tform").attr("op", "");
		ZXC.util.bind(this, $("body"), "click");
		//$.skygqbox.hide();
	},
methods: {
	face : function(event)
	{
		var _this = event.currentTarget;
		var setDiv = $("#"+$(_this).attr("facediv"));
		
		if(setDiv[0].style.display=='none')
		{
			setDiv.html($('#div_face').html());
			//ZXC.util.bind( this, $(".facebtn"), "click");
			$(".facebtn").attr('txtid',$(_this).attr("txtid"));
			$(".facebtn").bind("click", this.addface);
			setDiv.show();
		}
		else
		{
			setDiv.html('');
			setDiv.hide();
		}
	},
	addface : function(event){
		var facelistdiv = $(event.currentTarget).parents().find(".facediv");
		var facebtn = event.currentTarget;
		var ipttxt = $('#'+$(facebtn).attr('txtid'));
		var facetxt = '['+$(facebtn).attr('title')+']';
		ipttxt.val(ipttxt.val()+facetxt);
		facelistdiv.hide();
	},
	newpost : function(event)
	{
		this.iniNewWeiboDlg();
		$('#newtwitter').skygqbox({
			title: '发新微博'
		});
		//this.block = $(elem);
	},
	post : function(event) {
		var _this = this;
		event.preventDefault();
		if($("#newtwittertext").val().trim()=="")
		{
			warning_empty($("#newtwittertext"));
			return false;
		}
		var newpostform =$("#newform");
		var info = ZXC.util.buildFormData(newpostform);
		//_this.block.find("input[type=submit]").hide();
		var loading = newpostform.find('.loading').html('<img src="'+js_context.res_url+'/images/loading_view.gif">').show();
		ZXC.util.jQueryAjaxHelper({
			url : info.url,
			type: info.method,
			data: info.data,
			dataType: 'json',
			
			success: function(data){
				if (!data.success) {
					throw new Error(data.message);
				}
				$("#posts").prepend(data.status);
				$(window).trigger('smartresize');
				$.skygqbox.hide();
				_this.block.find("input[type=submit]").show();
				$('#successdialog').skygqbox({
					autoClose: 2000
				});
			}
		});
	},

	newrepost : function(event)
	{
		var _this = event.currentTarget;
		var ftext = $(_this).attr('ft');
		var sid = $(_this).attr('id');
		$('#forwardtwittertext').html(ftext);
		$('#fwtwitter').skygqbox({
			title: '转发微博'
		});
		$('#forwardsid').val(sid);
		 
		$('#forwardtwittertext').focus();
	}, 
	repost : function(event) {
		var _this = this;
		event.preventDefault();
		
		var repostform =$("#forwardForm");
		var info = ZXC.util.buildFormData(repostform);
		
		ZXC.util.jQueryAjaxHelper({
			url : info.url,
			type: info.method,
			data: info.data,
			dataType: 'json',
			success: function(data){
				if (!data.success) {
					throw new Error(data.message);
				}
				$("#posts").prepend(data.status);
				$(window).trigger('smartresize');
				$.skygqbox.hide();
				
				$('#successdialog').skygqbox({
					autoClose: 2000
				});
			}
		});
	},
	
	iniNewWeiboDlg : function(){
	$("#newtwittertext").val('');
	$("#hidpicurl").val('');
	
	$("#div_uploadpic").hide();
	
	$("#twitterit").show();
	$(".loading").hide();
	$('#newtwittertext').focus();
	$('#div_face').hide();
	},
	
	show :function(event){
		this.block.skygqbox();
	},
	hide :function(event){
		$.skygqbox.hide();
	}
	
}
	
});