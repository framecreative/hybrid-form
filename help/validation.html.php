<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<p style="margin-left:10px">
    <strong style="margin-left:-10px">Akismet</strong><br>
    <b>Enabled</b> - (true, false) Allows you to enable or disable the form from being sent to Akismet to be spam checked.<br>
    <b>Fields</b> - Match the Akismet fields on the left to your own form fields on the right.
</p>

<hr>

<p style="margin-left:10px">
    <strong style="margin-left:-10px">Validation</strong><br>
    HybridForm features a configurable validation system. Validation rules are defined in the form <code>"rule_name": "field_name"</code>, or for multiple fields <code>"rule_name": [["field1"],["field1"]]</code>
</p>
<pre>
    "validation": {
        #single field example
        "required": "email"

        #multiple field example
        "required": [
            ["name"],
            ["email"]
        ]

        #multiple field example with options
        "length"   => [
            ["name", 5],
            ["email", 5]
        ]
    }
</pre>

<hr>

<p style="margin-left:10px">
    <strong style="margin-left:-10px">Validation Rules</strong><br>
    <strong>required</strong> - Required field<br>
    <strong>honeypot</strong> - Opposite of required, must be empty<br>
    <strong>equals</strong> - Field must match another field (email/password confirmation)<br>
    <strong>different</strong> - Field must be different than another field<br>
    <strong>accepted</strong> - Checkbox or Radio must be accepted (yes, on, 1, true)<br>
    <strong>numeric</strong> - Must be numeric<br>
    <strong>integer</strong> - Must be integer number<br>
    <strong>length</strong> - String must be certain length<br>
    <strong>lengthBetween</strong> - String must be between given lengths<br>
    <strong>min</strong> - Minimum<br>
    <strong>max</strong> - Maximum<br>
    <strong>in</strong> - Performs in_array check on given array values<br>
    <strong>notIn</strong> - Negation of in rule (not in array of values)<br>
    <strong>ip</strong> - Valid IP address<br>
    <strong>email</strong> - Valid email address<br>
    <strong>url</strong> - Valid URL<br>
    <strong>urlActive</strong> - Valid URL with active DNS record<br>
    <strong>alpha</strong> - Alphabetic characters only<br>
    <strong>alphaNum</strong> - Alphabetic and numeric characters only<br>
    <strong>slug</strong> - URL slug characters (a-z, 0-9, -, _)<br>
    <strong>regex</strong> - Field matches given regex pattern<br>
    <strong>date</strong> - Field is a valid date<br>
    <strong>dateFormat</strong> - Field is a valid date in the given format<br>
    <strong>dateBefore</strong> - Field is a valid date and is before the given date<br>
    <strong>dateAfter</strong> - Field is a valid date and is after the given date<br>
    <strong>contains</strong> - Field is a string and contains the given string<br>
    <strong>creditCard</strong> - Field is a valid credit card number
</p>