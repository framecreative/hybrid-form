<!-- TEMPLATES -->
<script id="hybf_template_row" type="text/template">
    <tr class="hybf_form" data-key1="{{KEY1}}">
        <td>
            <input type="hidden" name="hybf_form[{{KEY1}}][id]" value="{{ID}}" />
            {{ID}}
        </td>
        <td><input type="text" name="hybf_form[{{KEY1}}][name]" value="{{NAME}}" style="width:95%" /></td>
        <td><input type="text" name="hybf_form[{{KEY1}}][redirect]" value="{{REDIRECT}}" style="width:95%" /></td>
        <td>
            <table class="hybf_handlers form-table">
                <tbody>
                    {{HANDLERS}}
                </tbody>
            </table>

            <div style="text-align: center; clear: both; margin-top: 20px; margin-bottom: 10px;">
                <button class="hybf_add-handler-btn button-secondary"><?php _e( 'Add Handler' ); ?></button>
            </div>
        </td>
    </tr>
</script>

<script id="hybf_template_handler" type="text/template">
    <tr class="hybf_handler" data-key2="{{KEY2}}">
        <th>Handler</th>
        <td>
            <select class="hybf_handlers_select" name="hybf_form[{{KEY1}}][handlers][{{KEY2}}][name]">
                <?php foreach($hybf_handler_list as $hybf_handler_list_key => $hybf_handler_list_item): ?>
                    <option value="<?php echo $hybf_handler_list_key; ?>">
                        <?php echo $hybf_handler_list_item; ?>
                    </option>
                <?php endforeach ?>
            </select>
            <button class="hybf_remove-handler-btn button-secondary"><?php _e( 'Remove' ); ?></button>
        </td>
    </tr>
    <tr>
        <th>Options</th>
        <td>
            <textarea class="hybf_handlers_options" name="hybf_form[{{KEY1}}][handlers][{{KEY2}}][options]" style="width:100%; height: 180px; font-family: monospace; resize: vertical;">{{HANDLEROPTION}}</textarea>
        </td>
    </tr>
</script>

<script>
    jQuery( document ).ready(function( $ ) {
        var template_row = $('#hybf_template_row').html();
        var template_handler = $('#hybf_template_handler').html();
        var handler_defaults = {
            "mail": {
                "from": "Website form<no-reply@example.com>",
                "to": [
                    "test@example.com"
                ],
                "subject": "Email From Website Form"
            },
            "campaign_monitor": {
                'access_token': '',
                'refresh_token': '',
                'list_id': '',
                'email_field': '',
                'name_field': ''
            },
            "mail_chimp": {
                'api_key': '',
                'list_id': '',
                'email_field': '',
                'first_name_field': '',
                'last_name_field': ''
            },
            "web2lead": {
                'org_id': '',
                'lead_source': ''
            }
        };

        // Add Form Button
        $('#hybf_add-form-btn').click(function(e) {
            e.preventDefault();

            var last_key1 = $('.hybf_form').last().attr('data-key1');
            var form_key1 = 0;

            if(typeof(last_key1) != 'undefined') {
                form_key1 = parseInt(last_key1) + 1;
            }

            var handler = template_handler.replace(/{{KEY1}}/g, form_key1);
            handler = handler.replace(/{{KEY2}}/g, 0);
            handler = handler.replace(/{{HANDLEROPTION}}/g, JSON.stringify(handler_defaults.mail, null, 2));

            var html = template_row.replace(/{{ID}}/g, form_key1+1);
            html = html.replace(/{{NAME}}/g, "Test Name");
            html = html.replace(/{{REDIRECT}}/g, '/');
            html = html.replace(/{{KEY1}}/g, form_key1);
            html = html.replace(/{{HANDLERS}}/g, handler);

            $('#hybf_filter-table > tbody').append(html);
        });

        // Add Handler Button
        $('#hybf_filter-table').on('click', '.hybf_add-handler-btn', function(e) {
            e.preventDefault();

            var handler_count = $(this).closest('td').find('.hybf_handlers .hybf_handler').length;
            var key1 = parseInt($(this).closest('.hybf_form').attr('data-key1'));

            var handler = template_handler.replace(/{{KEY1}}/g, key1);
            handler = handler.replace(/{{KEY2}}/g, handler_count);
            handler = handler.replace(/{{HANDLEROPTION}}/g, JSON.stringify(handler_defaults.mail, null, 2));

            $(this).closest('td').find('.hybf_handlers tbody').append(handler);
        });

        // Remove Handler Button
        $('#hybf_filter-table').on('click', '.hybf_remove-handler-btn', function(e) {
            e.preventDefault();

            var handler_count = $(this).closest('.hybf_handlers').find('.hybf_handler').length;

            if(handler_count > 1) {
                $(this).closest('.hybf_handler')
                            .next().remove()
                       .end().remove();
            } else {
                alert('Must have at least 1 handler.');
            }
        });

        // On Handler Select Change
        $('#hybf_filter-table').on('change', '.hybf_handlers_select', function(e) {
            var current_option = $(this).find("option:selected").val();
            var new_text = JSON.stringify(handler_defaults[current_option], null, 2);

            $(this).closest('.hybf_handler').next().find('textarea').val(new_text);
        });

        //On Form Submit -> Validate JSON
        $('#hybf_options_form').submit(function() {
            var all_valid = true;

            $('.hybf_handlers_options').each(function(index, el) {
                try {
                    jQuery.parseJSON($(el).val());
                } catch (e) {
                    all_valid = false;
                }
            });

            if(!all_valid) {
                alert('A handler option contains invalid JSON!');
            }

            return all_valid;
        });
    });
