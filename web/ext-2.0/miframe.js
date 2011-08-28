/*
 * @class Ext.ux.ManagedIFrame 
 * Version:  0.4
 * Author: Doug Hendricks 10/2007 doug[always-At]theactivegroup.com
 *
 *
 * <p> An Ext harness for iframe elements.  
  
  Adds Ext.UpdateManager(Updater) support and a compatible 'update' method for 
  writing content directly into an iFrames' document structure.
  
  * Usage:<br>
   * <pre><code>
   * // Harness it from an existing Iframe from markup
   * var i = new Ext.ux.ManagedIFrame("myIframe");
   * // Replace the iFrames document structure with the response from the requested URL.
   * i.load("http://myserver.com/index.php", "param1=1&amp;param2=2");
   * //Notes:  this is not the same as setting the Iframes src property !
   * // Content loaded in this fashion does not share the same document namespaces as it's parent --
   * // meaning, there (by default) will be no Ext namespace defined in it since the document is
   * // overwritten after each call to the update method, and no styleSheets.
  * </code></pre>
  * <br>
   * @cfg {Boolean/Object} autoCreate True to auto generate the IFRAME element, or a {@link Ext.DomHelper} config of the IFRAME to create
   * @cfg {String} html Any markup to be applied to the IFRAME's document content when rendered.
    * @constructor
    * Create new Updater directly.
    * @param {Mixed} el, Config object The iframe element or it's id to harness or a valid config object.
    * @param {Config} forceNew (optional) By default the constructor checks to see if the passed element already has an Updater and if it does it returns the same instance. This will skip that check (useful for extending this class).
 */
 

 Ext.ux.ManagedIFrame = function(){
    		
    	var args=Array.prototype.slice.call(arguments, 0)
    	    ,el = Ext.get(args[0])
    	    ,config = args[0];
    	
    	if(el && el.dom && el.dom.tagName == 'IFRAME'){
    	    config = args[1] || {};
    	}else{
    	   config = args[0] || args[1] || {};
    	   el = config.autoCreate?
    	  	Ext.get(Ext.DomHelper.append(document.body, Ext.apply({tag:'iframe', src:(Ext.isIE&&Ext.isSecure)?Ext.SSL_SECURE_URL:''},config.autoCreate))):null;
    	} 
    	
    	 if(!el || el.dom.tagName != 'IFRAME') return el;
    	 
    	 !!el.dom.name.length || (el.dom.name = el.dom.id); //make sure there is a valid frame name
    	 
         this.addEvents({
    	 	        	 		  
    		   /**
    		     * @event documentloaded
    		     * Fires when the iFrame's Document(DOM) has reach a state where the DOM may be manipulated
    		     * @param {Ext.ux.ManagedIFrame} this
    		     */
    		  "documentloaded" : true
    
    	  });
             
         if(config.listeners){
             	this.listeners=config.listeners;
             	Ext.ux.ManagedIFrame.superclass.constructor.call(this);
          }
            
         Ext.apply(el,this);  // apply this class interface ( pseudo Decorator )
         
         el.addClass('x-managed-iframe');    
         
         var content = config.html || config.content || false;
    	 	  
    	 if(content){
    	         el.update.defer(100,el,[content]);//allow the iframe to quiesce for Gecko
               }
             
         return el;
    	
    };	       
 
    Ext.extend(Ext.ux.ManagedIFrame , Ext.util.Observable,
      	{ 
      	
      	/*
	  Write(replacing) string content into the IFrames document structure
	 * @param {String} content The new content
         * @param {Boolean} loadScripts (optional) true to also render and process embedded scripts
         * @param {Function} callback (optional) Callback when update is complete. 
    	*/
    	update : function(content,loadScripts,callback){
    	
    	       
    	       loadScripts = loadScripts || this.getUpdateManager().loadScripts || false;
        		
    		this._windowContext = null;
    		content = Ext.DomHelper.markup(content||'');
                             
    		var doc = this.getDocument();
    		if(doc){
    		  doc.open();
    
    		  doc.write(loadScripts===true?
    		       content:content.replace(/(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)/ig, ""));
    		  
    		  //create an 'eval'able context for the iframe and this.execScript
    		  doc.write ('<script type="text/javascript">(function(){'+
    		           "var MSIE/*@cc_on =1@*/;"+ // IE sniff
    		           "parent.Ext.get('"+this.dom.id +"')._windowContext=MSIE?this:{eval:function(s){return eval(s);}}"+
       			   "})();<\/script>" );
        			   
        		   
    		  doc.close();
    
    		  if(!!content.length){
    		     this.checkDOM(false,callback); 
    		  } else if(callback){
    		  	callback();
    		  }
    		}
    
    		return this;
    	},
    	_windowContext : null,
    	/*
	  Return the Iframes document object
    	*/
    	getDocument:function(){
    		return this.getWindow().document;
    	},
    	
    	/*
	  Return the Iframes window object
    	*/
    	getWindow:function(){
    	        var dom= this.dom;
    		
    		return dom?dom.contentWindow||window.frames[dom.name]:window;
    	},
    	
    	/*
	  Print the contents of the Iframes (if we own the document)
    	*/
    	print:function(){ 
    	    if(this._windowContext){
    	      try{
    		
    		var win = this.getWindow();
    		
    		if(Ext.isIE){ win.focus(); } 
    		win.print();
    	      } catch(ex){
    	    	 throw 'print: invalid document context';
    	      }
    	    }	    
    	},
    	//private
    	destroy:function(){
    	  
    	    this.removeAllListeners();
    	    
    	    if(this.dom && this.dom.src) {
    	      this.dom.src = 'javascript:false';
    	    } 	
    	    
    	    	    	  
    	}
    	/*
    	  Execute a javascript code block(string) within the context of the Iframes window object.
    	  * @param {String} block A valid ('eval'able) script source block.
    	  * <p> Note: execScript will only work after a successful update (document.write);
    	*/
    	,execScript: function(block){
    	    if(this._windowContext){
    	        return this._windowContext.eval( block );
    	    } else {
    	    	throw 'execScript:no script context';
    	    }
    	}
    	/* Private 
	  Poll the Iframes document structure to determine DOM ready state,
	  and raise the 'documentloaded' event when applicable.
    	*/
    	,checkDOM : function(win,callback){
    	        //initialise the counter
    		var n = 0 
    		   ,win = win||this.getWindow()
    		   ,manager = this;
    		
      		var t =function() //DOM polling
    			{
    				var domReady=false;
    				//if DOM methods are supported, and the body element exists
    				//(using a double-check including document.body, for the benefit of older moz builds [eg ns7.1] 
    				//in which getElementsByTagName('body')[0] is undefined, unless this script is in the body section)
    				
    				domReady  = (win.document && typeof win.document.getElementsByTagName != 'undefined' 
    				    && ( win.document.getElementsByTagName('body')[0] != null || win.document.body != null ));
    				
    				//if the timer has reached 70 (timeout after ~10.5 seconds)
    				//in practice, shouldn't take longer than 7 iterations [in kde 3 
    				//in second place was IE6, which takes 2 or 3 iterations roughly 5% of the time]
    				if(n++ < 70 && !domReady)
    				{
    					//try again
    					t.defer(150);
    					return;
    				}
    				if(callback)callback();
    				manager.fireEvent("documentloaded",manager); //fallback
    		         };
    		t();
    	   }
 });
 
 /*
  * @class Ext.ux.ManagedIFramePanel 
  * Version:  0.11
  *     Made Panel state-aware.
  * Version:  0.1
  * Author: Doug Hendricks 12/2007 doug[always-At]theactivegroup.com
  *
  * 	
 */
 Ext.ux.ManagedIframePanel = Ext.extend(Ext.Panel, {
    /**
     * @cfg {String/Object/Function} bodyCfg
     Custom bodyCfg used to embed the ManagedIframe.     
    */
    bodyCfg:{tag:'div'
      ,cls:'x-panel-body'
      ,children:[{tag:'iframe',frameBorder:0,width:'100%',height:'100%',cls:'x-managed-iframe'}]
    },
    
    /**
         * Cached Iframe.src url to use for refreshes. Overwritten every time setSrc() is called unless "discardUrl" param is set to true.
         * @type String
     */
    defaultSrc:null,

    /**
         * @cfg {String/Object/Function} iframeStyle
         * Custom CSS styles to be applied to the body's ux.ManagedIframe element in the format expected by {@link Ext.Element#applyStyles}
         * (defaults to {overflow:'auto'}).
     */
    iframeStyle: {overflow:'auto'},
    
    animCollapse:false, 
    
    initComponent : function(){
        
         Ext.ux.ManagedIframePanel.superclass.initComponent.call(this); 
      
         this.addEvents("documentloaded");
         
         if(this.defaultSrc){
 	      this.on('render',  function(){this.setSrc();}, this, {delay:10});
 	      
 	      }
         },
         
      // private
     onDestroy : function(){
         if(this.iframe){
             delete this.iframe.ownerCt;
             Ext.destroy(this.iframe);
         }
                  
         Ext.ux.ManagedIframePanel.superclass.onDestroy.call(this);
     },   
    
     // private
      onRender : function(ct, position){
        
        Ext.ux.ManagedIframePanel.superclass.onRender.call(this, ct, position); 
        
        if(this.iframe = this.body.child('iframe.x-managed-iframe')){
            this.iframe = new Ext.ux.ManagedIFrame(this.iframe);
        
            this.iframe.ownerCt = this;  
            this.relayEvents(this.iframe, ["documentloaded"]);
            
            if(this.iframeStyle){
	      this.iframe.applyStyles(this.iframeStyle);
            }
        }
      },  
        // private
     afterRender : function(){  
         if(this.html && this.iframe){
              this.iframe.update(typeof this.html == 'object' ?
                             Ext.DomHelper.markup(this.html) :
                             this.html);
              delete this.html;
          }
                              
          Ext.ux.ManagedIframePanel.superclass.afterRender.call(this); 
         
     },
     
      //private  
     doAutoSrc : function(){  this.setSrc();  },
     
         /**
          * Sets the embedded Iframe src property.
          
          * @param {String/Function} url (Optional) A string or reference to a Function that returns a URI string when called
          * @param {Boolean} discardUrl (Optional) If not passed as <tt>false</tt> the URL of this action becomes the default URL for
          * this panel, and will be subsequently used in future setSrc calls.
          * Note:  invoke the function with no arguments to refresh the iframe based on the current defaultSrc value.
         */
     setSrc : function(url, discardUrl){
           
         var src = url || this.defaultSrc || (Ext.isIE&&Ext.isSecure?Ext.SSL_SECURE_URL:'');
              
          if(this.rendered && this.iframe){ //rendered?
              
              this.iframe._windowContext = null;
              this.iframe.dom.src = (typeof src == 'function'?src()||'':src);
           }
               
          if(discardUrl !== true){ this.defaultSrc = src; }
          this.saveState();
	             
          
     },
     
     //Make it state-aware
     getState: function(){

        return Ext.apply(Ext.ux.ManagedIframePanel.superclass.getState.call(this) || {},
             {defaultSrc  :this.defaultSrc });
     }
 
}); 
Ext.reg('iframepanel', Ext.ux.ManagedIframePanel);