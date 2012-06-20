=== Gravity to Solve360 ===
Contributors: steve.barnett
Tags: gravity, gravityforms, solve360
Stable tag: 0.97
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
3. Add extra data to your existing Gravity Forms as set out in the FAQ section.
4. Data is sent from Gravity Forms to Solve360 when someone visits your WordPress site.


== Frequently Asked Questions ==

= How do I mark fields in Gravity Forms for export to Solve360? =

= General =

To mark fields in your Gravity Form that are to be sent to Solve360, add an Admin Label in this pattern: "solve360 Field Name".
The field data will be added as the contents of the Field.
You can find _Field Names_ in Solve360 by going to _My Account_, _API Reference tab_, and selecting _Fields_ from the drop down.

Example

	Field Name in Solve360 "businessemail"
	Admin Label "solve360 businessemail"
	Data "Steve Barnett"
	will show
	"Steve Barnett" in the businessemail Field of the Solve360 contact.


= Solve360 required fields =

The following fields are required:

* businessemail;
* category;
* ownership.


= Special Fields: Solve360 Categories =

These must be added as hidden fields.
The Field Label should be "solve360 category CategoryName". CategoryName is for your reference only.
The Default Value must contain the Tag ID, e.g. "12345678".
You can find _Tag IDs_ in Solve360 by going to _My Account_, _API Reference tab_, and selecting _Category Tags_from the drop down.



= Special Fields: Gravity Names =

When Gravity's combined fields for Name are used, the Admin Label should be "solve360 fullname".


= Special Fields: Solve360 Notes =

The Admin Label should be of the form: "solve360 note NoteName".
For hidden fields, the Solve360 note will be the Admin Label text.
For regular inputs, the Solve360 note will be the Admin Label text followed by the field data.


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

= 0.97 =

* Bug fix - removed unused items from Setting page

= 0.96 =

* Added sending to Solve360 using WP-Cron

= 0.95 =

* Added better name support

= 0.9 =

* Added FAQ section

= 0.81 =

* Tiny bug fixes - name, found fields.

= 0.8 =

* Allowed for adding of any Field by Field Name
* Better error messages

= 0.7 =

* Added better error checking

= 0.62 =

* Fixed error with cellularphone field

= 0.61 =

* Fixed error with debug mode / last export date

= 0.6 =

* Added link to GitHub
* Add Options page


= 0.5 =
* Added check for is_plugin_active for later use in cronjob

= 0.4 =
* Initial release