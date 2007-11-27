/*----------------------------------------------------------------------------\
|                             String Builder 1.02                             |
|-----------------------------------------------------------------------------|
|                         Created by Erik Arvidsson                           |
|                  (http://webfx.eae.net/contact.html#erik)                   |
|                      For WebFX (http://webfx.eae.net/)                      |
|-----------------------------------------------------------------------------|
| A class that allows more efficient building of strings than concatenation.  |
|-----------------------------------------------------------------------------|
|                  Copyright (c) 1999 - 2006 Erik Arvidsson                   |
|-----------------------------------------------------------------------------|
| Licensed under the Apache License, Version 2.0 (the "License"); you may not |
| use this file except in compliance with the License.  You may obtain a copy |
| of the License at http://www.apache.org/licenses/LICENSE-2.0                |
| - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - |
| Unless  required  by  applicable law or  agreed  to  in  writing,  software |
| distributed under the License is distributed on an  "AS IS" BASIS,  WITHOUT |
| WARRANTIES OR  CONDITIONS OF ANY KIND,  either express or implied.  See the |
| License  for the  specific language  governing permissions  and limitations |
| under the License.                                                          |
|-----------------------------------------------------------------------------|
| 2000-10-02 | First version                                                  |
| 2000-10-05 | Added a cache of the string so that it does not need to be     |
|            | regenerated every time in toString                             |
| 2002-10-03 | Added minor improvement in the toString method                 |
| 2006-04-25 | Changed license to Apache Software License 2.0                 |  
|-----------------------------------------------------------------------------|
| Created 2000-10-02 | All changes are in the log above. | Updated 2006-04-25 |
\----------------------------------------------------------------------------*/

function StringBuilder(sString) {

	// public
	this.length = 0;
	
	this.append = function (sString) {
		// append argument
		this.length += (this._parts[this._current++] = String(sString)).length;
		
		// reset cache
		this._string = null;
		return this;
	};
	
	this.toString = function () {
		if (this._string != null)
			return this._string;
		
		var s = this._parts.join("");
		this._parts = [s];
		this._current = 1;
		this.length = s.length;
		
		return this._string = s;
	};

	// private
	this._current	= 0;
	this._parts		= [];
	this._string	= null;	// used to cache the string
	
	// init
	if (sString != null)
		this.append(sString);
}
