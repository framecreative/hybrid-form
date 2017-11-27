<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>

<p>
    <strong>How To</strong><br>
</p>
<ol>
    <li>
       First add a new form (click the "Add Form" button).<br>
    </li>
    <li>
        Configure the form. Make sure you setup at least one handler and define the success redirect (see the options below the line for more info).<br>
    </li>
    <li>
        Once you have saved the form you need to create a client side contact form. You can do this using normal HTML form markup. 
        The only addition is you need to set the form action to <code>/handle-form</code>, method to <code>post</code> 
        and add a hidden field called <code>_form_id</code> with the ID of the form you just created.
    </li>
</ol>

<hr>

<p>
    <strong>Simple Example Form</strong><br>
</p>
<pre>
&lt;form action=&quot;/handle-form&quot; method=&quot;post&quot;&gt;
    &lt;input type=&quot;hidden&quot; name=&quot;_form_id&quot; value=&quot;1&quot;&gt;
    &lt;input type=&quot;hidden&quot; name=&quot;_post_id&quot; value=&quot;&lt;?=get_the_ID()?&gt;&quot; &gt;
    &lt;input type=&quot;email&quot; name=&quot;email&quot; placeholder=&quot;Enter Email&quot; required&gt;
    &lt;button type=&quot;submit&quot;&gt;Submit Enquiry&lt;/button&gt;
&lt;/form&gt;

&lt;!-- Display error messages --&gt;
&lt;?=hyb_flash_message('form_error')?&gt;
</pre>

<hr>

<p>
    <strong>Options</strong><br>
</p>

<p style="margin-left:20px">
    <strong style="margin-left:-10px;">Name</strong><br>
    Name of the form handler (this isn't really used except for you own identification).</li>
</p>

<p style="margin-left:20px">
    <strong style="margin-left:-10px;">Redirect</strong><br>
    URL to redirect to if form is successfully submitted.<br>
    <em>(Works With Tags)</em>
</p>

<p style="margin-left:20px">
    <strong style="margin-left:-10px;">Handlers</strong><br>
    <b>Mail</b> - Simple mail handler. Will send all form data to specified address(es).<br>
    <b>Campaign Monitor</b> - Generates new mailing list signup.<br>
    <b>MailChimp</b> - Generates new mailing list signup.<br>
    <b>Web2Lead</b> - Generates new SalesForce lead.<br>
    <em>(All Handlers Work With Tags)</em>
</p>