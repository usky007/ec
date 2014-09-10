/**
 * menu
 */
ZXC.Namespace("ZXC.Widget");

ZXC.Widget.Export("Broadcast");
 
ZXC.Widget.Broadcast = ZXC.Class({
name: "ZXC.Widget.Broadcast",
construct:
	function(listen_uri) {
		this.listen_uri = listen_uri;
		
		this.options = {
			poll : false,
			pollDelay : 0
		};
	
		this._first = true;
		this._pollEvent = function(first) {};
		this._handlers = new Array();
	
		this._messages = 0;
		this._messageQueue = '';
		this._queueMessages = 0;
		this._eventCore = $(document.createElement("div"));
	
		this.initialize(options);
	},
methods: {
	initialize: function(options) {
		 
	},
	listen:function(options) {
		 if(!this.listen_uri)
		 {
			 return false;
		 }
		 else
		 {
			 $.ajax({
		           beforeSend: function(xhr,setting) {
		               //xhr.setRequestHeader("If-None-Match", etag);
		               //xhr.setRequestHeader("If-Modified-Since","Sat, 29 Oct 1994 19:43:31 GMT");
		           },
		           contentType : 'text/plain',
		           url: this.listen_uri+'&salt='+Math.random()   ,
		           dataType: 'json',
		           type: 'get',
		           cache: 'false',
		           success: function(data, textStatus, xhr) {
		        	   this._listenSuccess(data);
		               /* Start the next long poll. */
		               this.listen(options);
		           },
		           error: function(xhr, textStatus, errorThrown) {
		               if(console.log)
		               {
		            	   console.log('Broadcast:'+ textStatus + ' | ' + errorThrown);
		               }
		           }
		       });
		 }
	},
	_listen:function(options) {
		 
	},
	_listenSuccess:function(data){
		
	}
 
 
},
statics: {
	 
}
});

 