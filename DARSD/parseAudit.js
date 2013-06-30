/*
	assumptions made:
		- audit does not give course options unless it gives a number of required courses (false, see kenny's audit)
		- james scholarship is not a requirement (false, see chee's audit)
*/

var html = document.documentElement.innerHTML;

var json = { };

var hier1 = />NO.+<\/span>/gi;
var hier2 		= />\s+-.+?(\d+\))?(.+?)<\/span>/gi;
var hier2copy 	= />\s+-.+?(\d+\))?(.+?)<\/span>/gi;	// used to find index of next hier2 match result without
												// changing original hier2 RegEx object;
												// var hier2copy = hier2 would make hier2copy a duplicate handle
												// for the same instance ofhier2, so redundant declaration
												// is necessary.
var needsHour = /needs:.*?(\d+\.?\d?) hour.?/gi;
var hoursAdded = /(\d+\.?\d?) hour.? added/gi;
var needsCourse = /needs:.*?(\d+\.?\d?)\s+course.?/gi;
var courseOpts = /select from:(.|\n)+?(<br( \/)?>|underline">)/gim;
var underline = /<div class="underline">/gi;
var match1, match2, matchHour, matchAdded, matchCourse, matchOpts;
var endIndex;	// denotes the end of the primary hierarchical section
var parenthesizedHours;


// read the header of the audit;
// gather information about this user's identity and the audit generation date
var UIN = html.match(/\d{9}/);
UIN = Number(UIN);
json.UIN = UIN;

var major = html.match(/CATALOG YEAR: \d+<\/span>\n(.|\n)+?<div class="underline">/im);
major = major[0];
major = major.substring(major.search("\n")+1);	// get rid of the first linebreak + the next line
major = major.substring(major.search("\n")+1);
major = major.substring(0, major.length-24);	// get rid of the underline
major1 = major.substring(major.search(/>\s+/i)+1, major.search(/<\/span>/im));	// get rid of surrounding tags
majorResult = major1.trim();

// check for a second row of the major, also
if (major.search("\n") != -1)
{
	major2 = major.substring(major.search("\n")+1);
	major2 = major2.substring(major2.search(/>\s+/i)+1, major2.search(/<\/span>/im));	// get rid of surrounding tags
	major2 = major2.trim();
	majorResult += ' - ' + major2;
}
json.major = majorResult;

var date = html.match(/PREPARED: (\d{2}\/){2}\d{2}/);
date = date[0];		// this line is necessary because of the use of parentheses in the regexp
date = String(date);
date = date.substr(10);

var time = html.match(/\d{2}:\d{2}/);
time = String(time);

json.date = date;
json.time = time;

json.results = [ ];
var a = 0;
var b;
var match2found;
// find unsatisfied primary groups
while (match1 = hier1.exec(html))
{
	match1 = String(match1);
	match1 = match1.substring(3, match1.length-7);
	match1 = cleanMatch(match1);
	json.results[a] = { };
	json.results[a].primary = { };
	json.results[a].primary.Group = match1;
	
	hier2.lastIndex = hier1.lastIndex;
	underline.lastIndex = hier1.lastIndex;
	needsHour.lastIndex = hier1.lastIndex;
	needsCourse.lastIndex = hier1.lastIndex;

	underline.test(html);
	endIndex = underline.lastIndex;

	// find primary needs hour
	var temp1 = needsHour.lastIndex;
	var temp2 = hier2.lastIndex;
	hier2.test(html);
	if ((matchHour = needsHour.exec(html))
		&& ((needsHour.lastIndex < hier2.lastIndex) || (hier2.lastIndex == 0))
		&& (needsHour.lastIndex < endIndex))
	{
		matchHour = Number(matchHour[1]);
		json.results[a].primary.Hrs = matchHour;
	}
	else
		needsHour.lastIndex = temp1;

	// find primary needs course
	if ((matchCourse = needsCourse.exec(html))
		&& ((needsCourse.lastIndex < hier2.lastIndex) || (hier2.lastIndex == 0))
		&& (needsCourse.lastIndex < endIndex))
	{
		matchCourse = Number(matchCourse[1]);
		json.results[a].primary.Course = matchCourse;

		courseOpts.lastIndex = hier1.lastIndex;
		if ((matchOpts = courseOpts.exec(html))
			&& (courseOpts.lastIndex = html.indexOf(String(matchOpts[0])) + 5)	// take only the first result
			&& ((courseOpts.lastIndex < hier2.lastIndex) || (hier2.lastIndex == 0))
			&& (courseOpts.lastIndex < endIndex))
		{
			matchOpts = matchOpts[0];
			matchOpts = cleanMatch(matchOpts, 1);
			json.results[a].courseOpts = matchOpts;
		}
	}

	hier2.lastIndex = temp2;	// restore hier2
	match2found = false;
	b = 0;
	// find unsatisfied secondary groups
	while ((match2 = hier2.exec(html)) && (hier2.lastIndex < endIndex))
	{
		// initialize the secondary group object if it isn't already available
		if (match2found == false)
		{
			match2found = true;
			json.results[a].secondary = [ ];
		}

		match2 = String(match2[2]);
		match2 = match2.substring(match2.indexOf(')')+1);
		match2 = cleanMatch(match2);
		json.results[a].secondary[b] = { };
		json.results[a].secondary[b].Group = match2;

		// keep searching, but be sure matches corresponds to this specific secondary hierarchical section
		hier2copy.lastIndex = hier2.lastIndex;
		hier2copy.test(html);	// advance lastIndex to know when to stop considering matchCourse results
								// new lastIndex returns to 0 after no more results are found

		// could the requirement be an hours requirement? (that's not denoted by "needs: __ hours")
		hoursAdded.lastIndex = hier2.lastIndex;
		if ((parenthesizedHours != -1) && (matchAdded = hoursAdded.exec(html))
			&& ((hoursAdded.lastIndex < hier2copy.lastIndex) || (hier2copy.lastIndex == 0))
			&& (hoursAdded.lastIndex < endIndex))
		{
			matchAdded = matchAdded[1];	// obtain just the numeric match
			matchAdded = Number(parenthesizedHours) - Number(matchAdded);

			json.results[a].secondary[b].Hrs = matchAdded;
		}

		// find secondary needs hour (as denoted by "needs: __ hours")
		var temp = needsHour.lastIndex;
		if ((matchHour = needsHour.exec(html))
			&& ((needsHour.lastIndex < hier2copy.lastIndex) || (hier2copy.lastIndex == 0))
			&& (needsHour.lastIndex < endIndex))
		{
			matchHour = Number(matchHour[1]);
			json.results[a].secondary[b].Hrs = matchHour;
		}
		else
			needsHour.lastIndex = temp;

		// get matchCourse
		needsCourse.lastIndex = hier2.lastIndex;
		if ((matchCourse = needsCourse.exec(html))
			&& ((needsCourse.lastIndex < hier2copy.lastIndex) || (hier2copy.lastIndex == 0))
			&& (needsCourse.lastIndex < endIndex))
		{
			matchCourse = Number(matchCourse[1]);
			// matchCourse = cleanMatch(matchCourse);
			json.results[a].secondary[b].Course = matchCourse;

			// should also provide a list of courses to select from
			// but let's check and make sure there is a list before using it.
			courseOpts.lastIndex = hier2.lastIndex;
			if ((matchOpts = courseOpts.exec(html))
				&& (courseOpts.lastIndex = html.indexOf(String(matchOpts[0])) + 5)	// take only the first result
				&& ((courseOpts.lastIndex < hier2copy.lastIndex) || (hier2copy.lastIndex == 0))
				&& (courseOpts.lastIndex < endIndex))
			{
				matchOpts = matchOpts[0];
				matchOpts = cleanMatch(matchOpts, 1);
				json.results[a].courseOpts = matchOpts;
			}
		}

		b++;
	}

	a++;
	parenthesizedHours = -1;	// reset parenthesizedHours so it doesn't carry between primary groups
}

// now match the taken courses (including in progress)
json.taken = {};
var taken = /(\w{2}\d{2})\s+(\w{2,4}\s+\d{3})(\s+(\d|\w)*)?\s+\d\.\d\s+(IP\s*\&gt;\s*I)?/gi;
var inProgress;
while (matchTaken = taken.exec(html))
{
	json.taken[cleanMatch(matchTaken[2])] = matchTaken[1];
}
console.log(json);
json = JSON.stringify(json);


// send data to server
var xmlhttp = new XMLHttpRequest();
xmlhttp.open("POST","http://darsfordummies.web.engr.illinois.edu/din.php", true);
xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
xmlhttp.send("msg="+json+"&loc="+encodeURIComponent(window.location.href));
xmlhttp.onreadystatechange = function()
{
	if(xmlhttp.readyState==4)
	{
		// server response has been received
		document.documentElement.innerHTML = xmlhttp.responseText + document.documentElement.innerHTML;
	}
}


function cleanMatch(str, parenth)
{
	var val = String(str);
	var parenthesized;

	if (typeof parenth === 'undefined')
	{
		// if the value in between parentheses contains the word "hour", then save the first numeric match
		if ((parenthesized = val.match(/\(.+?hour.+?\)/gi)) !== null)
		{
			parenthesized = parenthesized[0];
			parenthesized = parenthesized.match(/\d{1,2}/);
			parenthesizedHours = parenthesized[0];
		}

		// eliminate parenthesized expressions
		val = val.replace(/\(.+?\)/g, '');
	}

	// remove asterisk's
	val = val.replace(/\*+/g, '');

	// remove anything in <> tags
	val = val.replace(/(<.+?>|&nbsp;|\n|\r|\t)/g, ' ');

	// eliminate random hyphen at the end of string
	if (val.charAt(val.length-1) === '-')
		val = val.substring(0, val.length-1);

	// compact whitespace inbetween characters
	val = val.replace(/\s+/g, ' ');
	val = val.trim();

	return val;
}