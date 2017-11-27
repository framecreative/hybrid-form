<?php if ( ! defined( 'ABSPATH' ) ) die( 'No direct access allowed' ); ?>
<p style="margin-left:10px">
    <strong style="margin-left:-10px;">Tags</strong><br>
    The following custom tags can be used in the 'redirect' and 'handler options' fields.
</p>

<pre>
    {{POST[key]}} = $_POST variable

    {{POSTTITLE}} = post title (*)
    {{POSTMETA[metakey]}} = meta key value (*)
                            e.g. {{POSTMETA[contact_form.to_email]}}
                            will load the contact_form.to_email meta value from the specified post
    {{POSTSLUG}} = post slug (*)
    {{POSTURL}} = post url (*) e.g. http://www.example.com/the-post
</pre>

<em style="margin-left:10px">All tags marked (*) require that the '_post_id' is set. Add a hidden input to your form with the name '_post_id'.</em><br>
