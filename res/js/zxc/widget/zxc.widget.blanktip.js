/**
 * menu
 */
ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("BlankTip");

ZXC.Widget.BlankTip = ZXC.Class({
name: "ZXC.Widget.BlankTip",
construct:
	function(target,tipInfo){
		var _this = this;
		this.target=jQuery(target);
		this.tipShow=false;
		this.tipInfo=tipInfo;
		if(this.target.val()==""){ //判断是否有默认内容 如果无默认内容 则 显示tipInfo
			this.target.val(tipInfo);
			this.target.addClass("blank_tip");
			this.tipShow=true;
		};
		this.target.focusin(function(event){
			_this.onFocus(event);
		})
		this.target.focusout(function(event){
			_this.onBlur(event);
		})
	},
methods:{
	onFocus:function(event){
		this.target.removeClass("blank_tip");
		if(this.tipShow==true){
			this.target.val("");
		};
		//this.resource("ddd");
	},
	onBlur:function(){
		if(this.target.val()==""){
			this.target.val(this.tipInfo);
			this.tipShow=true;
		this.target.addClass("blank_tip");
		}else{
			this.tipShow=false;
			this.target.removeClass("blank_tip");
		}
	}
},
statics: {
	register: function(tipInfo) {
		return this.each(function() {
				this.BlankTip = new ZXC.Widget.BlankTip(this,tipInfo);
			}
		);
	}
}
});

jQuery.fn.BlankTip = ZXC.Widget.BlankTip.register;