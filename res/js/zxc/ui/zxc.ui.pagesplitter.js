/**
 * page splitter
 */
ZXC.Require("ZXC.util");
ZXC.Require("ZXC.Resource");

ZXC.Namespace("ZXC.UI");

ZXC.UI.Export("PageSplitter");

ZXC.UI.PageSplitter = ZXC.Class({
name: "ZXC.UI.PageSplitter",
construct:
	function(elem, options) {
		this.elem = elem;

		this.options = {
			total: 0,
			page_size: 20,
			page_num_next: 5,
			show_in_table: false,
			tplFn: ZXC.UI.PageSplitter.getTemplate,
			images: [null, null, null, null],
			gotoFnName: null,
			gotoFn: null,
			caller: null
		}

		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		this.options.total = options.total;
		if (options.page_size)
			this.options.page_size = options.page_size;
		if (options.page_num_next)
			this.options.page_num_next = options.page_num_next;
		if (typeof options.show_in_table != "undefined")
			this.options.show_in_table = options.show_in_table;
		if (options.tplFn)
			this.options.tplFn = options.tplFn;
		this.options.imgages = options.images;
		if (typeof options.gotoFnName != "undefined")
			this.options.gotoFnName = options.gotoFnName;
		if (typeof options.gotoFn != "undefined")
			this.options.gotoFn = options.gotoFn;
		if (typeof options.caller != "undefined")
			this.options.caller = options.caller;
	},
	show: function(page) {
		var str = '';
    	var t = Math.ceil(this.options.total / this.options.page_size);
		if (t <= 1) {
			$(this.elem).each(function(){
				$(this).html('');
			});
			return;
		}

		if (page > t)
			page = t;
		if (page <= 0)
			page = 1;

		var strTdTxt = '';
		var strTdImg = '';
		var strTdEnd = '';
		if (this.options.show_in_table)
		{
			str += '<table><tr>';
			strTdTxt = '<td class="pstdtxt">';
			strTdImg = '<td class="pstdimg">';
			strTdEnd = '</td>';
		}
		//第一页没有被显示
		str += strTdImg;
		if (page - this.options.page_num_next > 1)
			str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_FIRST_INDEX, 1, this.options.imgages[ZXC.UI.PageSplitter.IMAGE_FIRST_INDEX]);
		str += strTdEnd;
		str += strTdImg;
		if (page > 1)
			str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_PREV_INDEX, page - 1, this.options.imgages[ZXC.UI.PageSplitter.IMAGE_PREV_INDEX]);
		str += strTdEnd;
		var iStart = page - this.options.page_num_next;
		var iEnd = page + this.options.page_num_next;
		var start = page - this.options.page_num_next > 1 ? page - this.options.page_num_next : 1;
		var end = page + this.options.page_num_next < t ? page + this.options.page_num_next : t;
		var idx = start;
		for (var k = iStart; k <= iEnd; k++)
		{
			str += strTdTxt;
			if (k >= start && k <= end)
			{
				if (k == page)
					str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_CURRENT_INDEX, k, '');
				else
					str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_NORMAL_INDEX, k, '');
			}
			str += strTdEnd;
		}
		str += strTdImg;
		if (page < t)
			str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_NEXT_INDEX, page + 1, this.options.imgages[ZXC.UI.PageSplitter.IMAGE_NEXT_INDEX]);
		str += strTdEnd;
		str += strTdImg;
		if (page + this.options.page_num_next < t)
			str += this.options.tplFn(ZXC.UI.PageSplitter.TEMPLATE_LAST_INDEX, t, this.options.imgages[ZXC.UI.PageSplitter.IMAGE_LAST_INDEX]);
		str += strTdEnd;
		if (this.options.show_in_table)
		{
			str += '</tr></table>';
		}
		ZXC.util.debugInfo(str.replace(/</g,"&lt;").replace(/>/g,"&gt;"));
		$(this.elem).each(function(){
			$(this).html(str);
		});
		var psobj = this;
		$("[obj='goToPage']").each(function(){
			$(this).click(function(evt){
				evt.preventDefault();
				if (psobj.options.gotoFn)
					psobj.options.gotoFn($(this).attr("page"), psobj.options.caller);
				else
					eval(psobj.options.gotoFnName + "(" + $(this).attr("page") + ");");
			});
		});
	}
},
statics: {
	TEMPLATE_FIRST_INDEX: 0,
	TEMPLATE_PREV_INDEX: 1,
	TEMPLATE_NORMAL_INDEX: 2,
	TEMPLATE_CURRENT_INDEX: 3,
	TEMPLATE_NEXT_INDEX: 4,
	TEMPLATE_LAST_INDEX: 5,
	IMAGE_FIRST_INDEX: 0,
	IMAGE_PREV_INDEX: 1,
	IMAGE_NEXT_INDEX: 2,
	IMAGE_LAST_INDEX: 3,
	getTemplate: function(type, page, image) {
		switch(type) {
			case ZXC.UI.PageSplitter.TEMPLATE_FIRST_INDEX:
				return '<span class="pager_first"><a href="#" obj="goToPage" page="' + page + '"><img title="第一页" src="' + image + '" /></a></span>';
			case ZXC.UI.PageSplitter.TEMPLATE_PREV_INDEX:
				return '<span class="pager_pre"><a href="#" obj="goToPage" page="' + page + '"><img title="第一页" src="' + image + '" /></a></span>';
			case ZXC.UI.PageSplitter.TEMPLATE_NORMAL_INDEX:
				return '<span class="pager_unit"><a href="#" obj="goToPage" page="' + page + '"><span>' + page + '</span></a></span>';
			case ZXC.UI.PageSplitter.TEMPLATE_CURRENT_INDEX:
				return '<span class="pager_cur">' + page + '</span>';
			case ZXC.UI.PageSplitter.TEMPLATE_NEXT_INDEX:
				return '<span class="pager_next"><a href="#" obj="goToPage" page="' + page + '"><img title="下一页" src="' + image + '" /></a></span>';
			case ZXC.UI.PageSplitter.TEMPLATE_LAST_INDEX:
				return '<span class="pager_last"><a href="#" obj="goToPage" page="' + page + '"><img title="最后一页" src="' + image + '" /></a></span>';
		}
	}
}
});