;(function(w, d) {
	var frame,offset,el;	
	w.onload = function() {
		frame = d.getElementById('formdesigner');
		if(frame) {
			offset = getOffsetTop(frame);
			frame.style.height = getHeight() + 'px';
			d.body.className += ' formdesigner';
		}
	}
		
	w.onresize = function() {
		if(frame) {
			frame.style.height = getHeight() + 'px';
		}
	}
	
	function getHeight(){
		if(!el) {
			var el = d.createElement('div');
			el.style = 'position: absolute; top: 0; left: 0; bottom: 0; width: 1px;';
			d.body.appendChild(el);
		}
		return el.offsetHeight - offset;
	}
	
	function getOffsetTop(elem) {
	    if (elem.getBoundingClientRect) {
	        return getOffsetTopRect(elem)
	    } else {
	        return getOffsetTopSum(elem)
	    }
	}
	
	function getOffsetTopSum(elem) {
	    var top=0;
	    while(elem) {
	        top = top + parseInt(elem.offsetTop);
	        elem = elem.offsetParent;
	    }					
	    return top;
	}
	
	function getOffsetTopRect(elem) {
	    var box = elem.getBoundingClientRect();
	    var body = d.body;
	    var docElem = d.documentElement;
	    var scrollTop = w.pageYOffset || docElem.scrollTop || body.scrollTop;
	    var clientTop = docElem.clientTop || body.clientTop || 0;				
	    return Math.round(box.top +  scrollTop - clientTop);
	}	
}(window, document));