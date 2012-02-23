=== Gravity to Solve360 ===
Contributors: steve.barnett
Tags: gravity, solve360
Stable tag: 0.6
Requires at least: 3.1.3
Tested up to: 3.3.1
License: GPLv2 or later

Exports data from completed Gravity Forms to a specified Solve360 account.

== Description ==

Exports data from completed <a href="http://www.gravityforms.com/">Gravity Forms</a> to a specified <a href="http://norada.com/">Solve360</a> account.
Also available on GitHub: <a href="https://github.com/SteveBarnett/Gravity-to-Solve360">https://github.com/SteveBarnett/Gravity-to-Solve360</a>.

== Installation ==

1. Go to Plugins > Add New, Upload and choose gravity-to-solve360.zip.
2. Activate the plugin.
3. Add extra data to your existing Gravity Forms as set out below.
4. Go to Tools > Export to Solve360 to send data from Gravity Forms to Solve360.

== Labelling Gravity Forms for export ==

= Solve360 required fields =

Fields with data to be passed to Solve360 must be Admin Labelled: "solve360 fieldname".
The required fields are:
	firstname;
	lastname;
	businessemail;
	cellularphone.
When Gravity's combined fields for Name are used, the Admin Label should be "solve360 fullname".


= Solve360 Categories =

These must be added as hidden fields.
The Field Label should be "solve360 category CategoryName". CategoryName is for reference only.
The Default Value must contain the tag id, e.g. "12345678".


= Solve360 Notes =

The Admin Label should be of the form: "solve360 note NoteName".
For hidden fields, the Solve360 note will be the Admin Label text.
For regular inputs, the Solve360 note will be the Admin Label text followed bt the field data.


Input field example

	Field Label "Company"
	Admin Label "solve360 note Company:"
	Data "XYZ Co"
	will show
	"Company: XYZ Co"


Hidden field example

	Field Label: "solve360 note NoteTextGoesHere"
	will show
	"NoteTextGoesHere"

== Changelog ==

= 0.6 =

* Added link to GitHub
* Add Options page


= 0.5 =
* Added check for is_plugin_active for later use in cronjob

= 0.4 =
* Intial release