</script>

<div class="wrap">
    <?php screen_icon(); ?>
    <h2><?php _e('HybridForm') ?></h2>
    <br/>
    <div>
        <form id="hybf_options_form" name="hybf_options_form" method="post" action=""> 
            <?php settings_fields('hybf-options'); ?>
            <input type="hidden" name="hybf_submit_hidden" value="Y">

            <table id="hybf_filter-table" class="widefat">
                <thead>
                    <tr>
                        <th class="row-title"><?php _e('ID') ?></th>
                        <th><?php _e('Name') ?></th>
                        <th><?php _e('Redirect') ?></th>
                        <th><?php _e('Handlers') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($hybf_forms as $key1 => $hybf_form_opt): ?>
                        <tr class="hybf_form" data-key1="<?php echo $key1;?>">
                            <td>
                                <input type="hidden" name="hybf_form[<?php echo $key1;?>][id]" value="<?php echo $hybf_form_opt['id']; ?>" />
                                <?php echo $hybf_form_opt['id']; ?>
                            </td>
                            <td><input type="text" name="hybf_form[<?php echo $key1;?>][name]" value="<?php echo $hybf_form_opt['name']; ?>" style="width:95%" /></td>
                            <td><input type="text" name="hybf_form[<?php echo $key1;?>][redirect]" value="<?php echo $hybf_form_opt['redirect']; ?>" style="width:95%" /></td>
                            <td>
                                <table class="hybf_handlers form-table">
                                    <?php foreach($hybf_form_opt['handlers'] as $key2 => $hybf_handler): ?>
                                        <tr class="hybf_handler" data-key2="<?php echo $key2;?>">
                                            <th>Handler</th>
                                            <td>
                                                <select class="hybf_handlers_select" name="hybf_form[<?php echo $key1;?>][handlers][<?php echo $key2;?>][name]">
                                                    <?php foreach($hybf_handler_list as $hybf_handler_list_key => $hybf_handler_list_item): ?>
                                                        <option value="<?php echo $hybf_handler_list_key; ?>" <?php echo $hybf_handler['name'] == $hybf_handler_list_key ? 'selected' : ''; ?>>
                                                            <?php echo $hybf_handler_list_item; ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                                <button class="hybf_remove-handler-btn button-secondary"><?php _e( 'Remove' ); ?></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Options</th>
                                            <td>
                                                <textarea class="hybf_handlers_options" name="hybf_form[<?php echo $key1;?>][handlers][<?php echo $key2;?>][options]" style="width:100%; height: 100px; font-family: monospace; resize: vertical;"><?php echo $hybf_handler['options']; ?></textarea>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </table>

                                <div style="text-align: center; clear: both; margin-top: 20px; margin-bottom: 10px;">
                                    <button class="hybf_add-handler-btn button-secondary"><?php _e( 'Add Handler' ); ?></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align:center"><button id="hybf_add-form-btn" class="button-secondary"><?php _e( 'Add Form' ); ?></button></th>
                    </tr>
                </tfoot>
            </table>

            <br/>
            <p><input id="hybf_submit-btn" class="button-primary" type="submit" name="Save" value="<?php _e( 'Save Changes' ); ?>" /></p>
        </form>
    </div>
    <div class="hybrid-form-help" style="background: #EAEAEA; margin-top: 30px; padding: 20px;">
        <h2>Help</h2>
        <pre>Name:
    Name of the form handler (this isn't really used except for you own identification).

Redirect:
    URL to redirect to if form is successfully submitted.

Handlers:
    Mail: Simple mail handler. Will send all form data to specified address(es).
    Campaign Monitor: Generates new mailing list signup.
    MailChimp: Generates new mailing list signup.
    Web2Lead: Generates new SalesForce lead.

Tags:
    The following custom tags can be used in the 'redirect' and 'handler options' fields.
    
    {{POST[key]}} = $_POST variable

    The following tags require that the '_post_id' is set. Add a hidden input to your form with the name '_post_id'.

    {{POSTTITLE}} = post title
    {{POSTMETA[metakey]}} = meta key value
                            e.g. {{POSTMETA[contact_form.to_email]}}
                            will load the contact_form.to_email meta value from the specified post
    {{POSTSLUG}} = post slug
    {{POSTURL}} = post url e.g. http://www.blog.com/the-post
</pre>
    </div>
</div>