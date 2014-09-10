/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Ajax message queue implement, derived from demo AMQ Ajax handler.
 * This class provides the main API for using the Ajax features of AMQ. It
 * allows JMS messages to be sent and received from javascript when used
 * with the org.apache.activemq.web.MessageListenerServlet
 *
 * Supportted options including:
 * poll: 	Set to true if waiting poll for messages is needed. Default false;
 * pollDelay: Poll delay. If set to positive integer, this is the time to wait
 *            in ms before sending the next poll after the last completes.
 *            Default 0;
 *
 * Author: Tianium
 */
ZXC.Namespace("ZXC.MQ");

ZXC.MQ.Export("AMQ");

ZXC.MQ.AMQ = ZXC.Class({
name: "ZXC.MQ.AMQ",
construct:
	function (uri, options) {
		// The URI of the MessageListenerServlet
		this.uri = !uri ? js_context.base_url + 'service/mq/ajax' : uri;

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
	initialize : function(options) {
		for (key in this.options) {
			if (options[key] != undefined)
				this.options[key] = options[key];
		}
	},
	// Send a JMS message to a destination (eg topic://MY.TOPIC).  Message should be xml or encoded
  	// xml content.
	sendMessage : function(destination,message) {
		this._sendMessage(destination,message,'send');
	},
	// Listen on a channel or topic.   handler must be a function taking a message arguement
  	addListener : function(id,destination,handler) {
	    this._handlers[id]=handler;
	    this._sendMessage(destination,id,'listen');
  	},
  	// remove Listener from channel or topic.
  	removeListener : function(id,destination) {
    	delete this._handlers[id];
    	this._sendMessage(destination,id,'unlisten');
  	},
	// Add a function that gets called on every poll response, after all received
  	// messages have been handled.  The poll handler is past a boolean that indicates
  	// if this is the first poll for the page.
	addPollHandler : function(func) {
	  var old = this._pollEvent;
	  this._pollEvent = function(first)
	  {
	    old(first);
	    func(first);
	  }
	},
	startBatch: function() {
    	this._queueMessages++;
  	},
	endBatch: function() {
	    this._queueMessages--;
	    if (this._queueMessages==0 && this._messages>0)
	    {
	    	var obj = this;
	      	var body = this._messageQueue;
	      	this._messageQueue='';
	      	this._messages=0;
	      	this._queueMessages++;
	      	ZXC.util.jQueryAjaxHelper({
	      		url: obj.uri, type: 'POST', data: body,
	      		error: function(){},
				complete: function(){ obj.endBatch(); }
			});
	    }
  	},
  	startPolling : function() {
  		if (this.options.poll)
      		this._sendPoll(true);
  	},
  	addEventHandler : function(name, func) {
  		this._eventCore.bind(name, func);
  	},
	_sendMessage : function(destination,message,type) {
	    if (this._queueMessages>0) {
	      	if (this._messages==0)
	      	{
	        	this._messageQueue='destination='+destination+'&message='+message+'&type='+type;
	      	}
	      	else
	      	{
	        	this._messageQueue+='&d'+amq._messages+'='+destination+'&m'+amq._messages+'='+message+'&t'+amq._messages+'='+type;
	      	}
	      	this._messages++;
	    } else {
	      	var obj = this;
	      	this.startBatch();
	      	ZXC.util.jQueryAjaxHelper({
	      		url: obj.uri, type: 'POST',
				data: 'destination='+destination+'&message='+message+'&type='+type,
				error: function(){},
				complete: function(){ obj.endBatch(); }
			});
	    }
  	},
	_sendPoll: function(nowait) {
  		var obj = this;
  		var success = false;
  		obj._eventCore.trigger("onPollStart");
  		ZXC.util.jQueryAjaxHelper({
			url: obj.uri + (nowait ? "/0" : ""), type: 'GET',
			error: function(){},
			success: function(data){
				success = true;
				obj.startBatch();
				obj._messageHandler(data);
				obj._pollEvent(obj._first);
				obj._first=false;
			},
			complete: function() {
				obj._eventCore.trigger("onPollEnd");
				if (!success) return;

				obj.endBatch();
				if (obj.options.pollDelay>0)
					setTimeout(function(){obj._sendPoll();}, obj.options.pollDelay);
				else
					obj._sendPoll();
	     	}
		});
  	},
	_messageHandler: function(data) {
	    var response = data.getElementsByTagName("ajax-response");
	    if (response != null && response.length == 1) {
          	for ( var i = 0 ; i < response[0].childNodes.length ; i++) {
	            var responseElement = response[0].childNodes[i];

	            // only process nodes of type element.....
	            if ( responseElement.nodeType != 1 )
	              continue;

	            var id   = responseElement.getAttribute('id');
	            var handler = this._handlers[id];
	            if (handler) {
	              	for (var j = 0; j < responseElement.childNodes.length; j++) {
	                	handler(responseElement.childNodes[j]);
		      		}
	            }
          	}
        }
  	}
}
});