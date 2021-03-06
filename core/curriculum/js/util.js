/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2012 Remote Learner.net Inc http://www.remote-learner.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    elis
 * @subpackage curriculummanagement
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2008-2012 Remote Learner.net Inc http://www.remote-learner.net
 *
 */

/* toggleVisible and toggleVisibleInit are shamelessly borrowed from
 * lib/javascript-static.js from Moodle.  */

function toggleVisible(e, element) {
    element = document.getElementById(element);
    var button = e.target ? e.target : e.srcElement;
    var regexp = new RegExp(' ?hide');
    if (element.className.match(regexp)) {
	element.className = element.className.replace(regexp, '');
	button.value = button.moodle.hideLabel;
    } else {
        element.className += ' hide';
	button.value = button.moodle.showLabel;
    }

    return false;
}

function toggleVisibleInit(addBefore, nameAttr, buttonLabel, hideText, showText, element) {
    var showHideButton = document.createElement("input");
    showHideButton.type = 'button';
    showHideButton.value = buttonLabel;
    showHideButton.name = nameAttr;
    showHideButton.moodle = {
        hideLabel: hideText,
        showLabel: showText
    };
    YAHOO.util.Event.addListener(showHideButton, 'click', toggleVisible, element);
    el = document.getElementById(addBefore);
    el.parentNode.insertBefore(showHideButton, el);
}

// Takes an integer and returns a string representing the integer, padded to a minimum of two characters
var cmPadDigit = function(i) {
	return (i < 10) ? "0" + i : String(i);
}

// Custom formatter for time range column
var cmFormatTimeRange = function(elCell, oRecord, oColumn, oData) {
    elCell.innerHTML = cmPadDigit(oData[0]) + ":" + cmPadDigit(oData[1]) + " - " + cmPadDigit(oData[2]) + ":" + cmPadDigit(oData[3]);
};

// Custom sorter for time range column
var cmSortTimeRange = function(a, b, desc) {
    // Deal with empty values
    if(!YAHOO.lang.isValue(a)) {
        return (!YAHOO.lang.isValue(b)) ? 0 : 1;
    }
    else if(!YAHOO.lang.isValue(b)) {
        return -1;
    }

    var comp = YAHOO.util.Sort.compare;

    // Start hour
    var lastComp = comp(a[0], b[0], desc);

    // Start minute
    if(lastComp == 0) {
    	lastComp = comp(a[1], b[1], desc);
    }

    // End hour
    if(lastComp == 0) {
    	lastComp = comp(a[2], b[2], desc);
    }

    // End minute
    if(lastComp == 0) {
    	lastComp = comp(a[3], b[3], desc);
    }

    return lastComp;
};

// Rewrite the name search query string
function changeNamesearch(anchoritem) {
  // Get the search text box
  var namesearch = document.getElementsByName('namesearch');

  // Replace the current query string with the current value in the search box
  // assume that "namesearch" (if it is provided) is not the first parameter
  var url = anchoritem.href;
  url = url.replace(/&namesearch=[^&]*/,'');
  url = url.concat('&namesearch=', namesearch[0].value);

  anchoritem.href = url;
}

// date formatter for YUI table that understands 0 dates
function cmFormatDate(elCell, oRecord, oColumn, oData) {
    if (oData.getDate()) {
	elCell.innerHTML = YAHOO.util.Date.format(oData, {format:"%b %d, %Y"});
    } else {
	elCell.innerHTML = '-';
    }
}
