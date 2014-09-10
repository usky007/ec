/**
 * menu
 */
ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("dotemplate");

ZXC.Widget.Dotemplate = ZXC.Class({
name: "ZXC.Widget.Dotemplate",
construct:
	function() {

	},
methods: {
	initialize: function() {

	},
	loadLoctions:function(loctions,div_id,rowcount) {
		var add_loc = function(locdata,div_id)
		{
			var tmp = $('#modalLocation-box-template').html();
			var pagefn = doT.template(tmp, undefined, {});
			var locbox = pagefn(locdata)
			$('#'+div_id).html($('#'+div_id).html()+locbox)  ;
		}

		if(!rowcount)
			rowcount = 4;
		for(var key in loctions){
			var loc = loctions[key];
			if(key % rowcount==0)
				loc.firstcolumn = 'first';
			if(key<rowcount)
				loc.firstrow = ' first-row';

			var html = this.transform(loc,'modalLocation-box-template',{});
			$('#'+div_id).html($('#'+div_id).html()+html)  ;
	        //add_loc(loc,'recommendLoc_result_box');
	    }
	},
	transform:function(data,tempid,def)
	{
		var tmp = $('#'+tempid).html();
		var pagefn = doT.template(tmp, undefined, def);
		return pagefn(data);
	}

},
statics: {

}
});

